<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LocationSearch extends Model
{
    use HasFactory;

    protected $fillable = [
        'query',
        'display_name',
        'area_id',
        'place_name',
        'state',
        'country',
        'total_found',
        'searched_at',
    ];

    protected $casts = [
        'area_id' => 'integer',
        'total_found' => 'integer',
        'searched_at' => 'datetime',
    ];

    public function institutions(): HasMany
    {
        return $this->hasMany(Institution::class, 'search_id');
    }
}
