<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('session_id')->index()->constrained('exam_sessions')->cascadeOnDelete();
            $table->foreignId('handled_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('type');
            $table->enum('level', ['ringan', 'sedang', 'berat']);
            $table->timestamp('occurred_at')->useCurrent()->index();
            $table->unsignedSmallInteger('duration_seconds')->default(0);
            $table->string('action')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violations');
    }
};
