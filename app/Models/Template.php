<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\BuiltinTemplateGeneratorService;
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
        'theme',
    ];

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function getPreviewUrlAttribute(): ?string
    {
        return $this->preview_path ? '/storage/' . $this->preview_path : null;
    }

    /**
     * Desain template (warna, style_type, ukuran foto) yang dikirim ke aplikasi.
     * Berasal dari definisi yang SAMA dengan yang dipakai generator PDF, sehingga
     * pemilih template, pratinjau, dan PDF tidak bisa berbeda desain.
     */
    public function getThemeAttribute(): array
    {
        return BuiltinTemplateGeneratorService::resolveTheme($this->template_key);
    }
}
