<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'template_key',
        'description',
        'type',
        'pptx_path',
        'preview_path',
        'active',
    ];

    protected $hidden = ['pptx_path'];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $appends = [
        'preview_url',
    ];

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function getPreviewUrlAttribute(): ?string
    {
        if ($this->preview_path) {
            return asset('storage/' . $this->preview_path);
        }
        return null;
    }
}

