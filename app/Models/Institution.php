<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Institution extends Model
{
    use HasFactory;

    protected $fillable = [
        'search_id',
        'osm_id',
        'osm_type',
        'name',
        'type',
        'latitude',
        'longitude',
        'address',
        'city',
        'state',
        'country',
        'postcode',
        'phone',
        'website',
        'is_checked',
        'last_view',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'is_checked' => 'boolean',
    ];

    public function search(): BelongsTo
    {
        return $this->belongsTo(LocationSearch::class, 'search_id');
    }

    public function rfpPlatforms(): BelongsToMany
    {
        return $this->belongsToMany(RFPsPlatform::class, 'institution_rfp_platform', 'institution_id', 'rfps_platform_id')
            ->withPivot([
                'id',
                'confidence',
                'status',
                'discovery_method',
                'source_title',
                'source_url',
                'first_verified_at',
                'last_verified_at',
                'notes',
            ])
            ->withTimestamps();
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search_id'] ?? null, fn ($q, $id) => $q->where('search_id', $id))
            ->when($filters['search'] ?? null, function ($q, $term) {
                $term = "%{$term}%";
                $q->where(
                    fn ($sub) => $sub->where('name', 'like', $term)
                        ->orWhere('address', 'like', $term)
                        ->orWhere('city', 'like', $term)
                        ->orWhere('state', 'like', $term)
                        ->orWhere('country', 'like', $term)
                );
            })
            ->when(($filters['type'] ?? null) && strtolower((string) $filters['type']) !== 'all', function ($q, $type) {
                $q->where('type', strtolower((string) $type));
            })
            ->when($filters['postcode'] ?? null, fn ($q, $zip) => $q->where('postcode', 'like', "%{$zip}%"));
    }
}
