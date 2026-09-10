<?php

namespace App\Repositories\Eloquent;

use App\Models\RFPsPlatform;
use App\Repositories\Contracts\RFPsPlatformRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RFPsPlatformRepository implements RFPsPlatformRepositoryInterface
{
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return RFPsPlatform::query()
            ->filter($filters)
            ->orderBy('name', 'asc')
            ->paginate($perPage);
    }

    public function getFilteredList(array $filters): Collection
    {
        return RFPsPlatform::query()
            ->filter($filters)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function findById(int $id): ?RFPsPlatform
    {
        return RFPsPlatform::query()->find($id);
    }

    public function create(array $data): RFPsPlatform
    {
        return RFPsPlatform::create($data);
    }

    public function update(RFPsPlatform $platform, array $data): RFPsPlatform
    {
        $platform->update($data);

        return $platform->fresh();
    }

    public function delete(RFPsPlatform $platform): bool
    {
        return (bool) $platform->delete();
    }
}
