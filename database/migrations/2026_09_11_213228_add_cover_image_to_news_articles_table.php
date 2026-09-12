<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_articles', function (Blueprint $table) {
            $table->string('image_path')->nullable();
            $table->string('image_alt')->nullable();
            $table->string('image_caption', 320)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('news_articles', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'image_alt', 'image_caption']);
        });
    }
};
