<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_location_id',
        'photo_type', // 'photo_1' or 'photo_2'
        'file_path',
    ];

    protected $appends = [
        'photo_url',
    ];

    public function reportLocation(): BelongsTo
    {
        return $this->belongsTo(ReportLocation::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if ($this->file_path) {
            return asset('storage/' . $this->file_path);
        }
        return null;
    }
}

