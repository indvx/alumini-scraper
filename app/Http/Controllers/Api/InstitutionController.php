<?php

namespace App\Http\Controllers\Api;

use App\Actions\SearchInstitutionsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\InstitutionFilterRequest;
use App\Http\Requests\Api\InstitutionSearchRequest;
use App\Http\Resources\Api\InstitutionResource;
use App\Http\Resources\Api\LocationSearchResource;
use App\Models\Institution;
use App\Repositories\Contracts\InstitutionRepositoryInterface;
use App\Repositories\Contracts\LocationSearchRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InstitutionController extends Controller
{
    public function __construct(
        protected InstitutionRepositoryInterface $institutionRepository,
        protected LocationSearchRepositoryInterface $searchRepository
    ) {}

    public function search(InstitutionSearchRequest $request, SearchInstitutionsAction $action): JsonResponse
    {
        $validated = $request->validated();
        $result = $action->execute(
            query: $validated['location_query'],
            forceRefresh: (bool) ($validated['force_refresh'] ?? false)
        );

        $collection = InstitutionResource::collection($result['institutions']);

        return response()->json([
            'search' => new LocationSearchResource($result['search']),
            'institutions' => $collection,
            'schools' => $collection,
            'cached' => $result['cached'],
        ]);
    }

    public function index(InstitutionFilterRequest $request): JsonResponse
    {
        $pageSize = $request->integer('page_size', 20);
        $institutions = $this->institutionRepository->getPaginated($request->validated(), $pageSize);

        return response()->json([
            'total' => $institutions->total(),
            'page' => $institutions->currentPage(),
            'page_size' => $institutions->perPage(),
            'total_pages' => $institutions->lastPage(),
            'items' => InstitutionResource::collection($institutions->items()),
        ]);
    }

    public function show(Institution $institution): JsonResponse
    {
        return response()->json(new InstitutionResource($institution->load('search')));
    }

    public function searches(Request $request): JsonResponse
    {
        $limit = min(100, max(1, (int) $request->query('limit', 20)));
        $searches = $this->searchRepository->getRecentSearches($limit);

        return response()->json(LocationSearchResource::collection($searches));
    }

    public function exportCsv(InstitutionFilterRequest $request): StreamedResponse
    {
        $institutions = $this->institutionRepository->getFilteredList($request->validated());

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="institutions_export.csv"',
        ];

        $callback = function () use ($institutions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Type', 'Latitude', 'Longitude', 'Address', 'City', 'State', 'Postcode', 'Phone', 'Website', 'OSM ID']);

            foreach ($institutions as $s) {
                fputcsv($file, [
                    $s->id,
                    $s->name,
                    $s->type,
                    $s->latitude,
                    $s->longitude,
                    $s->address,
                    $s->city,
                    $s->state,
                    $s->postcode,
                    $s->phone,
                    $s->website,
                    $s->osm_id,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
