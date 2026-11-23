<?php

namespace App\Policies;

use App\Models\ExamSession;
use App\Models\User;

class ExamSessionPolicy
{
    public function view(User $user, ExamSession $examSession): bool
    {
        return $this->isOwner($user, $examSession);
    }

    public function answer(User $user, ExamSession $examSession): bool
    {
        return $this->isOwner($user, $examSession)
            && $examSession->status === ExamSession::BERLANGSUNG
            && $examSession->remainingSeconds() > 0;
    }

    public function submit(User $user, ExamSession $examSession): bool
    {
        return $this->isOwner($user, $examSession)
            && $examSession->status === ExamSession::BERLANGSUNG;
    }

    public function heartbeat(User $user, ExamSession $examSession): bool
    {
        return $this->view($user, $examSession);
    }

    private function isOwner(User $user, ExamSession $examSession): bool
    {
        return $user->isSiswa() && $examSession->student?->user_id === $user->id;
    }
}
