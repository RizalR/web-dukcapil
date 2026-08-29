<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEmployeeRequest;
use App\Http\Requests\Admin\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Services\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $this->authorize('viewAny', Employee::class);

        return view('admin.employees.index', [
            'employees' => Employee::query()->orderBy('sort_order')->orderBy('name')->paginate(20),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Employee::class);

        return view('admin.employees.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmployeeRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $photoPath = $request->file('photo')->store('employees/photos', 'public');

        if (! $photoPath) {
            throw new RuntimeException('Foto pegawai tidak dapat disimpan.');
        }

        $data = Arr::except($request->validated(), ['photo']);
        $data['photo_url'] = $photoPath;
        $data['is_published'] = $request->boolean('is_published');
        $data['updated_by'] = $request->user()->id;

        try {
            $employee = Employee::query()->create($data);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($photoPath);

            throw $exception;
        }

        $auditLogger->record('employee.created', "Profil pegawai {$employee->name} dibuat.", $employee);

        return redirect()->route('admin.employees.index')->with('status', 'Profil pegawai berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee): RedirectResponse
    {
        $this->authorize('view', $employee);

        return redirect()->route('admin.employees.edit', $employee);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee): View
    {
        $this->authorize('update', $employee);

        return view('admin.employees.edit', ['employee' => $employee]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee, AuditLogger $auditLogger): RedirectResponse
    {
        $newPhotoPath = null;

        if ($request->hasFile('photo')) {
            $newPhotoPath = $request->file('photo')->store('employees/photos', 'public');

            if (! $newPhotoPath) {
                throw new RuntimeException('Foto pegawai tidak dapat disimpan.');
            }
        }

        $oldPhotoPath = $employee->photo_url;
        $data = Arr::except($request->validated(), ['photo']);
        $data['is_published'] = $request->boolean('is_published');
        $data['updated_by'] = $request->user()->id;

        if ($newPhotoPath) {
            $data['photo_url'] = $newPhotoPath;
        }

        try {
            $employee->update($data);
        } catch (Throwable $exception) {
            if ($newPhotoPath) {
                Storage::disk('public')->delete($newPhotoPath);
            }

            throw $exception;
        }

        if ($newPhotoPath) {
            $this->deleteStoredPhoto($oldPhotoPath);
        }

        $auditLogger->record('employee.updated', "Profil pegawai {$employee->name} diperbarui.", $employee);

        return redirect()->route('admin.employees.index')->with('status', 'Profil pegawai berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorize('delete', $employee);
        $name = $employee->name;
        $photoPath = $employee->photo_url;

        $auditLogger->record('employee.deleted', "Profil pegawai {$name} dihapus.", $employee);
        $employee->delete();
        $this->deleteStoredPhoto($photoPath);

        return redirect()->route('admin.employees.index')->with('status', 'Profil pegawai berhasil dihapus.');
    }

    private function deleteStoredPhoto(?string $photoPath): void
    {
        if (! $photoPath || str($photoPath)->startsWith(['http://', 'https://'])) {
            return;
        }

        Storage::disk('public')->delete($photoPath);
    }
}
