<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->default('PPSI');
            $table->string('site_tagline')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('email')->nullable();
            $table->string('telepon')->nullable();
            $table->text('alamat')->nullable();
            $table->text('footer_text')->nullable();
            $table->json('social_links')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();
            $table->timestamps();
        });

        Schema::create('landing_heroes', function (Blueprint $table) {
            $table->id();
            $table->string('badge')->nullable();
            $table->string('title');
            $table->string('highlighted_text')->nullable();
            $table->text('description')->nullable();
            $table->string('primary_cta_label')->nullable();
            $table->string('primary_cta_url')->nullable();
            $table->string('secondary_cta_label')->nullable();
            $table->string('secondary_cta_url')->nullable();
            $table->string('background_image_path')->nullable();
            $table->string('hero_image_path')->nullable();
            $table->string('video_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('landing_history_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->longText('body');
            $table->string('image_path')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('landing_organization_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('position');
            $table->string('group_name')->nullable();
            $table->string('photo_path')->nullable();
            $table->text('short_bio')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('landing_activities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('summary');
            $table->string('image_path')->nullable();
            $table->date('event_date')->nullable();
            $table->string('location')->nullable();
            $table->string('detail_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('landing_support_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo_path')->nullable();
            $table->string('website_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('landing_kta_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('checklist_text')->nullable();
            $table->string('banner_image_path')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_kta_blocks');
        Schema::dropIfExists('landing_support_items');
        Schema::dropIfExists('landing_activities');
        Schema::dropIfExists('landing_organization_items');
        Schema::dropIfExists('landing_history_items');
        Schema::dropIfExists('landing_heroes');
        Schema::dropIfExists('public_site_settings');
    }
};
