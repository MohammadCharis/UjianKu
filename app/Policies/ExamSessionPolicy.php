<?php

namespace App\Policies;

use App\Models\ExamSession;
use App\Models\User;

class ExamSessionPolicy
{
    public function view(User $user, ExamSession $examSession): bool
    {
        return $user->isAdmin()
            || $this->isStudentOwner($user, $examSession)
            || $this->isExamOwner($user, $examSession);
    }

    public function grade(User $user, ExamSession $examSession): bool
    {
        return $user->isAdmin() || $this->isExamOwner($user, $examSession);
    }

    public function answer(User $user, ExamSession $examSession): bool
    {
        return $this->isStudentOwner($user, $examSession)
            && $examSession->status === ExamSession::BERLANGSUNG
            && $examSession->remainingSeconds() > 0;
    }

    public function submit(User $user, ExamSession $examSession): bool
    {
        return $this->isStudentOwner($user, $examSession)
            && $examSession->status === ExamSession::BERLANGSUNG;
    }

    public function heartbeat(User $user, ExamSession $examSession): bool
    {
        return $this->view($user, $examSession);
    }

    private function isStudentOwner(User $user, ExamSession $examSession): bool
    {
        return $user->isSiswa() && $examSession->student?->user_id === $user->id;
    }

    private function isExamOwner(User $user, ExamSession $examSession): bool
    {
        return $user->isGuru() && $examSession->exam?->created_by === $user->id;
    }
}
