<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->enum('media_type', ['image', 'video'])->default('image')->after('id');
            $table->string('video_path')->nullable()->after('image_path');
            $table->string('image_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['media_type', 'video_path']);
        });
    }
};
