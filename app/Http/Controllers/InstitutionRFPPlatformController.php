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

        $rfpsPlatform->institutions()->syncWithoutDetaching([
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
        $rfpsPlatform->institutions()->detach($institution->id);

        return back()->with('success', "Institution '{$institution->name}' removed from platform.");
    }
}
