<?php

namespace App\Http\Controllers;

use App\Models\StudentTestResult;
use App\Models\StudentResponse;
use Illuminate\Http\Request;

class StudentResultController extends Controller
{
    public function show(StudentTestResult $result)
    {
        \Log::info('Starting to process result', [
            'result_id' => $result->id,
            'test_id' => $result->mcq_test_id
        ]);

        try {
            // First get the raw data from the database
            $rawAnswers = \DB::table('student_responses')
                ->join('questions', 'student_responses.question_id', '=', 'questions.id')
                ->where('test_attempt_id', $result->id)
                ->select([
                    'student_responses.id',
                    'student_responses.question_id',
                    'student_responses.selected_option',
                    'student_responses.is_correct',
                    'questions.question_text',
                    'questions.options',
                    'questions.correct_option',
                    'questions.explanation'
                ])
                ->get();

            \Log::info('Raw database data retrieved', [
                'raw_answers_count' => $rawAnswers->count(),
                'first_raw_answer' => $rawAnswers->first() ? [
                    'id' => $rawAnswers->first()->id,
                    'question_text' => $rawAnswers->first()->question_text,
                    'options' => $rawAnswers->first()->options,
                    'selected_option' => $rawAnswers->first()->selected_option,
                    'correct_option' => $rawAnswers->first()->correct_option
                ] : null
            ]);
        } catch (\Exception $e) {
            \Log::error('Error retrieving raw data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $rawAnswers = collect([]);
        }

        try {
            $answers = StudentResponse::with(['question' => function($query) {
                $query->withCasts([
                    'options' => 'array',
                    'correct_option' => 'array'
                ]);
            }])
            ->where('test_attempt_id', $result->id)
            ->get();
        
            \Log::info('Retrieved student responses', [
                'response_count' => $answers->count(),
                'first_response' => $answers->first() ? [
                    'id' => $answers->first()->id,
                    'question_id' => $answers->first()->question_id,
                    'raw_selected_option' => $answers->first()->selected_option,
                    'has_question' => $answers->first()->question ? true : false,
                    'question_options' => $answers->first()->question ? $answers->first()->question->options : null,
                    'question_correct_option' => $answers->first()->question ? $answers->first()->question->correct_option : null
                ] : null
            ]);
        } catch (\Exception $e) {
            \Log::error('Error retrieving student responses', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $answers = collect([]);
        }

        // If both queries failed or returned empty results, create dummy data for display
        if ($answers->isEmpty() && $rawAnswers->isEmpty()) {
            \Log::warning('No data found for result, creating dummy data', [
                'result_id' => $result->id
            ]);
            
            // Create dummy answers for display
            $processedAnswers = collect([
                [
                    'question' => [
                        'text' => 'Question data not available',
                        'explanation' => null
                    ],
                    'options' => [
                        ['letter' => 'A', 'text' => 'No option data available', 'is_selected' => false, 'is_correct' => false],
                        ['letter' => 'B', 'text' => 'No option data available', 'is_selected' => false, 'is_correct' => false],
                        ['letter' => 'C', 'text' => 'No option data available', 'is_selected' => false, 'is_correct' => false],
                        ['letter' => 'D', 'text' => 'No option data available', 'is_selected' => false, 'is_correct' => false],
                    ],
                    'is_answered' => false,
                    'is_correct' => false,
                    'selected_options' => [],
                    'correct_options' => []
                ]
            ]);
            
            return view('student.results.show', [
                'result' => $result,
                'answers' => $processedAnswers
            ]);
        }

        // Process the answers normally if we have data
        $processedAnswers = $answers->map(function ($answer) {
            \Log::info('Processing individual answer', [
                'answer_id' => $answer->id,
                'has_question' => $answer->question ? 'yes' : 'no',
                'raw_selected_option' => $answer->selected_option,
                'raw_options' => $answer->question ? $answer->question->options : null,
                'raw_correct_option' => $answer->question ? $answer->question->correct_option : null,
                'raw_question_data' => $answer->question ? [
                    'id' => $answer->question->id,
                    'text' => $answer->question->question_text,
                    'options' => $answer->question->options,
                    'correct_option' => $answer->question->correct_option,
                    'explanation' => $answer->question->explanation
                ] : null
            ]);

            // Ensure we have valid data before processing
            if (!$answer->question) {
                \Log::warning('Question not found for answer', ['answer_id' => $answer->id]);
                return null;
            }

            // Get options from the question
            $options = $answer->question->options;
            $correctOption = $answer->question->correct_option;
            $selectedOption = $answer->selected_option;

            \Log::info('Data types of important fields', [
                'options_type' => gettype($options),
                'options_value' => $options,
                'correct_option_type' => gettype($correctOption),
                'correct_option_value' => $correctOption,
                'selected_option_type' => gettype($selectedOption),
                'selected_option_value' => $selectedOption,
                'is_options_empty' => empty($options),
                'is_correct_option_empty' => empty($correctOption),
                'is_selected_option_empty' => empty($selectedOption)
            ]);

            // Process options - ensure we always have an array
            if (is_string($options)) {
                \Log::info('Options is a string, attempting to decode JSON', ['raw_options' => $options]);
                try {
                    $options = json_decode($options, true);
                    \Log::info('Decoded options from JSON', ['decoded_options' => $options]);
                } catch (\Exception $e) {
                    \Log::error('Failed to decode options', [
                        'error' => $e->getMessage(),
                        'raw_options' => $options
                    ]);
                    $options = [];
                }
            }

            // If options is still not an array, create a default
            if (!is_array($options)) {
                \Log::warning('Options is not an array after processing', [
                    'type' => gettype($options),
                    'value' => $options
                ]);
                $options = [];
            }

            // Process correct option - ensure we always have an array
            if (is_string($correctOption)) {
                \Log::info('Correct option is a string, attempting to decode JSON', ['raw_correct_option' => $correctOption]);
                try {
                    $correctOption = json_decode($correctOption, true);
                    \Log::info('Decoded correct option from JSON', ['decoded_correct_option' => $correctOption]);
                } catch (\Exception $e) {
                    \Log::error('Failed to decode correct option', [
                        'error' => $e->getMessage(),
                        'raw_correct_option' => $correctOption
                    ]);
                    $correctOption = [];
                }
            }

            // If correct option is still not an array, create a default
            if (!is_array($correctOption)) {
                \Log::warning('Correct option is not an array after processing', [
                    'type' => gettype($correctOption),
                    'value' => $correctOption
                ]);
                $correctOption = [];
            }

            // Process selected option - ensure we always have an array
            if (empty($selectedOption)) {
                \Log::info('No selected option found', ['answer_id' => $answer->id]);
                $selectedOption = [];
            } elseif (is_string($selectedOption)) {
                \Log::info('Selected option is string, attempting to decode JSON', [
                    'raw_selected_option' => $selectedOption
                ]);
                try {
                    $selectedOption = json_decode($selectedOption, true) ?? [];
                    \Log::info('Decoded selected option from JSON', ['decoded_selected_option' => $selectedOption]);
                } catch (\Exception $e) {
                    \Log::error('Failed to decode selected option', [
                        'answer_id' => $answer->id,
                        'error' => $e->getMessage(),
                        'raw_selected_option' => $selectedOption
                    ]);
                    $selectedOption = [];
                }
            }

            // Ensure selected option is an array
            $selectedArray = is_array($selectedOption) ? $selectedOption : [];

            \Log::info('Final arrays after processing', [
                'options_array' => $options,
                'selected_array' => $selectedArray,
                'correct_array' => $correctOption
            ]);
            
            // Process options to include additional information
            $processedOptions = [];
            foreach ($options as $index => $optionText) {
                $isSelected = in_array($index, $selectedArray, true);
                $isCorrect = in_array($index, $correctOption, true);
                
                $processedOptions[] = [
                    'text' => is_string($optionText) ? $optionText : 'Option text not available',
                    'letter' => strtoupper(chr(ord('a') + $index)),
                    'is_selected' => $isSelected,
                    'is_correct' => $isCorrect
                ];
            }

            \Log::info('Processed options', [
                'answer_id' => $answer->id,
                'processed_options' => $processedOptions
            ]);

            $result = [
                'question' => [
                    'text' => $answer->question->question_text ?? 'Question text not available',
                    'explanation' => $answer->question->explanation ?? null
                ],
                'options' => $processedOptions,
                'is_answered' => !empty($selectedArray),
                'is_correct' => $answer->is_correct ?? false,
                'selected_options' => array_map(function($index) {
                    return strtoupper(chr(ord('a') + $index));
                }, $selectedArray),
                'correct_options' => array_map(function($index) {
                    return strtoupper(chr(ord('a') + $index));
                }, $correctOption)
            ];

            \Log::info('Final answer data structure', [
                'answer_id' => $answer->id,
                'result' => $result
            ]);

            return $result;
        })
        ->filter()
        ->values();

        \Log::info('Final processed answers', [
            'total_answers' => $processedAnswers->count(),
            'sample_answer' => $processedAnswers->first()
        ]);
        
        // Set the processed answers as a property on the result object
        // Convert to a proper collection to ensure isEmpty() works
        $result->formattedResponses = collect($processedAnswers);
        
        return view('student.results.show', [
            'result' => $result
        ]);
    }
    
    /**
     * Create test data for debugging
     */
    public function seedTestData(StudentTestResult $result)
    {
        \Log::info('Seeding test data for debugging', [
            'result_id' => $result->id,
            'test_id' => $result->mcq_test_id
        ]);
        
        try {
            // First check if we have questions for this test
            $testQuestions = \DB::table('questions')
                ->where('mcq_test_id', $result->mcq_test_id)
                ->get();
                
            if ($testQuestions->isEmpty()) {
                // Create test questions
                for ($i = 1; $i <= 5; $i++) {
                    \DB::table('questions')->insert([
                        'mcq_test_id' => $result->mcq_test_id,
                        'question_text' => "Sample Question {$i}?",
                        'options' => json_encode(["Option A", "Option B", "Option C", "Option D"]),
                        'correct_option' => json_encode([1]), // Option B is correct
                        'explanation' => "This is the explanation for question {$i}",
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
                
                $testQuestions = \DB::table('questions')
                    ->where('mcq_test_id', $result->mcq_test_id)
                    ->get();
            }
            
            // Check if we have student responses
            $responses = \DB::table('student_responses')
                ->where('test_attempt_id', $result->id)
                ->get();
                
            if ($responses->isEmpty()) {
                // Create student responses
                foreach ($testQuestions as $index => $question) {
                    // Alternate correct and incorrect answers
                    $selectedOption = ($index % 2 == 0) ? [1] : [2]; // Correct for even, incorrect for odd
                    $isCorrect = ($index % 2 == 0);
                    
                    \DB::table('student_responses')->insert([
                        'test_attempt_id' => $result->id,
                        'question_id' => $question->id,
                        'selected_option' => json_encode($selectedOption),
                        'is_correct' => $isCorrect,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
            
            return redirect()->route('student.results.show', $result->id)
                ->with('success', 'Test data generated successfully!');
        } catch (\Exception $e) {
            \Log::error('Error seeding test data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('student.results.show', $result->id)
                ->with('error', 'Failed to generate test data: ' . $e->getMessage());
        }
    }
} 