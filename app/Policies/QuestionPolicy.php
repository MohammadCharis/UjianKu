<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;

class QuestionPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isGuru();
    }

    public function view(User $user, Question $question): bool
    {
        return $question->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isGuru();
    }

    public function update(User $user, Question $question): bool
    {
        return $question->created_by === $user->id;
    }

    public function delete(User $user, Question $question): bool
    {
        return $question->created_by === $user->id;
    }
}
