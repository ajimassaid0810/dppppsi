<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Kecamatan;
use App\Models\KotaKab;
use App\Models\LandingActivity;
use App\Models\LandingHero;
use App\Models\LandingHistoryItem;
use App\Models\LandingKtaBlock;
use App\Models\LandingOrganizationItem;
use App\Models\LandingSupportItem;
use App\Models\Pagoruan;
use App\Models\Provinsi;
use App\Models\PublicSiteSetting;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PublicLandingController extends Controller
{
    public function index(): Response
    {
        $settings = PublicSiteSetting::query()->first();
        $hero = LandingHero::query()->published()->orderBy('sort_order')->orderBy('id')->first();
        $historyItems = LandingHistoryItem::query()->published()->orderBy('sort_order')->orderBy('id')->get();
        $organizationItems = LandingOrganizationItem::query()->published()->orderBy('sort_order')->orderBy('id')->get();
        $activities = LandingActivity::query()->published()->orderBy('sort_order')->orderBy('id')->get();
        $supportItems = LandingSupportItem::query()->published()->orderBy('sort_order')->orderBy('id')->get();
        $ktaBlock = LandingKtaBlock::query()->published()->orderBy('sort_order')->orderBy('id')->first();

        return Inertia::render('Public/LandingPage', [
            'site' => $this->serializeSiteSettings($settings),
            'hero' => $hero ? $this->serializeHero($hero) : $this->defaultHero(),
            'historyItems' => $historyItems->map(fn (LandingHistoryItem $item) => $this->serializeHistoryItem($item))->all(),
            'organizationItems' => $organizationItems->map(fn (LandingOrganizationItem $item) => $this->serializeOrganizationItem($item))->all(),
            'activities' => $activities->map(fn (LandingActivity $item) => $this->serializeActivity($item))->all(),
            'supportItems' => $supportItems->map(fn (LandingSupportItem $item) => $this->serializeSupportItem($item))->all(),
            'ktaBlock' => $ktaBlock ? $this->serializeKtaBlock($ktaBlock) : $this->defaultKtaBlock(),
            'stats' => [
                'dpw' => Provinsi::count(),
                'dpd' => KotaKab::count(),
                'dpc' => Kecamatan::count(),
                'pagoruan' => Pagoruan::count(),
                'anggota' => Anggota::count(),
            ],
        ]);
    }

    public function media(string $collection, int $id, string $field)
    {
        [$record, $path] = $this->resolveMediaRecord($collection, $id, $field);

        abort_if(! $record || ! $path || ! Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path);
    }

    private function serializeSiteSettings(?PublicSiteSetting $settings): array
    {
        $socialLinks = $settings?->social_links ?? [];

        return [
            'id' => $settings?->id,
            'site_name' => $settings?->site_name ?? 'Persatuan Pencak Silat Indonesia',
            'site_tagline' => $settings?->site_tagline ?? 'Melestarikan pencak silat, membangun jejaring anggota, dan membuka pendaftaran secara nasional.',
            'email' => $settings?->email,
            'telepon' => $settings?->telepon,
            'alamat' => $settings?->alamat,
            'footer_text' => $settings?->footer_text ?? 'PPSI hadir untuk menguatkan organisasi, kader, dan pelestarian pencak silat Indonesia.',
            'logo_url' => $settings?->logo_path ? route('landing.media', ['collection' => 'settings', 'id' => $settings->id, 'field' => 'logo_path'], false) : null,
            'favicon_url' => $settings?->favicon_path ? route('landing.media', ['collection' => 'settings', 'id' => $settings->id, 'field' => 'favicon_path'], false) : null,
            'seo_title' => $settings?->seo_title ?? 'PPSI',
            'seo_description' => $settings?->seo_description,
            'seo_keywords' => $settings?->seo_keywords,
            'social_links' => [
                'facebook' => $socialLinks['facebook'] ?? null,
                'instagram' => $socialLinks['instagram'] ?? null,
                'youtube' => $socialLinks['youtube'] ?? null,
                'linkedin' => $socialLinks['linkedin'] ?? null,
            ],
        ];
    }

    private function serializeHero(LandingHero $hero): array
    {
        return [
            'id' => $hero->id,
            'badge' => $hero->badge,
            'title' => $hero->title,
            'highlighted_text' => $hero->highlighted_text,
            'description' => $hero->description,
            'primary_cta_label' => $hero->primary_cta_label,
            'primary_cta_url' => $hero->primary_cta_url,
            'secondary_cta_label' => $hero->secondary_cta_label,
            'secondary_cta_url' => $hero->secondary_cta_url,
            'background_image_url' => $hero->background_image_path ? route('landing.media', ['collection' => 'heroes', 'id' => $hero->id, 'field' => 'background_image_path'], false) : null,
            'hero_image_url' => $hero->hero_image_path ? route('landing.media', ['collection' => 'heroes', 'id' => $hero->id, 'field' => 'hero_image_path'], false) : null,
            'video_url' => $hero->video_url,
        ];
    }

    private function serializeHistoryItem(LandingHistoryItem $item): array
    {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'subtitle' => $item->subtitle,
            'body' => $item->body,
            'icon' => $item->icon,
            'image_url' => $item->image_path ? route('landing.media', ['collection' => 'history', 'id' => $item->id, 'field' => 'image_path'], false) : null,
        ];
    }

    private function serializeOrganizationItem(LandingOrganizationItem $item): array
    {
        $documentUrl = $item->photo_path
            ? route('landing.media', ['collection' => 'organization', 'id' => $item->id, 'field' => 'photo_path'], false)
            : null;

        return [
            'id' => $item->id,
            'name' => $item->name,
            'position' => $item->position,
            'group_name' => $item->group_name,
            'short_bio' => $item->short_bio,
            'document_url' => $documentUrl,
            'photo_url' => $documentUrl,
            'media_type' => $this->detectMediaType($item->photo_path),
        ];
    }

    private function serializeActivity(LandingActivity $item): array
    {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'summary' => $item->summary,
            'event_date' => $item->event_date?->toDateString(),
            'location' => $item->location,
            'detail_url' => $item->detail_url,
            'image_url' => $item->image_path ? route('landing.media', ['collection' => 'activities', 'id' => $item->id, 'field' => 'image_path'], false) : null,
        ];
    }

    private function serializeSupportItem(LandingSupportItem $item): array
    {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'website_url' => $item->website_url,
            'logo_url' => $item->logo_path ? route('landing.media', ['collection' => 'support', 'id' => $item->id, 'field' => 'logo_path'], false) : null,
        ];
    }

    private function serializeKtaBlock(LandingKtaBlock $item): array
    {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'description' => $item->description,
            'checklist_items' => $this->splitChecklistText($item->checklist_text),
            'cta_label' => $item->cta_label,
            'cta_url' => $item->cta_url,
            'banner_image_url' => $item->banner_image_path ? route('landing.media', ['collection' => 'kta', 'id' => $item->id, 'field' => 'banner_image_path'], false) : null,
        ];
    }

    private function splitChecklistText(?string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $value))
            ->map(fn (?string $line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();
    }

    private function defaultHero(): array
    {
        return [
            'id' => null,
            'badge' => 'Pusat Kendali Organisasi',
            'title' => 'Persatuan Pencak Silat',
            'highlighted_text' => 'Indonesia',
            'description' => 'PPSI membuka informasi publik yang lebih rapi, jalur pendaftaran anggota yang langsung, dan tampilan organisasi yang lebih layak diakses nasional.',
            'primary_cta_label' => 'Daftar Anggota',
            'primary_cta_url' => '/pendaftaran-anggota',
            'secondary_cta_label' => 'Cek Pengajuan',
            'secondary_cta_url' => '/cek-pengajuan',
            'background_image_url' => null,
            'hero_image_url' => null,
            'video_url' => null,
        ];
    }

    private function defaultKtaBlock(): array
    {
        return [
            'id' => null,
            'title' => 'Daftar Anggota PPSI',
            'description' => 'Lengkapi identitas, wilayah, dan dokumen pendukung. Setelah diverifikasi, pengajuan Anda akan dikonversi menjadi data anggota resmi.',
            'checklist_items' => [
                'Siapkan foto terbaru',
                'Unggah KK dan identitas',
                'Pilih wilayah DPD yang sesuai',
                'Simpan kode pengajuan untuk pelacakan',
            ],
            'cta_label' => 'Mulai Pendaftaran',
            'cta_url' => '/pendaftaran-anggota',
            'banner_image_url' => null,
        ];
    }

    private function detectMediaType(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf'
            ? 'pdf'
            : 'image';
    }

    private function resolveMediaRecord(string $collection, int $id, string $field): array
    {
        $map = [
            'settings' => [PublicSiteSetting::class, ['logo_path', 'favicon_path']],
            'heroes' => [LandingHero::class, ['background_image_path', 'hero_image_path']],
            'history' => [LandingHistoryItem::class, ['image_path']],
            'organization' => [LandingOrganizationItem::class, ['photo_path']],
            'activities' => [LandingActivity::class, ['image_path']],
            'support' => [LandingSupportItem::class, ['logo_path']],
            'kta' => [LandingKtaBlock::class, ['banner_image_path']],
        ];

        abort_unless(isset($map[$collection]), 404);

        [$modelClass, $fields] = $map[$collection];

        abort_unless(in_array($field, $fields, true), 404);

        $record = $modelClass::query()->findOrFail($id);

        return [$record, $record->{$field}];
    }
}
