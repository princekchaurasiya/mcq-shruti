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
        'selected_option' => 'string',
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
} 