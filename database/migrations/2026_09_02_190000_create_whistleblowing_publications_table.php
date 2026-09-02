<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('whistleblowing_publications', function (Blueprint $table): void {
            $table->id();
            $table->string('publication_id', 32)->unique();
            $table->string('category_code', 64);
            $table->string('category_label', 120);
            $table->text('summary');
            $table->text('follow_up_result');
            $table->dateTime('published_at');
            $table->dateTime('source_updated_at');
            $table->dateTime('last_synced_at');
            $table->boolean('is_active')->default(true);
            $table->dateTime('withdrawn_at')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'published_at']);
            $table->index(['is_active', 'category_code']);
        });

        Schema::create('whistleblowing_sync_states', function (Blueprint $table): void {
            $table->id();
            $table->string('source', 32)->unique();
            $table->dateTime('last_attempted_at')->nullable();
            $table->dateTime('last_succeeded_at')->nullable();
            $table->dateTime('last_snapshot_generated_at')->nullable();
            $table->unsignedInteger('last_item_count')->default(0);
            $table->dateTime('last_error_at')->nullable();
            $table->string('last_error_code', 120)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whistleblowing_sync_states');
        Schema::dropIfExists('whistleblowing_publications');
    }
};
