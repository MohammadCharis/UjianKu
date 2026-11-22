<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subject_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->index()->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('token')->unique();
            $table->dateTime('start_at')->index();
            $table->dateTime('end_at')->index();
            $table->unsignedSmallInteger('duration_min');
            $table->unsignedSmallInteger('max_violations')->default(3);
            $table->boolean('shuffle')->default(false);
            $table->enum('lock_mode', ['kunci', 'auto_submit'])->default('kunci');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
