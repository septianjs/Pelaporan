<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'location_id',
        'condition', // 'clean' or 'needs_attention'
        'notes',
        'problem',
        'solution',
    ];

    protected $appends = [
        'condition_label',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function reportPhotos(): HasMany
    {
        return $this->hasMany(ReportPhoto::class);
    }

    public function getConditionLabelAttribute(): string
    {
        return $this->condition === 'clean'
            ? 'Kondisi Sudah Bersih dan Rapi'
            : 'Kondisi Masih Perlu Penanganan Khusus';
    }
}

