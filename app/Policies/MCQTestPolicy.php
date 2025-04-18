<?php

namespace App\Policies;

use App\Models\MCQTest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MCQTestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isTeacher();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MCQTest $mcqTest): bool
    {
        return $user->isTeacher() && $user->teacher && $mcqTest->teacher_id === $user->teacher->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isTeacher();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MCQTest $mcqTest): bool
    {
        return $user->isTeacher() && $user->teacher && $mcqTest->teacher_id === $user->teacher->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MCQTest $mcqTest): bool
    {
        return $user->isTeacher() && $user->teacher && $mcqTest->teacher_id === $user->teacher->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MCQTest $mcqTest): bool
    {
        return $user->isTeacher() && $user->teacher && $mcqTest->teacher_id === $user->teacher->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MCQTest $mcqTest): bool
    {
        return $user->isTeacher() && $user->teacher && $mcqTest->teacher_id === $user->teacher->id;
    }
}
