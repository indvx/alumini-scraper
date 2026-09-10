<?php

namespace App\Repositories\Eloquent;

use App\Models\Institution;
use App\Repositories\Contracts\InstitutionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class InstitutionRepository implements InstitutionRepositoryInterface
{
    public function getPaginated(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return Institution::query()
            ->with('search')
            ->filter($filters)
            ->orderBy('name', 'asc')
            ->paginate($perPage);
    }

    public function getFilteredList(array $filters): Collection
    {
        return Institution::query()
            ->with('search')
            ->filter($filters)
            ->orderBy('name', 'asc')
            ->get();
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
            ->when($osmType, fn ($q) => $q->where('osm_type', $osmType))
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
}
