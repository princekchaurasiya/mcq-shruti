<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

class MCQTest extends Model
{
    use HasFactory;

    protected $table = 'mcq_tests';

    protected $fillable = [
        'title',
        'description',
        'duration_minutes',
        'passing_percentage',
        'start_time',
        'end_time',
        'subject_id',
        'teacher_id',
        'is_active'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean'
    ];

    /**
     * Get the route key name for route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($test) {
            Log::info('Creating new MCQ test', [
                'title' => $test->title,
                'teacher_id' => $test->teacher_id,
                'subject_id' => $test->subject_id
            ]);
        });

        static::created(function ($test) {
            Log::info('MCQ test created', [
                'test_id' => $test->id,
                'title' => $test->title,
                'teacher_id' => $test->teacher_id
            ]);
        });

        static::updating(function ($test) {
            Log::info('Updating MCQ test', [
                'test_id' => $test->id,
                'changes' => $test->getDirty()
            ]);
        });

        static::updated(function ($test) {
            Log::info('MCQ test updated', [
                'test_id' => $test->id,
                'changes' => $test->getChanges()
            ]);
        });

        static::deleting(function ($test) {
            Log::info('Deleting MCQ test', [
                'test_id' => $test->id,
                'title' => $test->title,
                'teacher_id' => $test->teacher_id
            ]);
        });
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'mcq_test_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class, 'mcq_test_id');
    }

    public function scopeAvailable($query)
    {
        try {
            Log::info('Filtering available MCQ tests');
            return $query->where('start_time', '<=', now())
                        ->where('end_time', '>=', now())
                        ->where('is_active', true);
        } catch (Exception $e) {
            Log::error('Error in scopeAvailable', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function isAvailable(): bool
    {
        try {
            // Validate that the properties are set correctly
            if ($this->id === null) {
                Log::error('MCQTest::isAvailable called on object with null ID');
                return false;
            }
            
            // Log the current state before checking availability
            Log::info('Checking test properties before availability check', [
                'test_id' => $this->id,
                'is_active' => $this->is_active,
                'start_time' => $this->start_time ? $this->start_time->toDateTimeString() : null,
                'end_time' => $this->end_time ? $this->end_time->toDateTimeString() : null,
                'now' => now()->toDateTimeString()
            ]);
            
            // Check if the test is active and not ended yet
            // We allow tests that are scheduled but haven't started to be "visible" but not "takeable"
            // Actual availability for taking the test is now checked separately
            $available = $this->is_active && now()->lte($this->end_time);
            
            // For debugging purposes, log the complete availability information
            Log::info('Checking MCQ test availability', [
                'test_id' => $this->id,
                'is_active' => $this->is_active,
                'start_time' => $this->start_time ? $this->start_time->toDateTimeString() : null,
                'end_time' => $this->end_time ? $this->end_time->toDateTimeString() : null,
                'is_available' => $available,
                'now' => now()->toDateTimeString(),
                'is_after_start' => $this->start_time ? now()->gte($this->start_time) : null,
                'is_before_end' => $this->end_time ? now()->lte($this->end_time) : null
            ]);
            
            return $available;
        } catch (Exception $e) {
            Log::error('Error checking test availability', [
                'test_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    public function canBeTaken(): bool
    {
        // A test can be taken only if it's available AND has started
        return $this->isAvailable() && now()->gte($this->start_time);
    }

    public function hasBeenAttemptedBy($user): bool
    {
        try {
            // Check if there's any attempt by this user for this test
            $attemptExists = $this->attempts()
                            ->where('user_id', $user->id)
                            ->exists();
            
            // For more detailed information, get the attempts
            $attempts = $this->attempts()
                            ->where('user_id', $user->id)
                            ->get();
            
            // Get the status of attempts (for logging)
            $attemptStatuses = $attempts->map(function($attempt) {
                return [
                    'id' => $attempt->id,
                    'completed' => $attempt->completed_at !== null,
                    'started_at' => $attempt->started_at,
                    'completed_at' => $attempt->completed_at
                ];
            });
            
            Log::info('Checking if test has been attempted', [
                'test_id' => $this->id,
                'user_id' => $user->id,
                'attempted' => $attemptExists,
                'attempts' => $attemptStatuses
            ]);
            
            return $attemptExists;
        } catch (Exception $e) {
            Log::error('Error checking test attempt', [
                'test_id' => $this->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false; // Default to false on error
        }
    }

    /**
     * Get the number of attempts a user has made for this test
     */
    public function getAttemptsCountByUser($user): int
    {
        return $this->attempts()
                    ->where('user_id', $user->id)
                    ->count();
    }
    
    /**
     * Check if a user has reached the maximum allowed attempts for this test
     */
    public function hasReachedMaxAttempts($user, $maxAttempts = 5): bool
    {
        return $this->getAttemptsCountByUser($user) >= $maxAttempts;
    }
    
    /**
     * Check if a user can attempt this test again
     */
    public function canBeAttemptedAgainBy($user, $maxAttempts = 5): bool
    {
        // Check if the test is available and the user hasn't reached max attempts
        return $this->canBeTaken() && !$this->hasReachedMaxAttempts($user, $maxAttempts);
    }

    public function getRemainingTimeAttribute()
    {
        try {
            if (!$this->isAvailable()) {
                return 0;
            }

            $remaining = now()->diffInMinutes($this->end_time, false);
            
            Log::info('Calculating remaining time for test', [
                'test_id' => $this->id,
                'end_time' => $this->end_time,
                'remaining_minutes' => $remaining
            ]);
            
            return $remaining;
        } catch (Exception $e) {
            Log::error('Error calculating remaining time', [
                'test_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function getStatusAttribute()
    {
        try {
            $status = now() < $this->start_time ? 'upcoming' :
                     ($this->isAvailable() ? 'active' : 'expired');
            
            Log::info('Getting test status', [
                'test_id' => $this->id,
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'is_active' => $this->is_active,
                'status' => $status
            ]);
            
            return $status;
        } catch (Exception $e) {
            Log::error('Error getting test status', [
                'test_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function getFormattedDurationAttribute()
    {
        try {
            $hours = floor($this->duration_minutes / 60);
            $minutes = $this->duration_minutes % 60;

            $formatted = $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
            
            Log::info('Formatting test duration', [
                'test_id' => $this->id,
                'duration_minutes' => $this->duration_minutes,
                'formatted' => $formatted
            ]);
            
            return $formatted;
        } catch (Exception $e) {
            Log::error('Error formatting test duration', [
                'test_id' => $this->id,
                'duration_minutes' => $this->duration_minutes,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function getTotalMarksAttribute()
    {
        try {
            // Calculate the total marks based on the number of questions
            // Each question is worth 1 mark
            $totalMarks = $this->questions()->count();
            
            Log::info('Calculating total marks for test', [
                'test_id' => $this->id,
                'total_marks' => $totalMarks
            ]);
            
            return $totalMarks;
        } catch (Exception $e) {
            Log::error('Error calculating total marks', [
                'test_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 0;
        }
    }
} 