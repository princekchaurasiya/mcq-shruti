<?php

namespace App\Http\Controllers;

use App\Models\StudentTestResult;
use App\Models\StudentResponse;
use Illuminate\Http\Request;

class StudentResultController extends Controller
{
    public function show(StudentTestResult $result)
    {
        $answers = StudentResponse::with('question')
            ->where('student_test_result_id', $result->id)
            ->get();
        
        // Debug: Log the structure of answers and questions
        if (env('APP_DEBUG', false)) {
            \Log::debug('Test Result Structure:', [
                'result_id' => $result->id,
                'total_answers' => $answers->count(),
                'sample_answer' => $answers->first() ? [
                    'question_id' => $answers->first()->question_id,
                    'selected_option' => $answers->first()->selected_option,
                    'is_correct' => $answers->first()->is_correct,
                    'question_options' => $answers->first()->question->options,
                    'question_correct_option' => $answers->first()->question->correct_option,
                ] : null,
                'incorrect_answer' => $answers->where('is_correct', false)->first() ? [
                    'question_id' => $answers->where('is_correct', false)->first()->question_id,
                    'selected_option' => $answers->where('is_correct', false)->first()->selected_option,
                    'question_options' => $answers->where('is_correct', false)->first()->question->options,
                    'question_correct_option' => $answers->where('is_correct', false)->first()->question->correct_option,
                ] : null
            ]);
        }
        
        return view('student.results.show', compact('result', 'answers'));
    }
} 