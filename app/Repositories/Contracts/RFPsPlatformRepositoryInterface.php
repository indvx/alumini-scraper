<?php

namespace App\Repositories\Contracts;

use App\Models\RFPsPlatform;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface RFPsPlatformRepositoryInterface
{
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function getFilteredList(array $filters): Collection;

    public function findById(int $id): ?RFPsPlatform;

    public function create(array $data): RFPsPlatform;

    public function update(RFPsPlatform $platform, array $data): RFPsPlatform;

    public function delete(RFPsPlatform $platform): bool;
}
