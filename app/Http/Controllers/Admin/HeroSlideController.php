<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreHeroSlideRequest;
use App\Http\Requests\Admin\UpdateHeroSlideRequest;
use App\Models\HeroSlide;
use App\Services\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class HeroSlideController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', HeroSlide::class);

        return view('admin.hero-slides.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHeroSlideRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $imagePath = $request->file('image')->store('homepage/slides', 'public');

        if (! $imagePath) {
            throw new RuntimeException('Gambar slider tidak dapat disimpan.');
        }

        $data = Arr::except($request->validated(), ['image']);
        $data['image_path'] = $imagePath;
        $data['is_active'] = $request->boolean('is_active');
        $data['updated_by'] = $request->user()->id;

        try {
            $heroSlide = HeroSlide::query()->create($data);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($imagePath);

            throw $exception;
        }

        $auditLogger->record('homepage.slide.created', "Slide {$heroSlide->label} dibuat.", $heroSlide);

        return redirect()->route('admin.homepage.edit')->with('status', 'Slide berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HeroSlide $heroSlide): View
    {
        $this->authorize('update', $heroSlide);

        return view('admin.hero-slides.edit', ['heroSlide' => $heroSlide]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHeroSlideRequest $request, HeroSlide $heroSlide, AuditLogger $auditLogger): RedirectResponse
    {
        $newImagePath = null;

        if ($request->hasFile('image')) {
            $newImagePath = $request->file('image')->store('homepage/slides', 'public');

            if (! $newImagePath) {
                throw new RuntimeException('Gambar slider tidak dapat disimpan.');
            }
        }

        $oldImagePath = $heroSlide->image_path;
        $data = Arr::except($request->validated(), ['image']);
        $data['is_active'] = $request->boolean('is_active');
        $data['updated_by'] = $request->user()->id;

        if ($newImagePath) {
            $data['image_path'] = $newImagePath;
        }

        try {
            $heroSlide->update($data);
        } catch (Throwable $exception) {
            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }

            throw $exception;
        }

        if ($newImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        $auditLogger->record('homepage.slide.updated', "Slide {$heroSlide->label} diperbarui.", $heroSlide);

        return redirect()->route('admin.homepage.edit')->with('status', 'Slide berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HeroSlide $heroSlide, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorize('delete', $heroSlide);
        $label = $heroSlide->label;
        $imagePath = $heroSlide->image_path;
        $auditLogger->record('homepage.slide.deleted', "Slide {$label} dihapus.", $heroSlide);
        $heroSlide->delete();
        Storage::disk('public')->delete($imagePath);

        return redirect()->route('admin.homepage.edit')->with('status', 'Slide berhasil dihapus.');
    }
}
