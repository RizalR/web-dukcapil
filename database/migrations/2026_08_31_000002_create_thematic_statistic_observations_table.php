<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thematic_statistic_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dataset_id')->constrained('thematic_statistic_datasets')->cascadeOnDelete();
            $table->string('area_code', 32);
            $table->string('area', 120);
            $table->string('area_level', 30);
            $table->string('parent_area_code', 32)->nullable();
            $table->string('dimension_one', 40)->default('');
            $table->string('dimension_one_value', 120)->default('');
            $table->string('dimension_two', 40)->default('');
            $table->string('dimension_two_value', 120)->default('');
            $table->string('metric', 60);
            $table->string('sex', 10);
            $table->string('snapshot', 20)->default('dynamic');
            $table->decimal('value', 18, 4);
            $table->string('unit', 30);
            $table->char('observation_key', 40);

            $table->unique(['dataset_id', 'observation_key'], 'thematic_observation_unique');
            $table->index(['dataset_id', 'area_code', 'metric'], 'thematic_observation_area_index');
            $table->index(['dataset_id', 'dimension_one', 'dimension_one_value'], 'thematic_observation_dimension_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thematic_statistic_observations');
    }
};
