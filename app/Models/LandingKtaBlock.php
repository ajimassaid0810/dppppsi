<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingKtaBlock extends Model
{
    use HasFactory;

    protected $table = 'landing_kta_blocks';

    protected $fillable = [
        'title',
        'description',
        'checklist_text',
        'banner_image_path',
        'cta_label',
        'cta_url',
        'sort_order',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
