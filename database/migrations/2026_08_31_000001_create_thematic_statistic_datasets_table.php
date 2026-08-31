<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thematic_statistic_datasets', function (Blueprint $table) {
            $table->id();
            $table->string('type', 80);
            $table->string('period', 80);
            $table->string('source', 180);
            $table->text('source_url')->nullable();
            $table->string('original_filename');
            $table->char('file_hash', 64);
            $table->string('status', 20)->default('draft');
            $table->unsignedInteger('row_count');
            $table->unsignedInteger('observation_count');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['type', 'period', 'file_hash'], 'thematic_dataset_file_unique');
            $table->index(['period', 'type', 'status', 'published_at'], 'thematic_dataset_public_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thematic_statistic_datasets');
    }
};
