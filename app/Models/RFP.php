<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RFP extends Model
{
    use HasFactory;

    protected $table = 'rfps';

    protected $fillable = [
        'institution_id',
        'rfps_platform_id',
        'project_id',
        'private_project_id',
        'reference_id',
        'title',
        'description',
        'department',
        'date_open',
        'date_close',
        'is_public_award',
        'status',
        'source',
        'portal_url',
        'opportunity_url',
        'raw_data',
    ];

    protected $casts = [
        'date_open' => 'datetime',
        'date_close' => 'datetime',
        'is_public_award' => 'boolean',
        'raw_data' => 'array',
    ];

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(RFPsPlatform::class, 'rfps_platform_id');
    }
}
