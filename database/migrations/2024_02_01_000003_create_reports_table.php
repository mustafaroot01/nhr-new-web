<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title_ar');
            $table->string('title_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->string('cover_image_path')->nullable();
            $table->string('pdf_ar_path')->nullable();
            $table->string('pdf_en_path')->nullable();
            $table->unsignedSmallInteger('year');
            $table->string('category')->nullable();
            $table->boolean('is_published')->default(true);
            $table->integer('sort_order')->default(0);
            $table->unsignedBigInteger('download_count')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
