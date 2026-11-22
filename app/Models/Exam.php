<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'subject_id',
    'created_by',
    'title',
    'token',
    'start_at',
    'end_at',
    'duration_min',
    'max_violations',
    'shuffle',
    'lock_mode',
])]
class Exam extends Model
{
    public const LOCK = 'kunci';

    public const AUTO_SUBMIT = 'auto_submit';

    /** @use HasFactory<\Database\Factories\ExamFactory> */
    use HasFactory;

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'exam_classes');
    }

    public function examClasses(): HasMany
    {
        return $this->hasMany(ExamClass::class);
    }

    public function examQuestions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class);
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'exam_questions')
            ->withPivot(['order_no', 'points'])
            ->orderByPivot('order_no');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'shuffle' => 'boolean',
            'duration_min' => 'integer',
            'max_violations' => 'integer',
        ];
    }
}
