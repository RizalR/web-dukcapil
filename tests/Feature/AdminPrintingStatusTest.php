<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\PrintingStatus;
use App\Models\User;
use App\PrintingDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPrintingStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_printing_status_management(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.printing-statuses.index'))
            ->assertOk()
            ->assertSeeInOrder(['Pencetakan KTP-el', 'Pencetakan KIA'])
            ->assertSee('Belum diperbarui');
    }

    public function test_edit_form_offers_only_ada_and_tidak_ada(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $printingStatus = PrintingStatus::query()->firstOrFail();

        $response = $this->actingAs($admin)
            ->get(route('admin.printing-statuses.edit', $printingStatus))
            ->assertOk()
            ->assertSeeInOrder(['Status pencetakan', 'Ada', 'Tidak ada']);

        $this->assertSame(2, substr_count($response->getContent(), '<option'));
    }

    public function test_super_admin_can_update_printing_status_with_ada_or_tidak_ada(): void
    {
        $this->travelTo('2026-08-30 10:00:00');
        $admin = User::factory()->superAdmin()->create();
        $printingStatus = PrintingStatus::query()
            ->where('document', PrintingDocument::ElectronicIdentityCard->value)
            ->sole();

        $this->actingAs($admin)
            ->put(route('admin.printing-statuses.update', $printingStatus), [
                'is_available' => '1',
                'note' => 'Pencetakan tersedia selama jam pelayanan.',
                'source' => 'Operator pencetakan',
                'checked_at' => '2026-08-30 09:30:00',
                'document' => PrintingDocument::ChildIdentityCard->value,
                'location' => 'Lokasi yang tidak boleh diubah',
            ])
            ->assertRedirect(route('admin.printing-statuses.index'))
            ->assertSessionHas('status');

        $printingStatus->refresh();
        $this->assertTrue($printingStatus->is_available);
        $this->assertSame(PrintingDocument::ElectronicIdentityCard, $printingStatus->document);
        $this->assertSame('Kantor Dukcapil Limboto', $printingStatus->location);
        $this->assertSame($admin->id, $printingStatus->updated_by);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'printing-status.updated',
            'user_id' => $admin->id,
            'auditable_id' => $printingStatus->id,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.printing-statuses.update', $printingStatus), [
                'is_available' => '0',
                'note' => 'Pencetakan belum tersedia.',
                'source' => 'Operator pencetakan',
                'checked_at' => '2026-08-30 09:45:00',
            ])
            ->assertRedirect(route('admin.printing-statuses.index'));

        $this->assertFalse($printingStatus->fresh()->is_available);
        $this->assertSame(2, AuditLog::query()->where('action', 'printing-status.updated')->count());
    }

    public function test_news_editor_cannot_manage_printing_statuses(): void
    {
        $editor = User::factory()->create();
        $printingStatus = PrintingStatus::query()->firstOrFail();

        $this->actingAs($editor)->get(route('admin.printing-statuses.index'))->assertForbidden();
        $this->actingAs($editor)->get(route('admin.printing-statuses.edit', $printingStatus))->assertForbidden();
        $this->actingAs($editor)->put(route('admin.printing-statuses.update', $printingStatus), [])->assertForbidden();
    }

    public function test_guest_is_redirected_from_printing_status_management(): void
    {
        $printingStatus = PrintingStatus::query()->firstOrFail();

        $this->get(route('admin.printing-statuses.index'))->assertRedirect(route('login'));
        $this->get(route('admin.printing-statuses.edit', $printingStatus))->assertRedirect(route('login'));
        $this->put(route('admin.printing-statuses.update', $printingStatus))->assertRedirect(route('login'));
    }

    public function test_printing_status_rejects_invalid_availability_and_future_check_time(): void
    {
        $this->travelTo('2026-08-30 10:00:00');
        $admin = User::factory()->superAdmin()->create();
        $printingStatus = PrintingStatus::query()->firstOrFail();

        $this->actingAs($admin)
            ->from(route('admin.printing-statuses.edit', $printingStatus))
            ->put(route('admin.printing-statuses.update', $printingStatus), [
                'is_available' => 'mungkin',
                'note' => str_repeat('a', 501),
                'source' => '',
                'checked_at' => '2026-08-30 10:01:00',
            ])
            ->assertRedirect(route('admin.printing-statuses.edit', $printingStatus))
            ->assertSessionHasErrors(['is_available', 'note', 'source', 'checked_at']);

        $printingStatus->refresh();
        $this->assertNull($printingStatus->checked_at);
        $this->assertDatabaseEmpty('audit_logs');
    }
}
