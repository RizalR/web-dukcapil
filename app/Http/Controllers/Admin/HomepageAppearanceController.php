<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateHomepageAppearanceRequest;
use App\Models\HeroSlide;
use App\Models\HomepageSetting;
use App\Services\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class HomepageAppearanceController extends Controller
{
    public function edit(): View
    {
        Gate::authorize('manageHomepage');
        $homepageSetting = HomepageSetting::query()->first();

        return view('admin.homepage.edit', [
            'homepageSetting' => $homepageSetting,
            'backgroundPreviewUrl' => $homepageSetting?->background_image_path
                ? Storage::disk('public')->url($homepageSetting->background_image_path)
                : asset('images/dukcapil_.jpeg'),
            'slides' => HeroSlide::query()->with('updatedBy')->orderBy('sort_order')->orderBy('id')->paginate(15),
        ]);
    }

    public function update(UpdateHomepageAppearanceRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $newImagePath = $request->file('background_image')->store('homepage/backgrounds', 'public');

        if (! $newImagePath) {
            throw new RuntimeException('Gambar background tidak dapat disimpan.');
        }

        $homepageSetting = HomepageSetting::query()->firstOrCreate();
        $oldImagePath = $homepageSetting->background_image_path;

        try {
            $homepageSetting->update([
                'background_image_path' => $newImagePath,
                'updated_by' => $request->user()->id,
            ]);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($newImagePath);

            throw $exception;
        }

        if ($oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        $auditLogger->record('homepage.background.updated', 'Background halaman utama diperbarui.', $homepageSetting);

        return redirect()->route('admin.homepage.edit')->with('status', 'Background halaman utama berhasil diperbarui.');
    }

    public function destroy(AuditLogger $auditLogger): RedirectResponse
    {
        Gate::authorize('manageHomepage');

        $homepageSetting = HomepageSetting::query()->first();

        if (! $homepageSetting?->background_image_path) {
            return redirect()->route('admin.homepage.edit')->with('status', 'Background bawaan sudah digunakan.');
        }

        $oldImagePath = $homepageSetting->background_image_path;
        $homepageSetting->update([
            'background_image_path' => null,
            'updated_by' => request()->user()->id,
        ]);
        Storage::disk('public')->delete($oldImagePath);
        $auditLogger->record('homepage.background.reset', 'Background halaman utama dikembalikan ke gambar bawaan.', $homepageSetting);

        return redirect()->route('admin.homepage.edit')->with('status', 'Background bawaan berhasil dipulihkan.');
    }
}
