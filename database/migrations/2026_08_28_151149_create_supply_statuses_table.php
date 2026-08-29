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
        Schema::create('supply_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('location');
            $table->string('item');
            $table->string('status');
            $table->string('note', 500)->nullable();
            $table->string('source')->nullable();
            $table->timestamp('checked_at');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['location', 'item']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supply_statuses');
    }
};
