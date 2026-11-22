<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->index()->constrained()->cascadeOnDelete();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('finished_at')->nullable();
            $table->enum('status', ['berlangsung', 'terkunci', 'selesai', 'diskualifikasi'])->default('berlangsung');
            $table->decimal('score', 5, 2)->nullable();
            $table->unsignedSmallInteger('violation_count')->default(0);
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['exam_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_sessions');
    }
};
