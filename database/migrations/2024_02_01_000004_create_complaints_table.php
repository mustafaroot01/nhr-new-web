<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();        // tracking code shown to the submitter
            // Sensitive fields are stored encrypted (text to hold the ciphertext).
            $table->text('name')->nullable();
            $table->text('contact')->nullable();          // phone/email, optional
            $table->string('subject');
            $table->text('details');
            $table->string('incident_location')->nullable();
            $table->date('incident_date')->nullable();
            $table->enum('status', ['new', 'reviewing', 'resolved', 'closed'])->default('new');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->text('admin_notes')->nullable();
            $table->boolean('is_anonymous')->default(true);
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
