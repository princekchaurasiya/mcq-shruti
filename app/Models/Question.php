<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'mcq_test_id',
        'question_text',
        'options',
        'correct_option',
        'explanation',
        'marks',
        'image_path'
    ];

    protected $casts = [
        'options' => 'json',
        'correct_option' => 'json',
        'marks' => 'integer'
    ];

    public function mcqTest()
    {
        return $this->belongsTo(MCQTest::class, 'mcq_test_id');
    }

    public function options()
    {
        return $this->hasMany(Option::class);
    }

    public function responses()
    {
        return $this->hasMany(StudentResponse::class);
    }

    public function correctOption()
    {
        return $this->options()->where('is_correct', true)->first();
    }

    public function isCorrectAnswer($selectedOptions)
    {
        // Convert single option to array if needed
        if (!is_array($selectedOptions)) {
            $selectedOptions = [$selectedOptions];
        }

        // Sort both arrays to ensure order doesn't matter
        sort($selectedOptions);
        $correctOptions = $this->correct_option;
        sort($correctOptions);

        // Check if the selected options match the correct options exactly
        return $selectedOptions == $correctOptions;
    }

    public function getImageUrlAttribute()
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    public function getOptionsArrayAttribute()
    {
        $options = $this->options;
        \Log::info('Getting options array', [
            'question_id' => $this->id,
            'raw_options' => $options,
            'is_string' => is_string($options),
            'is_array' => is_array($options),
            'is_null' => is_null($options)
        ]);

        if (empty($options)) {
            \Log::info('Options are empty', ['question_id' => $this->id]);
            return [];
        }

        // If it's already an array, return it
        if (is_array($options)) {
            return $options;
        }

        // If it's a JSON string, decode it
        if (is_string($options)) {
            try {
                $decoded = json_decode($options, true);
                \Log::info('Decoded options from string', [
                    'question_id' => $this->id,
                    'decoded_result' => $decoded
                ]);
                return $decoded ?? [];
            } catch (\Exception $e) {
                \Log::error('Failed to decode options', [
                    'question_id' => $this->id,
                    'error' => $e->getMessage()
                ]);
                return [];
            }
        }

        return [];
    }

    public function getCorrectOptionArrayAttribute()
    {
        $correctOption = $this->correct_option;
        \Log::info('Getting correct option array', [
            'question_id' => $this->id,
            'raw_correct_option' => $correctOption,
            'is_string' => is_string($correctOption),
            'is_array' => is_array($correctOption),
            'is_null' => is_null($correctOption)
        ]);

        if (empty($correctOption)) {
            \Log::info('Correct options are empty', ['question_id' => $this->id]);
            return [];
        }

        // If it's already an array, return it
        if (is_array($correctOption)) {
            return $correctOption;
        }

        // If it's a JSON string, decode it
        if (is_string($correctOption)) {
            try {
                $decoded = json_decode($correctOption, true);
                \Log::info('Decoded correct options from string', [
                    'question_id' => $this->id,
                    'decoded_result' => $decoded
                ]);
                return $decoded ?? [];
            } catch (\Exception $e) {
                \Log::error('Failed to decode correct options', [
                    'question_id' => $this->id,
                    'error' => $e->getMessage()
                ]);
                return [];
            }
        }

        return [];
    }

    public function getFormattedOptionsAttribute()
    {
        $options = $this->options_array;
        $correctOption = $this->correct_option_array;
        
        \Log::info('Formatting options', [
            'question_id' => $this->id,
            'raw_options' => $options,
            'raw_correct_options' => $correctOption,
            'options_type' => gettype($options),
            'correct_option_type' => gettype($correctOption)
        ]);
        
        if (empty($options)) {
            \Log::warning('No options available for formatting', [
                'question_id' => $this->id,
                'options' => $options
            ]);
            return [];
        }

        if (!is_array($options)) {
            \Log::warning('Options is not an array, attempting to convert', [
                'question_id' => $this->id,
                'options_type' => gettype($options)
            ]);
            try {
                $options = json_decode($options, true);
            } catch (\Exception $e) {
                \Log::error('Failed to decode options', [
                    'question_id' => $this->id,
                    'error' => $e->getMessage()
                ]);
                return [];
            }
        }

        $formatted = collect($options)->map(function($option, $index) use ($correctOption) {
            $result = [
                'letter' => strtoupper(chr(ord('a') + $index)),
                'text' => is_array($option) ? ($option['text'] ?? 'Option text not available') : (string)$option,
                'is_correct' => in_array($index, (array)$correctOption, true)
            ];
            \Log::info('Formatted option', [
                'question_id' => $this->id,
                'index' => $index,
                'option' => $result
            ]);
            return $result;
        })->values()->all();

        \Log::info('Final formatted options', [
            'question_id' => $this->id,
            'formatted_options' => $formatted
        ]);

        return $formatted;
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function($question) {
            // Delete associated image when question is deleted
            if ($question->image_path) {
                Storage::disk('public')->delete($question->image_path);
            }
        });
    }
}
