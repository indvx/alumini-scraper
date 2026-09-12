<?php

namespace App\Repositories\Contracts;

use App\Models\Institution;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface InstitutionRepositoryInterface
{
    public function getPaginated(array $filters, int $perPage = 20): LengthAwarePaginator;

    public function getFilteredList(array $filters): Collection;

    public function findById(int $id): ?Institution;

    public function findByCoordinates(float $lat, float $lon): ?Institution;

    public function findByOsm(string $osmId, ?string $osmType): ?Institution;

    public function upsertRecord(array $rec, int $searchId): Institution;

    public function getUnnamedInstitutions(int $limit = 20): Collection;

    public function updateInstitutionName(Institution $institution, array $data): Institution;

    public function update(Institution $institution, array $data): Institution;
}
