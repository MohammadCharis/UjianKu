<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'exam_id',
    'student_id',
    'started_at',
    'finished_at',
    'status',
    'score',
    'violation_count',
    'last_seen_at',
])]
class ExamSession extends Model
{
    public const BERLANGSUNG = 'berlangsung';

    public const TERKUNCI = 'terkunci';

    public const SELESAI = 'selesai';

    public const DISKUALIFIKASI = 'diskualifikasi';

    /** @use HasFactory<\Database\Factories\ExamSessionFactory> */
    use HasFactory;

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'session_id');
    }

    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class, 'session_id');
    }

    /**
     * @param  Builder<ExamSession>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', self::BERLANGSUNG);
    }

    /**
     * @param  Builder<ExamSession>  $query
     */
    public function scopeProblematic(Builder $query): void
    {
        $query->where('violation_count', '>', 0);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'score' => 'decimal:2',
            'violation_count' => 'integer',
        ];
    }
}
