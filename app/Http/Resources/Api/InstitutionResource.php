<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstitutionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'search_id' => $this->search_id,
            'osm_id' => $this->osm_id,
            'osm_type' => $this->osm_type,
            'name' => $this->name,
            'type' => $this->type,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'postcode' => $this->postcode,
            'phone' => $this->phone,
            'website' => $this->website,
            'is_checked' => $this->is_checked,
            'search' => new LocationSearchResource($this->whenLoaded('search')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
