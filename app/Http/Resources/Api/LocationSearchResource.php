<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationSearchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'query' => $this->query,
            'display_name' => $this->display_name,
            'area_id' => $this->area_id,
            'place_name' => $this->place_name,
            'state' => $this->state,
            'country' => $this->country,
            'total_found' => $this->total_found,
            'searched_at' => $this->searched_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
