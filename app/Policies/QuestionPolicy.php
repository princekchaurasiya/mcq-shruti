<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuestionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return $user->teacher !== null;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Question $question)
    {
        // Teachers can only view questions from their own tests
        if ($user->teacher) {
            return $question->mcqTest->teacher_id === $user->teacher->id;
        }

        // Students can view questions if they are enrolled in the test
        if ($user->student) {
            return $user->student->enrolledTests()
                ->where('mcq_test_id', $question->mcq_test_id)
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return $user->teacher !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Question $question)
    {
        // Only teachers can update their own questions
        return $user->teacher && $question->mcqTest->teacher_id === $user->teacher->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Question $question)
    {
        // Only teachers can delete their own questions
        return $user->teacher && $question->mcqTest->teacher_id === $user->teacher->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Question $question): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Question $question): bool
    {
        return false;
    }
}
