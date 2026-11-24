<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_sessions', function (Blueprint $table): void {
            $table->index(['exam_id', 'status'], 'exam_sessions_exam_status_index');
            $table->index(['student_id', 'status'], 'exam_sessions_student_status_index');
        });

        Schema::table('violations', function (Blueprint $table): void {
            $table->index(['session_id', 'occurred_at'], 'violations_session_occurred_index');
        });

        Schema::table('questions', function (Blueprint $table): void {
            $table->index(['subject_id', 'type'], 'questions_subject_type_index');
        });

        Schema::table('exam_classes', function (Blueprint $table): void {
            $table->index(['exam_id'], 'exam_classes_exam_index');
        });
    }

    public function down(): void
    {
        Schema::table('exam_sessions', function (Blueprint $table): void {
            $table->dropIndex('exam_sessions_exam_status_index');
            $table->dropIndex('exam_sessions_student_status_index');
        });

        Schema::table('violations', function (Blueprint $table): void {
            $table->dropIndex('violations_session_occurred_index');
        });

        Schema::table('questions', function (Blueprint $table): void {
            $table->dropIndex('questions_subject_type_index');
        });

        Schema::table('exam_classes', function (Blueprint $table): void {
            $table->dropIndex('exam_classes_exam_index');
        });
    }
};
