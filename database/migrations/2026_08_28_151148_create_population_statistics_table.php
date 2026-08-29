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
        Schema::create('population_statistics', function (Blueprint $table) {
            $table->id();
            $table->string('label', 120);
            $table->string('category', 80);
            $table->unsignedBigInteger('value');
            $table->string('unit', 30)->default('jiwa');
            $table->string('period', 80);
            $table->string('area', 120)->default('Kabupaten Gorontalo');
            $table->string('source', 180);
            $table->string('source_url')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['period', 'category']);
            $table->index('published_at');
            $table->unique(['label', 'category', 'period', 'area'], 'population_statistic_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('population_statistics');
    }
};
