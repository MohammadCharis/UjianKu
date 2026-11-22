<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('session_id')->index()->constrained('exam_sessions')->cascadeOnDelete();
            $table->foreignId('question_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('option_id')->nullable()->index()->constrained('question_options')->nullOnDelete();
            $table->text('answer_text')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->boolean('is_doubtful')->default(false);
            $table->timestamps();

            $table->unique(['session_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
