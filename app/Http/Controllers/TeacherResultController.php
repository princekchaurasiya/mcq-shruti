<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TestAttempt;
use Illuminate\Support\Facades\Auth;
use App\Traits\LoggableTrait;
use App\Models\Question;

class TeacherResultController extends Controller
{
    use LoggableTrait;

    public function index()
    {
        try {
            $teacher = Auth::user()->teacher;
            
            if (!$teacher) {
                $this->logWarning('Unauthorized access attempt to teacher results', [
                    'user_id' => Auth::id(),
                    'user_role' => Auth::user()->role
                ]);
                abort(403, 'Unauthorized action.');
            }

            $results = TestAttempt::whereHas('mcqTest', function($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })
            ->with(['mcqTest.subject', 'user'])
            ->latest()
            ->get();

            $this->logInfo('Teacher viewed student results list', [
                'teacher_id' => $teacher->id,
                'results_count' => $results->count()
            ]);

            return view('teacher.results.index', compact('results'));
        } catch (\Exception $e) {
            $this->logError('Error retrieving student results list', [
                'error' => $e->getMessage(),
                'teacher_id' => Auth::user()->teacher->id ?? null
            ]);
            return back()->with('error', 'Unable to retrieve results. Please try again.');
        }
    }

    /**
     * Display the specified student test result.
     */
    public function show($id)
    {
        try {
            $testAttempt = TestAttempt::with(['user', 'mcqTest.subject', 'responses.question'])->findOrFail($id);
            
            // Get the formatted responses using our model attribute
            $processedAnswers = $testAttempt->formatted_responses ?? collect([]);
            
            \Log::info('Teacher viewing student result', [
                'teacher_id' => auth()->id(),
                'student_id' => $testAttempt->user_id,
                'result_id' => $testAttempt->id,
                'answers_count' => $processedAnswers->count()
            ]);
            
            // Calculate time taken in minutes
            $timeTaken = $testAttempt->completed_at 
                ? ceil($testAttempt->completed_at->diffInSeconds($testAttempt->started_at) / 60) 
                : null;
                
            // Count questions in each category
            $totalQuestions = $testAttempt->mcqTest->questions->count() ?? 0;
            $correctAnswers = $testAttempt->responses->where('is_correct', true)->count() ?? 0;
            $incorrectAnswers = $testAttempt->responses->where('is_correct', false)->count() ?? 0;
            $unansweredQuestions = $totalQuestions - $correctAnswers - $incorrectAnswers;
            
            // Get the attempt number for this student
            $attemptNumber = TestAttempt::where('user_id', $testAttempt->user_id)
                ->where('mcq_test_id', $testAttempt->mcq_test_id)
                ->where('created_at', '<=', $testAttempt->created_at)
                ->count();
                
            // Calculate percentages for charts
            $correctPercentage = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 1) : 0;
            $incorrectPercentage = $totalQuestions > 0 ? round(($incorrectAnswers / $totalQuestions) * 100, 1) : 0;
            $unansweredPercentage = $totalQuestions > 0 ? round(($unansweredQuestions / $totalQuestions) * 100, 1) : 0;
            
            // Get responses more directly for the view
            $responses = $testAttempt->responses()->with('question')->get();
            
            // Convert the formatted responses to the format the view expects
            $questions = [];
            foreach ($processedAnswers as $answer) {
                $questions[] = [
                    'question_text' => $answer['question']['text'] ?? 'Question text unavailable',
                    'explanation' => $answer['question']['explanation'] ?? null,
                    'options' => array_map(function($option) {
                        return $option['text'] ?? 'Option text unavailable';
                    }, $answer['options'] ?? []),
                    'selected_option' => $this->findSelectedOptionIndex($answer['options'] ?? []),
                    'correct_option' => $this->findCorrectOptionIndex($answer['options'] ?? []),
                    'is_correct' => $answer['is_correct'] ?? false,
                    'is_answered' => $answer['is_answered'] ?? false
                ];
            }
            
            return view('teacher.results.show', compact(
                'testAttempt',
                'timeTaken',
                'totalQuestions',
                'correctAnswers',
                'incorrectAnswers',
                'unansweredQuestions',
                'correctPercentage',
                'incorrectPercentage',
                'unansweredPercentage',
                'questions',
                'attemptNumber',
                'responses'
            ));
        } catch (\Exception $e) {
            \Log::error('Error displaying test result', [
                'test_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->view('errors.custom', [
                'errorTitle' => 'Error Displaying Result',
                'errorMessage' => 'We encountered an issue while trying to display this test result. Our team has been notified.'
            ], 500);
        }
    }
    
    /**
     * Helper method to find the index of the selected option
     */
    private function findSelectedOptionIndex($options)
    {
        foreach ($options as $index => $option) {
            if (isset($option['is_selected']) && $option['is_selected']) {
                return $index;
            }
        }
        return null;
    }
    
    /**
     * Helper method to find the index of the correct option
     */
    private function findCorrectOptionIndex($options)
    {
        foreach ($options as $index => $option) {
            if (isset($option['is_correct']) && $option['is_correct']) {
                return $index;
            }
        }
        return null;
    }
} 