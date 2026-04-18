<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicSiteSetting extends Model
{
    use HasFactory;

    protected $table = 'public_site_settings';

    protected $fillable = [
        'site_name',
        'site_tagline',
        'logo_path',
        'favicon_path',
        'email',
        'telepon',
        'alamat',
        'footer_text',
        'social_links',
        'seo_title',
        'seo_description',
        'seo_keywords',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
        ];
    }
}
