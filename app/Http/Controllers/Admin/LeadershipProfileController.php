<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLeadershipProfileRequest;
use App\Http\Requests\Admin\UpdateLeadershipProfileRequest;
use App\LeadershipRole;
use App\Models\LeadershipProfile;
use App\Services\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class LeadershipProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $this->authorize('viewAny', LeadershipProfile::class);

        return view('admin.leadership-profiles.index', [
            'leadershipProfiles' => LeadershipProfile::query()->ordered()->paginate(10),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', LeadershipProfile::class);

        return view('admin.leadership-profiles.create', ['roles' => LeadershipRole::cases()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLeadershipProfileRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $photoPath = $request->file('photo')->store('leadership/photos', 'public');

        if (! $photoPath) {
            throw new RuntimeException('Foto pimpinan tidak dapat disimpan.');
        }

        $data = Arr::except($request->validated(), ['photo']);
        $role = LeadershipRole::from($data['role']);
        $data['photo_path'] = $photoPath;
        $data['sort_order'] = $role->sortOrder();
        $data['is_published'] = $request->boolean('is_published');
        $data['updated_by'] = $request->user()->id;

        try {
            $leadershipProfile = LeadershipProfile::query()->create($data);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($photoPath);

            throw $exception;
        }

        $auditLogger->record('leadership_profile.created', "Profil pimpinan {$leadershipProfile->name} dibuat.", $leadershipProfile);

        return redirect()->route('admin.leadership-profiles.index')->with('status', 'Profil pimpinan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LeadershipProfile $leadershipProfile): RedirectResponse
    {
        $this->authorize('view', $leadershipProfile);

        return redirect()->route('admin.leadership-profiles.edit', $leadershipProfile);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeadershipProfile $leadershipProfile): View
    {
        $this->authorize('update', $leadershipProfile);

        return view('admin.leadership-profiles.edit', [
            'leadershipProfile' => $leadershipProfile,
            'roles' => LeadershipRole::cases(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLeadershipProfileRequest $request, LeadershipProfile $leadershipProfile, AuditLogger $auditLogger): RedirectResponse
    {
        $newPhotoPath = null;

        if ($request->hasFile('photo')) {
            $newPhotoPath = $request->file('photo')->store('leadership/photos', 'public');

            if (! $newPhotoPath) {
                throw new RuntimeException('Foto pimpinan tidak dapat disimpan.');
            }
        }

        $oldPhotoPath = $leadershipProfile->photo_path;
        $data = Arr::except($request->validated(), ['photo']);
        $role = LeadershipRole::from($data['role']);
        $data['sort_order'] = $role->sortOrder();
        $data['is_published'] = $request->boolean('is_published');
        $data['updated_by'] = $request->user()->id;

        if ($newPhotoPath) {
            $data['photo_path'] = $newPhotoPath;
        }

        try {
            $leadershipProfile->update($data);
        } catch (Throwable $exception) {
            if ($newPhotoPath) {
                Storage::disk('public')->delete($newPhotoPath);
            }

            throw $exception;
        }

        if ($newPhotoPath) {
            $this->deleteStoredPhoto($oldPhotoPath);
        }

        $auditLogger->record('leadership_profile.updated', "Profil pimpinan {$leadershipProfile->name} diperbarui.", $leadershipProfile);

        return redirect()->route('admin.leadership-profiles.index')->with('status', 'Profil pimpinan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeadershipProfile $leadershipProfile, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorize('delete', $leadershipProfile);
        $name = $leadershipProfile->name;
        $photoPath = $leadershipProfile->photo_path;

        $auditLogger->record('leadership_profile.deleted', "Profil pimpinan {$name} dihapus.", $leadershipProfile);
        $leadershipProfile->delete();
        $this->deleteStoredPhoto($photoPath);

        return redirect()->route('admin.leadership-profiles.index')->with('status', 'Profil pimpinan berhasil dihapus.');
    }

    private function deleteStoredPhoto(string $photoPath): void
    {
        if (str($photoPath)->startsWith('images/')) {
            return;
        }

        Storage::disk('public')->delete($photoPath);
    }
}
