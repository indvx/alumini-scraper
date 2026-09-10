<?php

namespace App\Repositories\Contracts;

use App\Models\LocationSearch;
use Illuminate\Database\Eloquent\Collection;

interface LocationSearchRepositoryInterface
{
    public function findByExactQuery(string $query): ?LocationSearch;

    public function findByAreaOrDisplayName(?int $areaId, ?string $displayName): ?LocationSearch;

    public function createSearch(array $data): LocationSearch;

    public function updateSearch(LocationSearch $search, array $data): LocationSearch;

    public function getRecentSearches(int $limit = 20): Collection;
}
