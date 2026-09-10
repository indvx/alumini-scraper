<?php

namespace App\Repositories\Eloquent;

use App\Models\LocationSearch;
use App\Repositories\Contracts\LocationSearchRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class LocationSearchRepository implements LocationSearchRepositoryInterface
{
    public function findByExactQuery(string $query): ?LocationSearch
    {
        return LocationSearch::query()
            ->with('institutions')
            ->whereRaw('LOWER(query) = ?', [strtolower(trim($query))])
            ->latest('searched_at')
            ->first();
    }

    public function findByAreaOrDisplayName(?int $areaId, ?string $displayName): ?LocationSearch
    {
        return LocationSearch::query()
            ->with('institutions')
            ->when($areaId, fn ($q) => $q->where('area_id', $areaId))
            ->when($displayName, fn ($q) => $q->orWhereRaw('LOWER(display_name) = ?', [strtolower(trim($displayName))]))
            ->latest('searched_at')
            ->first();
    }

    public function createSearch(array $data): LocationSearch
    {
        return LocationSearch::create($data);
    }

    public function updateSearch(LocationSearch $search, array $data): LocationSearch
    {
        $search->update($data);

        return $search->fresh('institutions');
    }

    public function getRecentSearches(int $limit = 20): Collection
    {
        return LocationSearch::query()
            ->latest('searched_at')
            ->limit($limit)
            ->get();
    }
}
