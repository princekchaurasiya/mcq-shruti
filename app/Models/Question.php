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
        'options' => 'array',
        'correct_option' => 'array',
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

    public function getFormattedOptionsAttribute()
    {
        return collect($this->options)->map(function($option, $index) {
            return [
                'id' => $index,
                'text' => $option,
                'is_correct' => in_array($option, $this->correct_option)
            ];
        });
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
