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
        return $this->hasMany(StudentResponse::class, 'test_attempt_id');
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

    /**
     * Process options data for consistent display across all views
     * 
     * @return array Formatted responses with properly processed options
     */
    public function getFormattedResponsesAttribute()
    {
        $formattedResponses = collect();
        
        foreach ($this->responses as $response) {
            if (!$response->question) {
                \Log::warning('Question not found for response', [
                    'response_id' => $response->id,
                    'question_id' => $response->question_id
                ]);
                continue;
            }
            
            // Get options from the question
            $options = $response->question->options;
            $correctOption = $response->question->correct_option;
            $selectedOption = $response->selected_option;
            
            // Convert to arrays if needed
            $options = $this->ensureArray($options);
            $correctOption = $this->ensureArray($correctOption);
            $selectedOption = $this->ensureArray($selectedOption);
            
            // Format options for display
            $processedOptions = [];
            foreach ($options as $index => $optionText) {
                $isSelected = in_array($index, $selectedOption);
                $isCorrect = in_array($index, $correctOption);
                
                $processedOptions[] = [
                    'text' => is_string($optionText) ? $optionText : "Option " . strtoupper(chr(97 + $index)),
                    'letter' => strtoupper(chr(97 + $index)),
                    'is_selected' => $isSelected,
                    'is_correct' => $isCorrect
                ];
            }
            
            // If we still have no options, create dummies
            if (empty($processedOptions)) {
                for ($i = 0; $i < 4; $i++) {
                    $letter = strtoupper(chr(97 + $i));
                    $processedOptions[] = [
                        'text' => "Option {$letter}",
                        'letter' => $letter,
                        'is_selected' => false,
                        'is_correct' => false
                    ];
                }
            }
            
            // Make sure arrays are properly formatted for mapping
            if (!is_array($selectedOption)) {
                $selectedOption = [];
            }
            
            if (!is_array($correctOption)) {
                $correctOption = [];
            }
            
            $formattedResponses->push([
                'question' => [
                    'text' => $response->question->question_text,
                    'explanation' => $response->question->explanation ?? null
                ],
                'options' => $processedOptions,
                'is_answered' => !empty($selectedOption),
                'is_correct' => $response->is_correct,
                'selected_options' => array_map(function($idx) {
                    // Ensure $idx is an integer
                    $idx = intval($idx);
                    return strtoupper(chr(97 + $idx));
                }, $selectedOption),
                'correct_options' => array_map(function($idx) {
                    // Ensure $idx is an integer
                    $idx = intval($idx);
                    return strtoupper(chr(97 + $idx));
                }, $correctOption)
            ]);
        }
        
        return $formattedResponses;
    }
    
    /**
     * Helper method to ensure a value is an array
     * 
     * @param mixed $value
     * @return array
     */
    private function ensureArray($value)
    {
        if (empty($value)) {
            return [];
        }
        
        if (is_string($value)) {
            try {
                $decoded = json_decode($value, true);
                return is_array($decoded) ? $decoded : [];
            } catch (\Exception $e) {
                return [];
            }
        }
        
        return is_array($value) ? $value : [];
    }
} 