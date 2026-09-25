<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\RFPsPlatform;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InstitutionRFPPlatformController extends Controller
{
    /**
     * Attach an RFP Platform to an Institution.
     */
    public function storeInstitutionPlatform(Request $request, Institution $institution): RedirectResponse
    {
        $validated = $request->validate([
            'rfps_platform_id' => 'required|exists:rfps_platforms,id',
            'confidence' => 'nullable|integer|between:0,100',
            'status' => 'nullable|in:active,inactive,pending',
            'discovery_method' => 'nullable|string|max:255',
            'source_title' => 'nullable|string|max:255',
            'source_url' => 'nullable|url|max:2048',
            'first_verified_at' => 'nullable|date',
            'last_verified_at' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ]);

        $platformId = $validated['rfps_platform_id'];
        unset($validated['rfps_platform_id']);

        if (! isset($validated['status'])) {
            $validated['status'] = 'active';
        }

        if (! isset($validated['confidence'])) {
            $validated['confidence'] = 100;
        }

        $institution->rfpPlatforms()->syncWithoutDetaching([
            $platformId => $validated,
        ]);

        $platform = RFPsPlatform::find($platformId);

        return back()->with('success', "Relationship with RFP Platform '{$platform?->name}' added successfully.");
    }

    /**
     * Detach an RFP Platform from an Institution.
     */
    public function destroyInstitutionPlatform(Institution $institution, RFPsPlatform $rfpsPlatform): RedirectResponse
    {
        $institution->rfpPlatforms()->detach($rfpsPlatform->id);

        return back()->with('success', "RFP Platform '{$rfpsPlatform->name}' removed from institution.");
    }

    /**
     * Attach an Institution to an RFP Platform.
     */
    public function storePlatformInstitution(Request $request, RFPsPlatform $rfpsPlatform): RedirectResponse
    {
        $validated = $request->validate([
            'institution_id' => 'required|exists:institutions,id',
            'confidence' => 'nullable|integer|between:0,100',
            'status' => 'nullable|in:active,inactive,pending',
            'discovery_method' => 'nullable|string|max:255',
            'source_title' => 'nullable|string|max:255',
            'source_url' => 'nullable|url|max:2048',
            'first_verified_at' => 'nullable|date',
            'last_verified_at' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ]);

        $institutionId = $validated['institution_id'];
        unset($validated['institution_id']);

        if (! isset($validated['status'])) {
            $validated['status'] = 'active';
        }

        if (! isset($validated['confidence'])) {
            $validated['confidence'] = 100;
        }

        $rfpsPlatform->rfpInstitutions()->syncWithoutDetaching([
            $institutionId => $validated,
        ]);

        $institution = Institution::find($institutionId);

        return back()->with('success', "Relationship with Institution '{$institution?->name}' added successfully.");
    }

    /**
     * Detach an Institution from an RFP Platform.
     */
    public function destroyPlatformInstitution(RFPsPlatform $rfpsPlatform, Institution $institution): RedirectResponse
    {
        $rfpsPlatform->rfpInstitutions()->detach($institution->id);

        return back()->with('success', "Institution '{$institution->name}' removed from platform.");
    }

    /**
     * Import related institution URLs or CSV into RFP Platform.
     */
    public function importPlatformInstitutions(Request $request, RFPsPlatform $rfpsPlatform): RedirectResponse
    {
        $validated = $request->validate([
            'csv_file' => 'nullable|file|mimes:csv,txt|max:5120',
            'confidence' => 'nullable|integer|between:0,100',
            'status' => 'nullable|in:active,inactive,pending',
            'discovery_method' => 'nullable|string|max:255',
        ]);

        $defaultConfidence = $validated['confidence'] ?? 98;
        $defaultStatus = $validated['status'] ?? 'active';
        $defaultDiscovery = $validated['discovery_method'] ?? 'Bulk Import / URL Import';

        $importedCount = 0;
        $today = date('Y-m-d');

        if ($request->hasFile('csv_file') && $request->file('csv_file')->isValid()) {
            $handle = fopen($request->file('csv_file')->getRealPath(), 'r');
            $header = fgetcsv($handle);

            if ($header) {
                // Sanitize header keys
                $headerMap = array_map(function ($col) {
                    // Remove BOM if present and trim/lowercase
                    $col = preg_replace('/[\x{EF}\x{BB}\x{BF}]/u', '', $col);

                    return strtolower(trim($col));
                }, $header);

                while (($row = fgetcsv($handle)) !== false) {
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    $countHeader = count($headerMap);
                    $countRow = count($row);
                    $data = array_combine(
                        array_slice($headerMap, 0, min($countHeader, $countRow)),
                        array_slice($row, 0, min($countHeader, $countRow))
                    );

                    $instId = $data['id'] ?? $data['institution_id'] ?? null;

                    // Extract Portal / Source / Evidence URL from columns
                    $sourceUrl = '';
                    foreach ($data as $key => $val) {
                        if (str_contains($key, 'url') || str_contains($key, 'link') || str_contains($key, 'portal')) {
                            $valTrim = trim($val);
                            if (filter_var($valTrim, FILTER_VALIDATE_URL)) {
                                $sourceUrl = $valTrim;
                                break;
                            }
                        }
                    }

                    // Skip rows that do not have a valid portal URL
                    if (empty($sourceUrl) || ! filter_var($sourceUrl, FILTER_VALIDATE_URL)) {
                        continue;
                    }

                    // Extract Source Title & Notes
                    $sourceTitle = trim($data['verification / source'] ?? $data['verification_source'] ?? $data['source_title'] ?? $data['source title'] ?? $data['source'] ?? $data['verification'] ?? '');
                    $statusText = trim($data['bonfire status'] ?? $data['status'] ?? '');
                    $notesFromCsv = trim($data['notes'] ?? '');
                    $rowNotes = implode(' | ', array_filter([$statusText, $notesFromCsv]));

                    $rowConfidence = isset($data['confidence']) && is_numeric($data['confidence']) ? (int) $data['confidence'] : $defaultConfidence;
                    $rowStatus = in_array(strtolower($statusText), ['active', 'inactive', 'pending']) ? strtolower($statusText) : $defaultStatus;

                    $institution = null;

                    // Match by Primary Key ID
                    if ($instId && is_numeric($instId)) {
                        $institution = Institution::find((int) $instId);
                    }
                    if ($institution) {
                        $rfpsPlatform->rfpInstitutions()->syncWithoutDetaching([
                            $institution->id => [
                                'confidence' => $rowConfidence,
                                'status' => $rowStatus,
                                'discovery_method' => $defaultDiscovery,
                                'source_title' => $sourceTitle ?: 'Imported Source',
                                'source_url' => $sourceUrl,
                                'first_verified_at' => $today,
                                'last_verified_at' => $today,
                                'notes' => $rowNotes ?: 'Imported via CSV file.',
                            ],
                        ]);
                        $importedCount++;
                    }
                }
            }
            fclose($handle);
        }

        if ($importedCount === 0) {
            return back()->with('error', 'No valid institution records or URLs found in the CSV to import.');
        }

        return back()->with('success', "Successfully imported and linked {$importedCount} institution relationship(s) to '{$rfpsPlatform->name}'.");
    }
}
