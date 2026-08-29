<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('population_statistics', function (Blueprint $table) {
            $table->string('area_code', 32)->nullable()->after('area');
            $table->string('area_level', 30)->nullable()->after('area_code');
            $table->string('parent_area_code', 32)->nullable()->after('area_level');
        });

        DB::table('population_statistics')
            ->orderBy('id')
            ->chunkById(200, function ($statistics): void {
                foreach ($statistics as $statistic) {
                    DB::table('population_statistics')
                        ->where('id', $statistic->id)
                        ->update([
                            'area_code' => 'legacy-'.$statistic->id,
                            'area_level' => 'legacy',
                        ]);
                }
            });

        Schema::table('population_statistics', function (Blueprint $table) {
            $table->dropUnique('population_statistic_unique');
            $table->unique(['label', 'category', 'period', 'area_code'], 'population_statistic_area_unique');
            $table->index(['period', 'area_level', 'parent_area_code'], 'population_statistics_browse_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('population_statistics', function (Blueprint $table) {
            $table->dropUnique('population_statistic_area_unique');
            $table->dropIndex('population_statistics_browse_index');
            $table->dropColumn(['area_code', 'area_level', 'parent_area_code']);
            $table->unique(['label', 'category', 'period', 'area'], 'population_statistic_unique');
        });
    }
};
