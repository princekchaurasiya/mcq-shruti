<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class McqTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'subject_id',
        'duration_minutes',
        'start_time',
        'end_time',
        'pass_percentage',
        'is_active',
        'created_by',
        'total_marks'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the subject that owns the test.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the teacher who created the test.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the questions for the test.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'mcq_test_id');
    }

    /**
     * Get the attempts for this test.
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class, 'mcq_test_id');
    }

    /**
     * Check if the test can be taken now.
     */
    public function canBeTaken(): bool
    {
        return $this->is_active &&
               $this->start_time <= now() &&
               $this->end_time > now() &&
               $this->questions()->count() > 0;
    }

    /**
     * Get the number of attempts by a specific user.
     */
    public function getAttemptsCountByUser(User $user): int
    {
        return $this->attempts()
                    ->where('user_id', $user->id)
                    ->count();
    }

    /**
     * Check if a test is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->start_time <= now() && $this->end_time > now();
    }
}
