<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingActivity extends Model
{
    use HasFactory;

    protected $table = 'landing_activities';

    protected $fillable = [
        'title',
        'summary',
        'image_path',
        'event_date',
        'location',
        'detail_url',
        'sort_order',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
