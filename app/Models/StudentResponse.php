<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_attempt_id',
        'question_id',
        'selected_option',
        'is_correct',
        'is_marked_for_review'
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'selected_option' => 'json',
        'is_marked_for_review' => 'boolean'
    ];

    public function testAttempt()
    {
        return $this->belongsTo(TestAttempt::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedOption()
    {
        return $this->belongsTo(Option::class, 'selected_option_id');
    }

    // This is called before saving to set the is_correct flag
    public static function boot()
    {
        parent::boot();
        
        static::saving(function ($response) {
            // Skip if marked for review only or no selection
            if ($response->is_marked_for_review && empty($response->selected_option)) {
                $response->is_correct = null;
                return;
            }
            
            // If selected_option is a string (JSON), decode it
            $selectedOptions = $response->selected_option;
            if (is_string($selectedOptions) && !is_null($selectedOptions)) {
                $selectedOptions = json_decode($selectedOptions, true);
            }
            
            // Get the question
            $question = Question::find($response->question_id);
            if ($question && $selectedOptions) {
                // Check if the selected options match the correct options
                $correctOptions = $question->correct_option;
                
                // Convert to arrays and sort for comparison
                $selectedArray = is_array($selectedOptions) ? $selectedOptions : [$selectedOptions];
                $correctArray = is_array($correctOptions) ? $correctOptions : [$correctOptions];
                
                sort($selectedArray);
                sort($correctArray);
                
                // Set is_correct based on match
                $response->is_correct = $selectedArray == $correctArray;
            } else {
                $response->is_correct = false;
            }
        });
    }
} 