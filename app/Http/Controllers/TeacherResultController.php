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

    public function show($id)
    {
        try {
            $testAttempt = TestAttempt::with(['user', 'mcqTest.questions', 'responses'])
                ->findOrFail($id);
            
            // Only the teacher who created the test can view results
            if (auth()->user()->teacher->id !== $testAttempt->mcqTest->user_id) {
                $this->logWarning('Unauthorized access attempt to test result details', [
                    'teacher_id' => auth()->id(),
                    'test_owner_id' => $testAttempt->mcqTest->user_id,
                    'test_attempt_id' => $id
                ]);
                
                return redirect()->route('teacher.dashboard')
                    ->with('error', 'You can only view results for your own tests.');
            }
            
            // Calculate time taken in minutes
            $timeTaken = $testAttempt->completed_at 
                ? ceil($testAttempt->completed_at->diffInSeconds($testAttempt->started_at) / 60) 
                : null;
            
            // Count questions in each category
            $totalQuestions = $testAttempt->mcqTest->questions->count();
            $correctAnswers = $testAttempt->responses->where('is_correct', true)->count();
            $incorrectAnswers = $testAttempt->responses->where('is_correct', false)->count();
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
            
            // Organize questions with responses
            $questions = [];
            
            foreach ($testAttempt->mcqTest->questions as $question) {
                $response = $testAttempt->responses->firstWhere('question_id', $question->id);
                
                $questions[] = [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'options' => json_decode($question->options, true),
                    'correct_option' => json_decode($question->correct_option, true),
                    'selected_option' => $response ? $response->selected_option : null,
                    'is_correct' => $response ? $response->is_correct : false,
                    'is_answered' => $response !== null,
                    'explanation' => $question->explanation
                ];
            }
            
            $this->logInfo('Teacher viewed test attempt details', [
                'teacher_id' => auth()->user()->teacher->id,
                'test_attempt_id' => $testAttempt->id,
                'student_id' => $testAttempt->user_id
            ]);
            
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
                'attemptNumber'
            ));
        } catch (\Exception $e) {
            $this->logError('Error displaying test attempt details', [
                'test_attempt_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Unable to view test result details. Please try again.');
        }
    }
} 