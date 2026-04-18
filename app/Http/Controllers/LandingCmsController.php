<?php

namespace App\Http\Controllers;

use App\Models\LandingActivity;
use App\Models\LandingHero;
use App\Models\LandingHistoryItem;
use App\Models\LandingKtaBlock;
use App\Models\LandingOrganizationItem;
use App\Models\LandingSupportItem;
use App\Models\PublicSiteSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class LandingCmsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('LandingCms/Index', [
            'settings' => $this->serializeSettings(PublicSiteSetting::query()->first()),
            'heroes' => LandingHero::query()->orderBy('sort_order')->orderBy('id')->get()->map(fn (LandingHero $item) => $this->serializeHero($item))->all(),
            'historyItems' => LandingHistoryItem::query()->orderBy('sort_order')->orderBy('id')->get()->map(fn (LandingHistoryItem $item) => $this->serializeHistoryItem($item))->all(),
            'organizationItems' => LandingOrganizationItem::query()->orderBy('sort_order')->orderBy('id')->get()->map(fn (LandingOrganizationItem $item) => $this->serializeOrganizationItem($item))->all(),
            'activities' => LandingActivity::query()->orderBy('sort_order')->orderBy('id')->get()->map(fn (LandingActivity $item) => $this->serializeActivity($item))->all(),
            'supportItems' => LandingSupportItem::query()->orderBy('sort_order')->orderBy('id')->get()->map(fn (LandingSupportItem $item) => $this->serializeSupportItem($item))->all(),
            'ktaBlocks' => LandingKtaBlock::query()->orderBy('sort_order')->orderBy('id')->get()->map(fn (LandingKtaBlock $item) => $this->serializeKtaBlock($item))->all(),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'site_tagline' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:100'],
            'alamat' => ['nullable', 'string'],
            'footer_text' => ['nullable', 'string'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'seo_keywords' => ['nullable', 'string', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'favicon' => ['nullable', 'image', 'max:1024'],
        ]);

        $settings = PublicSiteSetting::query()->first() ?? new PublicSiteSetting();

        $payload = [
            'site_name' => $validated['site_name'],
            'site_tagline' => $validated['site_tagline'] ?? null,
            'email' => $validated['email'] ?? null,
            'telepon' => $validated['telepon'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'footer_text' => $validated['footer_text'] ?? null,
            'seo_title' => $validated['seo_title'] ?? null,
            'seo_description' => $validated['seo_description'] ?? null,
            'seo_keywords' => $validated['seo_keywords'] ?? null,
            'social_links' => [
                'facebook' => $validated['facebook_url'] ?? null,
                'instagram' => $validated['instagram_url'] ?? null,
                'youtube' => $validated['youtube_url'] ?? null,
                'linkedin' => $validated['linkedin_url'] ?? null,
            ],
        ];

        if ($request->hasFile('logo')) {
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }

            $payload['logo_path'] = $request->file('logo')->store('landing/settings', 'public');
        } elseif ($settings->exists) {
            $payload['logo_path'] = $settings->logo_path;
        }

        if ($request->hasFile('favicon')) {
            if ($settings->favicon_path) {
                Storage::disk('public')->delete($settings->favicon_path);
            }

            $payload['favicon_path'] = $request->file('favicon')->store('landing/settings', 'public');
        } elseif ($settings->exists) {
            $payload['favicon_path'] = $settings->favicon_path;
        }

        $settings->fill($payload);
        $settings->save();

        return redirect()->route('landing-cms.index')->with('success', 'Pengaturan landing page berhasil disimpan.');
    }

    public function storeSection(Request $request, string $section): RedirectResponse
    {
        [$modelClass, $payload] = $this->prepareSectionPayload($request, $section);

        $modelClass::query()->create($payload);

        return redirect()->route('landing-cms.index')->with('success', $this->sectionLabel($section) . ' berhasil ditambahkan.');
    }

    public function updateSection(Request $request, string $section, int $id): RedirectResponse
    {
        [$modelClass, $payload] = $this->prepareSectionPayload($request, $section, $id);

        /** @var \Illuminate\Database\Eloquent\Model $record */
        $record = $modelClass::query()->findOrFail($id);

        $this->deleteReplacedFiles($request, $section, $record);

        $record->update($payload);

        return redirect()->route('landing-cms.index')->with('success', $this->sectionLabel($section) . ' berhasil diperbarui.');
    }

    public function destroySection(string $section, int $id): RedirectResponse
    {
        $modelClass = $this->sectionModelClass($section);

        /** @var \Illuminate\Database\Eloquent\Model $record */
        $record = $modelClass::query()->findOrFail($id);

        foreach ($this->sectionFileFieldMap($section) as $pathField) {
            if ($record->{$pathField}) {
                Storage::disk('public')->delete($record->{$pathField});
            }
        }

        $record->delete();

        return redirect()->route('landing-cms.index')->with('success', $this->sectionLabel($section) . ' berhasil dihapus.');
    }

    private function prepareSectionPayload(Request $request, string $section, ?int $id = null): array
    {
        $validated = $request->validate($this->sectionValidationRules($section, $id));
        $payload = $validated;

        $payload['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $payload['is_published'] = $request->boolean('is_published');

        $modelClass = $this->sectionModelClass($section);
        /** @var \Illuminate\Database\Eloquent\Model|null $existing */
        $existing = $id ? $modelClass::query()->findOrFail($id) : null;

        foreach ($this->sectionFileFieldMap($section) as $input => $pathField) {
            unset($payload[$input]);

            if ($request->hasFile($input)) {
                $payload[$pathField] = $request->file($input)->store("landing/{$section}", 'public');
                continue;
            }

            if ($existing) {
                $payload[$pathField] = $existing->{$pathField};
            }
        }

        return [$modelClass, $payload];
    }

    private function deleteReplacedFiles(Request $request, string $section, Model $record): void
    {
        foreach ($this->sectionFileFieldMap($section) as $input => $pathField) {
            if ($request->hasFile($input) && $record->{$pathField}) {
                Storage::disk('public')->delete($record->{$pathField});
            }
        }
    }

    private function sectionModelClass(string $section): string
    {
        return match ($section) {
            'heroes' => LandingHero::class,
            'history' => LandingHistoryItem::class,
            'organization' => LandingOrganizationItem::class,
            'activities' => LandingActivity::class,
            'support' => LandingSupportItem::class,
            'kta' => LandingKtaBlock::class,
            default => abort(404),
        };
    }

    private function sectionLabel(string $section): string
    {
        return match ($section) {
            'heroes' => 'Hero',
            'history' => 'Item sejarah',
            'organization' => 'Item struktur organisasi',
            'activities' => 'Kegiatan',
            'support' => 'Support',
            'kta' => 'Blok KTA',
            default => 'Data',
        };
    }

    private function sectionValidationRules(string $section, ?int $id = null): array
    {
        $baseStateRules = [
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['nullable', 'boolean'],
        ];

        return match ($section) {
            'heroes' => [
                ...$baseStateRules,
                'badge' => ['nullable', 'string', 'max:255'],
                'title' => ['required', 'string', 'max:255'],
                'highlighted_text' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'primary_cta_label' => ['nullable', 'string', 'max:255'],
                'primary_cta_url' => ['nullable', 'string', 'max:255'],
                'secondary_cta_label' => ['nullable', 'string', 'max:255'],
                'secondary_cta_url' => ['nullable', 'string', 'max:255'],
                'video_url' => ['nullable', 'url', 'max:255'],
                'background_image' => ['nullable', 'image', 'max:4096'],
                'hero_image' => ['nullable', 'image', 'max:4096'],
            ],
            'history' => [
                ...$baseStateRules,
                'title' => ['required', 'string', 'max:255'],
                'subtitle' => ['nullable', 'string', 'max:255'],
                'body' => ['required', 'string'],
                'icon' => ['nullable', 'string', 'max:100'],
                'image' => ['nullable', 'image', 'max:4096'],
            ],
            'organization' => [
                ...$baseStateRules,
                'name' => ['required', 'string', 'max:255'],
                'position' => ['required', 'string', 'max:255'],
                'group_name' => ['nullable', 'string', 'max:255'],
                'short_bio' => ['nullable', 'string'],
                'photo' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            ],
            'activities' => [
                ...$baseStateRules,
                'title' => ['required', 'string', 'max:255'],
                'summary' => ['required', 'string'],
                'event_date' => ['nullable', 'date'],
                'location' => ['nullable', 'string', 'max:255'],
                'detail_url' => ['nullable', 'url', 'max:255'],
                'image' => ['nullable', 'image', 'max:4096'],
            ],
            'support' => [
                ...$baseStateRules,
                'name' => ['required', 'string', 'max:255'],
                'website_url' => ['nullable', 'url', 'max:255'],
                'logo' => ['nullable', 'image', 'max:2048'],
            ],
            'kta' => [
                ...$baseStateRules,
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'checklist_text' => ['nullable', 'string'],
                'cta_label' => ['nullable', 'string', 'max:255'],
                'cta_url' => ['nullable', 'string', 'max:255'],
                'banner_image' => ['nullable', 'image', 'max:4096'],
            ],
            default => abort(404),
        };
    }

    private function sectionFileFieldMap(string $section): array
    {
        return match ($section) {
            'heroes' => [
                'background_image' => 'background_image_path',
                'hero_image' => 'hero_image_path',
            ],
            'history' => [
                'image' => 'image_path',
            ],
            'organization' => [
                'photo' => 'photo_path',
            ],
            'activities' => [
                'image' => 'image_path',
            ],
            'support' => [
                'logo' => 'logo_path',
            ],
            'kta' => [
                'banner_image' => 'banner_image_path',
            ],
            default => [],
        };
    }

    private function serializeSettings(?PublicSiteSetting $settings): array
    {
        $socialLinks = $settings?->social_links ?? [];

        return [
            'id' => $settings?->id,
            'site_name' => $settings?->site_name ?? 'PPSI',
            'site_tagline' => $settings?->site_tagline,
            'email' => $settings?->email,
            'telepon' => $settings?->telepon,
            'alamat' => $settings?->alamat,
            'footer_text' => $settings?->footer_text,
            'seo_title' => $settings?->seo_title,
            'seo_description' => $settings?->seo_description,
            'seo_keywords' => $settings?->seo_keywords,
            'facebook_url' => $socialLinks['facebook'] ?? null,
            'instagram_url' => $socialLinks['instagram'] ?? null,
            'youtube_url' => $socialLinks['youtube'] ?? null,
            'linkedin_url' => $socialLinks['linkedin'] ?? null,
            'logo_url' => $settings?->logo_path ? route('landing.media', ['collection' => 'settings', 'id' => $settings->id, 'field' => 'logo_path'], false) : null,
            'favicon_url' => $settings?->favicon_path ? route('landing.media', ['collection' => 'settings', 'id' => $settings->id, 'field' => 'favicon_path'], false) : null,
        ];
    }

    private function serializeHero(LandingHero $item): array
    {
        return [
            'id' => $item->id,
            'badge' => $item->badge,
            'title' => $item->title,
            'highlighted_text' => $item->highlighted_text,
            'description' => $item->description,
            'primary_cta_label' => $item->primary_cta_label,
            'primary_cta_url' => $item->primary_cta_url,
            'secondary_cta_label' => $item->secondary_cta_label,
            'secondary_cta_url' => $item->secondary_cta_url,
            'video_url' => $item->video_url,
            'sort_order' => $item->sort_order,
            'is_published' => $item->is_published,
            'background_image_url' => $item->background_image_path ? route('landing.media', ['collection' => 'heroes', 'id' => $item->id, 'field' => 'background_image_path'], false) : null,
            'hero_image_url' => $item->hero_image_path ? route('landing.media', ['collection' => 'heroes', 'id' => $item->id, 'field' => 'hero_image_path'], false) : null,
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
            'sort_order' => $item->sort_order,
            'is_published' => $item->is_published,
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
            'sort_order' => $item->sort_order,
            'is_published' => $item->is_published,
            'document_url' => $documentUrl,
            'photo_url' => $documentUrl,
            'media_type' => $this->detectMediaType($item->photo_path),
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

    private function serializeActivity(LandingActivity $item): array
    {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'summary' => $item->summary,
            'event_date' => $item->event_date?->toDateString(),
            'location' => $item->location,
            'detail_url' => $item->detail_url,
            'sort_order' => $item->sort_order,
            'is_published' => $item->is_published,
            'image_url' => $item->image_path ? route('landing.media', ['collection' => 'activities', 'id' => $item->id, 'field' => 'image_path'], false) : null,
        ];
    }

    private function serializeSupportItem(LandingSupportItem $item): array
    {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'website_url' => $item->website_url,
            'sort_order' => $item->sort_order,
            'is_published' => $item->is_published,
            'logo_url' => $item->logo_path ? route('landing.media', ['collection' => 'support', 'id' => $item->id, 'field' => 'logo_path'], false) : null,
        ];
    }

    private function serializeKtaBlock(LandingKtaBlock $item): array
    {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'description' => $item->description,
            'checklist_text' => $item->checklist_text,
            'cta_label' => $item->cta_label,
            'cta_url' => $item->cta_url,
            'sort_order' => $item->sort_order,
            'is_published' => $item->is_published,
            'banner_image_url' => $item->banner_image_path ? route('landing.media', ['collection' => 'kta', 'id' => $item->id, 'field' => 'banner_image_path'], false) : null,
        ];
    }
}
