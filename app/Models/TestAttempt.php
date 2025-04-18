<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mcq_test_id',
        'started_at',
        'completed_at',
        'score'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'score' => 'float'
    ];

    public function mcqTest(): BelongsTo
    {
        return $this->belongsTo(MCQTest::class, 'mcq_test_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(StudentResponse::class);
    }

    public function getStatusAttribute()
    {
        if (!$this->completed_at) {
            return 'In Progress';
        }
        return $this->score >= $this->mcqTest->passing_percentage ? 'Passed' : 'Failed';
    }

    public function isExpired(): bool
    {
        if (!$this->started_at) {
            return false;
        }

        $duration = $this->mcqTest->duration_minutes;
        return now()->diffInMinutes($this->started_at) > $duration;
    }

    public function calculateScore(): float
    {
        $totalQuestions = $this->mcqTest->questions()->count();
        if ($totalQuestions === 0) {
            return 0;
        }

        $correctAnswers = $this->responses()
            ->where('is_correct', true)
            ->count();

        return ($correctAnswers / $totalQuestions) * 100;
    }
} 