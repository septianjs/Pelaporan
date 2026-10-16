<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_number',
        'inspection_date',
        'officer_name',
        'officer_photo',
        'template_id',
        'pptx_path',
        'pdf_path',
        'status',
    ];

    protected $casts = [
        'inspection_date' => 'date',
    ];

    protected $appends = [
        'officer_photo_url',
        'pptx_url',
        'pdf_url',
        'day_name',
        'formatted_date',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function reportLocations(): HasMany
    {
        return $this->hasMany(ReportLocation::class)->orderBy('id');
    }

    public function photos(): HasManyThrough
    {
        return $this->hasManyThrough(ReportPhoto::class, ReportLocation::class);
    }

    public function getOfficerPhotoUrlAttribute(): ?string
    {
        if ($this->officer_photo) {
            return asset('storage/' . $this->officer_photo);
        }
        return null;
    }

    public function getPptxUrlAttribute(): ?string
    {
        if ($this->pptx_path) {
            return asset('storage/' . $this->pptx_path);
        }
        return null;
    }

    public function getPdfUrlAttribute(): ?string
    {
        if ($this->pdf_path) {
            return asset('storage/' . $this->pdf_path);
        }
        return null;
    }

    public function getDayNameAttribute(): string
    {
        if (!$this->inspection_date) {
            return '';
        }
        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];
        $enDay = Carbon::parse($this->inspection_date)->format('l');
        return $days[$enDay] ?? $enDay;
    }

    public function getFormattedDateAttribute(): string
    {
        if (!$this->inspection_date) {
            return '';
        }
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $carbon = Carbon::parse($this->inspection_date);
        $monthName = $months[(int)$carbon->format('n')];
        return "{$this->day_name}, {$carbon->format('j')} {$monthName} {$carbon->format('Y')}";
    }

    /**
     * Generate automatic report number: LAP-BPSDM-{YEAR}-{SEQUENCE}
     * Example: LAP-BPSDM-2026-0001
     */
    public static function generateNextNumber(?int $year = null): string
    {
        $year = $year ?: (int)date('Y');
        $prefix = "LAP-BPSDM-{$year}-";

        $lastReport = self::where('report_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastReport) {
            $lastNumberPart = substr($lastReport->report_number, strlen($prefix));
            $seq = (int)$lastNumberPart + 1;
        } else {
            $seq = 1;
        }

        return $prefix . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }
}

