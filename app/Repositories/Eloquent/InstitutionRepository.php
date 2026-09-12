<?php

namespace App\Repositories\Eloquent;

use App\Models\Institution;
use App\Repositories\Contracts\InstitutionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class InstitutionRepository implements InstitutionRepositoryInterface
{
    private function getList(array $filters, int $perPage = 20, bool $is_collection = false): LengthAwarePaginator|Collection
    {
        $query = Institution::query()->with('search');

        if (! empty($filters['search_id'])) {
            $query->where('search_id', $filters['search_id']);
        }

        if (! empty($filters['search']) && trim($filters['search']) !== '') {
            $search = strtolower(trim($filters['search']));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(address) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(type) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(postcode) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(search_id) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(city) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(state) LIKE ?', ["%{$search}%"])
                    ->orWhereHas('search', function ($subQuery) use ($search) {
                        $subQuery->where(function ($sq) use ($search) {
                            $sq->whereRaw('LOWER(country) LIKE ?', ["%{$search}%"])
                                ->orWhereRaw('LOWER(state) LIKE ?', ["%{$search}%"])
                                ->orWhereRaw('LOWER(query) LIKE ?', ["%{$search}%"]);
                        });
                    });
            });
        }

        if (! empty($filters['type']) && strtolower($filters['type']) !== 'all') {
            $query->where('type', strtolower($filters['type']));
        }

        if (! empty($filters['postcode']) && trim($filters['postcode']) !== '') {
            $query->where('postcode', 'like', '%' . trim($filters['postcode']) . '%');
        }

        if ($is_collection) {
            return $query
                ->orderBy('name', 'asc')
                ->get();
        }

        return $query
            ->orderBy('name', 'asc')
            ->paginate($perPage);
    }

    public function getPaginated(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->getList($filters, $perPage, false);
    }

    public function getFilteredList(array $filters): Collection
    {
        return $this->getList($filters, 0, true);
    }

    public function findById(int $id): ?Institution
    {
        return Institution::query()->with('search')->find($id);
    }

    public function findByCoordinates(float $lat, float $lon): ?Institution
    {
        return Institution::query()->where('latitude', $lat)->where('longitude', $lon)->first();
    }

    public function findByOsm(string $osmId, ?string $osmType): ?Institution
    {
        return Institution::query()
            ->where('osm_id', $osmId)
            ->when($osmType, fn($q) => $q->where('osm_type', $osmType))
            ->first();
    }

    public function upsertRecord(array $rec, int $searchId): Institution
    {
        $institution = null;

        if (isset($rec['latitude'], $rec['longitude']) && $rec['latitude'] !== null && $rec['longitude'] !== null) {
            $institution = $this->findByCoordinates((float) $rec['latitude'], (float) $rec['longitude']);
        }

        if (! $institution && ! empty($rec['osm_id'])) {
            $institution = $this->findByOsm((string) $rec['osm_id'], $rec['osm_type'] ?? null);
        }

        if ($institution) {
            $institution->update(array_filter([
                'address' => $rec['address'] ?? null,
                'city' => $rec['city'] ?? null,
                'state' => $rec['state'] ?? null,
                'postcode' => $rec['postcode'] ?? null,
                'phone' => $rec['phone'] ?? null,
                'website' => $rec['website'] ?? null,
                'search_id' => $searchId,
            ]));

            return $institution->fresh('search');
        }

        return Institution::create(array_merge($rec, [
            'search_id' => $searchId,
        ]))->load('search');
    }

    public function getUnnamedInstitutions(int $limit = 20): Collection
    {
        return Institution::query()
            ->where(function ($query) {
                $query->whereNull('name')
                    ->orWhere('name', '')
                    ->orWhere('name', 'Unnamed')
                    ->orWhere('name', 'Unnamed School');
            })
            ->where('is_checked', 0)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->limit($limit)
            ->get();
    }

    public function updateInstitutionName(Institution $institution, array $data): Institution
    {
        $institution->update($data);

        return $institution->fresh();
    }

    public function update(Institution $institution, array $data): Institution
    {
        $institution->update($data);

        return $institution->fresh();
    }
}
