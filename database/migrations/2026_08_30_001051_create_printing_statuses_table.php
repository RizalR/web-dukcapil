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
        Schema::create('printing_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('location');
            $table->string('document');
            $table->unsignedSmallInteger('sort_order');
            $table->boolean('is_available')->default(false);
            $table->string('note', 500)->nullable();
            $table->string('source')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['location', 'document']);
        });

        $now = now();

        DB::table('printing_statuses')->insert([
            [
                'location' => 'Kantor Dukcapil Limboto',
                'document' => 'ktp_el',
                'sort_order' => 10,
                'is_available' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'location' => 'Kantor Dukcapil Limboto',
                'document' => 'kia',
                'sort_order' => 20,
                'is_available' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printing_statuses');
    }
};
