<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['session_id', 'handled_by', 'type', 'level', 'occurred_at', 'duration_seconds', 'action', 'note'])]
class Violation extends Model
{
    public const RINGAN = 'ringan';

    public const SEDANG = 'sedang';

    public const BERAT = 'berat';

    /** @use HasFactory<\Database\Factories\ViolationFactory> */
    use HasFactory;

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class, 'session_id');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'duration_seconds' => 'integer',
        ];
    }
}
