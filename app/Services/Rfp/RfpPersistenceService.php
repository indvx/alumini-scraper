<?php

namespace App\Services\Rfp;

use App\Data\Rfp\RfpData;
use App\Models\RFP;

class RfpPersistenceService
{
    public function __construct(
        private AlumniRfpMatcher $alumniRfpMatcher,
    ) {}

    /**
     * Persist or update an array of RfpData DTOs in database.
     *
     * @param  array<int, RfpData>  $rfps
     * @return array<int, RFP>
     */
    public function saveMany(array $rfps): array
    {
        $savedModels = [];

        foreach ($rfps as $rfpData) {
            // if (! $this->alumniRfpMatcher->isRelevant($rfpData)) {
            //     continue;
            // }

            $savedModels[] = $this->save($rfpData);
        }

        return $savedModels;
    }

    public function save(RfpData $rfpData): RFP
    {
        $sourceValue = $rfpData->source instanceof \BackedEnum
            ? $rfpData->source->value
            : (string) ($rfpData->source ?? 'bonfire');

        $rfp = RFP::firstOrNew([
            'project_id' => $rfpData->projectId,
            'source' => $sourceValue,
        ]);

        $attributes = [
            'private_project_id' => $rfpData->privateProjectId,
            'reference_id' => $rfpData->referenceId,
            'title' => $rfpData->title,
            'description' => $rfpData->description,
            'department' => $rfpData->department,
            'date_open' => $rfpData->dateOpen ? date('Y-m-d H:i:s', strtotime($rfpData->dateOpen)) : null,
            'date_close' => $rfpData->dateClose ? date('Y-m-d H:i:s', strtotime($rfpData->dateClose)) : null,
            'is_public_award' => $rfpData->isPublicAward,
            'status' => $rfpData->status->value,
            'portal_url' => $rfpData->portalUrl,
            'opportunity_url' => $rfpData->opportunityUrl,
            'raw_data' => $rfpData->rawData,
        ];

        // Do not erase previously resolved IDs when a later scrape cannot identify them.
        if ($rfpData->institutionId !== null) {
            $attributes['institution_id'] = $rfpData->institutionId;
        }

        if ($rfpData->rfpsPlatformId !== null) {
            $attributes['rfps_platform_id'] = $rfpData->rfpsPlatformId;
        }

        $rfp->fill($attributes);
        $rfp->save();

        return $rfp;
    }
}
