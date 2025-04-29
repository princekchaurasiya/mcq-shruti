<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TestAttempt;
use Illuminate\Support\Facades\Auth;
use App\Models\Question;

class ResultController extends Controller
{
    /**
     * Display a listing of the results.
     */
    public function index()
    {
        $results = Auth::user()->testAttempts()
            ->with(['mcqTest.subject'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student.results.index', compact('results'));
    }

    /**
     * Display the specified result.
     */
    public function show(TestAttempt $result)
    {
        // Check if the result belongs to the authenticated user
        if ($result->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        \Log::info('Processing test result', [
            'result_id' => $result->id,
            'test_id' => $result->mcq_test_id,
            'user_id' => $result->user_id
        ]);

        // Load necessary relationships
        $result->load(['mcqTest.subject', 'responses.question']);
        
        // Manually get questions to avoid potential relationship issues
        $questions = Question::where('mcq_test_id', $result->mcq_test_id)
            ->get();
        
        $result->mcqTest->setRelation('questions', $questions);
        
        // Use the formatted responses from the model attribute - add try/catch for safety
        try {
            $processedAnswers = $result->formatted_responses;
            
            \Log::info('Final processed answers', [
                'count' => $processedAnswers->count(),
                'first_answer' => $processedAnswers->first()
            ]);
            
            return view('student.results.show', [
                'result' => $result,
                'answers' => $processedAnswers
            ]);
        } catch (\Exception $e) {
            \Log::error('Error processing formatted responses', [
                'result_id' => $result->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Create a simple fallback for answers if formatted_responses fails
            $fallbackAnswers = collect($result->responses)->map(function($response) {
                return [
                    'question' => [
                        'text' => $response->question ? $response->question->question_text : 'Question text unavailable',
                        'explanation' => $response->question ? ($response->question->explanation ?? null) : null
                    ],
                    'options' => [],
                    'is_answered' => !empty($response->selected_option),
                    'is_correct' => $response->is_correct,
                    'selected_options' => [],
                    'correct_options' => []
                ];
            });
            
            return view('student.results.show', [
                'result' => $result,
                'answers' => $fallbackAnswers,
                'error_message' => 'There was an issue processing some test data. Basic results are shown below.'
            ]);
        }
    }

    /**
     * Create test data for debugging
     */
    public function seedTestData(TestAttempt $result)
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
            
            return redirect()->route('results.show', $result->id)
                ->with('success', 'Test data generated successfully!');
        } catch (\Exception $e) {
            \Log::error('Error seeding test data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('results.show', $result->id)
                ->with('error', 'Failed to generate test data: ' . $e->getMessage());
        }
    }
} 