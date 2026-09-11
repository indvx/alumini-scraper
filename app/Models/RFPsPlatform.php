<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RFPsPlatform extends Model
{
    protected $table = 'rfps_platforms';

    protected $fillable = [
        'name',
        'domain',
        'url',
        'platform_type',
        'country',
        'state',
        'city',
        'institution_type',
        'coverage',
        'is_public',
        'requires_login',
        'last_checked',
        'status',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'requires_login' => 'boolean',
        'last_checked' => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function ($q, $search) {
                $keywords = array_filter(explode(' ', trim($search)));
                if (! empty($keywords)) {
                    $q->where(function ($sub) use ($keywords) {
                        foreach ($keywords as $word) {
                            $sub->orWhere(function ($wQuery) use ($word) {
                                $wQuery->where('name', 'like', "%{$word}%")
                                    ->orWhere('domain', 'like', "%{$word}%")
                                    ->orWhere('url', 'like', "%{$word}%")
                                    ->orWhere('platform_type', 'like', "%{$word}%")
                                    ->orWhere('institution_type', 'like', "%{$word}%")
                                    ->orWhere('city', 'like', "%{$word}%")
                                    ->orWhere('state', 'like', "%{$word}%")
                                    ->orWhere('country', 'like', "%{$word}%");
                            });
                        }
                    });
                }
            })
            ->when(($filters['platform_type'] ?? 'all') !== 'all' && ! empty($filters['platform_type']), function ($q) use ($filters) {
                $q->where('platform_type', $filters['platform_type']);
            })
            ->when(($filters['country'] ?? 'all') !== 'all' && ! empty($filters['country']), function ($q) use ($filters) {
                $q->where('country', $filters['country']);
            })
            ->when(($filters['status'] ?? 'all') !== 'all' && ! empty($filters['status']), function ($q) use ($filters) {
                $q->where('status', $filters['status']);
            });
    }
}
