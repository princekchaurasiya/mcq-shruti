<?php

namespace App\Http\Controllers;

use App\Models\MCQTest;
use App\Models\Question;
use App\Models\Subject;
use App\Services\MCQQuestionParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\LoggableTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Exception;
use App\Models\TestAttempt;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Models\StudentResponse;

class MCQTestController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, LoggableTrait;

    protected $questionParser;

    public function __construct(MCQQuestionParser $questionParser)
    {
        $this->questionParser = $questionParser;
        Log::info('MCQTestController initialized');
    }

    public function index()
    {
        if (auth()->user()->hasRole('teacher')) {
            try {
                // Get current teacher id
                $teacherId = auth()->user()->teacher->id ?? null;
                
                if (!$teacherId) {
                    Log::error('Teacher ID not found for user', [
                        'user_id' => auth()->id()
                    ]);
                    return view('teacher.mcq-tests.index', [
                        'tests' => collect([]),
                        'error' => 'Teacher profile not found. Please contact administrator.'
                    ]);
                }
                
                // Log the query we're about to run
                Log::info('Fetching MCQ tests for teacher', [
                    'teacher_id' => $teacherId
                ]);
                
                // Use proper teacher_id column instead of user_id and add pagination
                $tests = MCQTest::where('teacher_id', $teacherId)
                    ->with(['subject', 'questions'])
                    ->latest()
                    ->paginate(10); // Add pagination
                
                Log::info('MCQ tests fetched successfully', [
                    'count' => $tests->count(),
                    'teacher_id' => $teacherId
                ]);
                
                return view('teacher.mcq-tests.index', compact('tests'));
            } catch (\Exception $e) {
                Log::error('Error fetching MCQ tests', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return view('errors.custom', [
                    'errorTitle' => 'Error Loading Tests',
                    'errorMessage' => 'We encountered an issue while trying to load your tests. Our team has been notified.'
                ], 500);
            }
        } else {
            // Student view for available tests
            return redirect()->route('available-tests');
        }
    }

    public function create()
    {
        $subjects = Subject::all();
        return view('teacher.mcq-tests.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $teacher = auth()->user()->teacher;

            if (!$teacher) {
                Log::warning('Non-teacher user attempted to create MCQ test', [
                    'user_id' => auth()->id(),
                    'role' => auth()->user()->role,
                    'request_data' => $request->except(['_token'])
                ]);
                throw new Exception('Unauthorized: Only teachers can create tests.');
            }

            Log::info('Starting MCQ test creation', [
                'teacher_id' => $teacher->id,
                'request_data' => $request->except(['_token'])
            ]);

            // Validate table structure
            $tableColumns = Schema::getColumnListing('mcq_tests');
            Log::info('Validating mcq_tests table structure', ['columns' => $tableColumns]);

            $requiredColumns = ['title', 'description', 'duration_minutes', 'passing_percentage', 'start_time', 'end_time', 'subject_id', 'teacher_id'];
            $missingColumns = array_diff($requiredColumns, $tableColumns);

            if (!empty($missingColumns)) {
                Log::error('Missing required columns in mcq_tests table', ['missing_columns' => $missingColumns]);
                throw new Exception('Database schema error: Missing required columns - ' . implode(', ', $missingColumns));
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'duration_minutes' => 'required|integer|min:1',
                'passing_percentage' => 'required|integer|between:1,100',
                'start_time' => 'required|date|after_or_equal:now',
                'end_time' => 'required|date|after:start_time',
                'subject_id' => 'required|exists:subjects,id'
            ]);

            Log::info('Validation passed', ['validated_data' => $validated]);

            try {
                $test = $teacher->mcqTests()->create($validated);

                Log::info('MCQ test created successfully', [
                    'test_id' => $test->id,
                    'teacher_id' => $teacher->id,
                    'test_data' => $test->toArray()
                ]);

                DB::commit();

                return redirect()
                    ->route('mcq-tests.show', $test)
                    ->with('success', 'Test created successfully.');

            } catch (QueryException $e) {
                DB::rollBack();
                Log::error('Database error while creating MCQ test', [
                    'error' => $e->getMessage(),
                    'sql' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'teacher_id' => $teacher->id
                ]);
                throw new Exception('Failed to create test: Database error');
            }

        } catch (ValidationException $e) {
            DB::rollBack();
            Log::warning('MCQ test validation failed', [
                'errors' => $e->errors(),
                'teacher_id' => auth()->user()->teacher->id ?? null,
                'request_data' => $request->except(['_token'])
            ]);

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create MCQ test', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'teacher_id' => auth()->user()->teacher->id ?? null,
                'request_data' => $request->except(['_token'])
            ]);

            return redirect()
                ->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    public function show(MCQTest $mcqTest)
    {
        try {
            Log::info('Fetching MCQ test details', [
                'test_id' => $mcqTest->id,
                'teacher_id' => auth()->user()->teacher->id ?? null
            ]);

            if (auth()->user()->teacher->id !== $mcqTest->teacher_id) {
                Log::warning('Unauthorized access attempt to MCQ test', [
                    'test_id' => $mcqTest->id,
                    'requesting_teacher_id' => auth()->user()->teacher->id,
                    'test_owner_id' => $mcqTest->teacher_id
                ]);
                throw new Exception('Unauthorized access to test');
            }

            $mcqTest = $mcqTest->load(['subject']);
            
            // Manually get questions to avoid potential relationship issues
            $questions = Question::where('mcq_test_id', $mcqTest->id)
                ->with('options')
                ->get();
                
            $mcqTest->setRelation('questions', $questions);

            Log::info('Successfully fetched MCQ test details', [
                'test_id' => $mcqTest->id,
                'question_count' => $mcqTest->questions->count(),
                'teacher_id' => auth()->user()->teacher->id
            ]);

            return view('teacher.mcq-tests.show', compact('mcqTest'));

        } catch (Exception $e) {
            Log::error('Error showing MCQ test', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'test_id' => $mcqTest->id,
                'teacher_id' => auth()->user()->teacher->id ?? null
            ]);

            return redirect()
                ->route('mcq-tests.index')
                ->with('error', $e->getMessage());
        }
    }

    public function edit(MCQTest $mcqTest)
    {
        try {
            // Check if teacher is the owner of this test
            if (auth()->user()->teacher->id !== $mcqTest->teacher_id) {
                Log::warning('Unauthorized access attempt to edit MCQ test', [
                    'test_id' => $mcqTest->id,
                    'requesting_teacher_id' => auth()->user()->teacher->id,
                    'test_owner_id' => $mcqTest->teacher_id
                ]);
                return redirect()->route('mcq-tests.index')
                    ->with('error', 'You can only edit your own tests.');
            }
            
            return view('teacher.mcq-tests.edit', compact('mcqTest'));
        } catch (Exception $e) {
            Log::error('Error editing MCQ test', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'test_id' => $mcqTest->id,
                'teacher_id' => auth()->user()->teacher->id ?? null
            ]);

            return redirect()
                ->route('mcq-tests.index')
                ->with('error', 'An error occurred while trying to edit the test.');
        }
    }

    public function update(Request $request, MCQTest $mcqTest)
    {
        try {
            // Check if teacher is the owner of this test
            if (auth()->user()->teacher->id !== $mcqTest->teacher_id) {
                Log::warning('Unauthorized access attempt to update MCQ test', [
                    'test_id' => $mcqTest->id,
                    'requesting_teacher_id' => auth()->user()->teacher->id,
                    'test_owner_id' => $mcqTest->teacher_id
                ]);
                return redirect()->route('mcq-tests.index')
                    ->with('error', 'You can only update your own tests.');
            }

            // Different validation rules based on whether the test has already started
            $startTimeRule = 'required|date';
            $now = now();
            
            // If the test hasn't started yet, force start_time to be in the future
            if ($mcqTest->start_time->gt($now)) {
                $startTimeRule .= '|after_or_equal:now';
            }

            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'duration_minutes' => 'required|integer|min:1',
                'passing_percentage' => 'required|integer|between:1,100',
                'start_time' => $startTimeRule,
                'end_time' => 'required|date|after:start_time',
            ]);

            // First, update all fields except is_active
            $data = $request->except(['_token', '_method', 'is_active']);
            $mcqTest->update($data);
            
            // Handle the is_active field separately
            
            // For HTML form checkbox behavior:
            // 1. When checkbox is checked, is_active will be in the request
            // 2. When checkbox is unchecked, is_active won't be in the request at all
            
            // For API/JSON requests:
            // We need to handle explicit is_active=false
            
            $isActive = false; // Default to false
            
            // If is_active is in the request
            if ($request->has('is_active')) {
                $rawValue = $request->input('is_active');
                
                // Handle falsy string values explicitly
                if (is_string($rawValue)) {
                    $lowercaseValue = strtolower($rawValue);
                    if (in_array($lowercaseValue, ['0', 'false', 'no', 'off'])) {
                        $isActive = false;
                    } else {
                        // For other string values, use filter_var
                        $isActive = filter_var($rawValue, FILTER_VALIDATE_BOOLEAN);
                    }
                } else if (is_null($rawValue) || $rawValue === '') {
                    // Explicitly handle null and empty string
                    $isActive = false;
                } else {
                    // For boolean or numeric values
                    $isActive = filter_var($rawValue, FILTER_VALIDATE_BOOLEAN);
                }
            }
            
            // Log the processed value
            Log::info('Processing is_active value', [
                'test_id' => $mcqTest->id,
                'raw_value' => $request->has('is_active') ? $request->input('is_active') : 'not present',
                'processed_value' => $isActive,
                'is_active_type' => gettype($isActive)
            ]);
            
            // Explicitly update is_active directly in the database to ensure it's saved correctly
            DB::table('mcq_tests')
                ->where('id', $mcqTest->id)
                ->update(['is_active' => $isActive]);
            
            // Refresh the model to get the updated values
            $mcqTest->refresh();
            
            Log::info('MCQ Test updated', [
                'test_id' => $mcqTest->id,
                'is_active' => $mcqTest->is_active,
                'db_is_active' => DB::table('mcq_tests')->where('id', $mcqTest->id)->value('is_active')
            ]);

            return redirect()->route('mcq-tests.show', $mcqTest)
                ->with('success', 'Test updated successfully.');
        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (Exception $e) {
            Log::error('Error updating MCQ test', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'test_id' => $mcqTest->id,
                'teacher_id' => auth()->user()->teacher->id ?? null
            ]);

            return redirect()
                ->back()
                ->with('error', 'An error occurred while trying to update the test.')
                ->withInput();
        }
    }

    public function destroy(MCQTest $mcqTest)
    {
        try {
            // Check if teacher is the owner of this test
            if (auth()->user()->teacher->id !== $mcqTest->teacher_id) {
                Log::warning('Unauthorized access attempt to delete MCQ test', [
                    'test_id' => $mcqTest->id,
                    'requesting_teacher_id' => auth()->user()->teacher->id,
                    'test_owner_id' => $mcqTest->teacher_id
                ]);
                return redirect()->route('mcq-tests.index')
                    ->with('error', 'You can only delete your own tests.');
            }

            $mcqTest->delete();

            return redirect()->route('mcq-tests.index')
                ->with('success', 'Test deleted successfully.');
        } catch (Exception $e) {
            Log::error('Error deleting MCQ test', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'test_id' => $mcqTest->id,
                'teacher_id' => auth()->user()->teacher->id ?? null
            ]);

            return redirect()
                ->route('mcq-tests.index')
                ->with('error', 'An error occurred while trying to delete the test.');
        }
    }

    public function availableTests()
    {
        return MCQTest::where('end_time', '>', now())
                ->where('is_active', true)
                ->with(['subject', 'questions', 'attempts' => function($query) {
                    $query->where('user_id', auth()->id());
                }]);
    }
    
    public function availableTestsPage(Request $request)
    {
        $query = $this->availableTests();
        
        // Filter by subject if provided
        if ($request->has('subject') && !empty($request->subject)) {
            $query->where('subject_id', $request->subject);
        }
        
        $tests = $query->paginate(10);
        $subjects = Subject::has('mcqTests')->get();
        
        return view('student.test.available', compact('tests', 'subjects'));
    }

    public function attempt(MCQTest $mcq_test)
    {
        try {
                if (!$mcq_test) {
                Log::error('Test not found', [
                    'test_id' => $mcq_test->id ?? null,
                    'user_id' => auth()->id()
                ]);
                return redirect()->route('student.dashboard')->with('error', 'Test not found.');
            }

            if (!$mcq_test->canBeTaken()) {
                Log::warning('User attempted to access unavailable test', [
                    'test_id' => $mcq_test->id,
                    'user_id' => auth()->id(),
                    'is_active' => $mcq_test->is_active,
                    'start_time' => $mcq_test->start_time,
                    'end_time' => $mcq_test->end_time,
                    'now' => now()
                ]);
                return redirect()->route('student.dashboard')->with('error', 'This test is not currently available for attempt.');
            }
            
            // Check if user has reached maximum attempts
            $attemptsCount = $mcq_test->getAttemptsCountByUser(auth()->user());
            $maxAttempts = 10; // Maximum allowed attempts
            
            if ($attemptsCount >= $maxAttempts) {
                Log::warning('User attempted to exceed maximum attempts for test', [
                    'test_id' => $mcq_test->id,
                    'user_id' => auth()->id(),
                    'attempts_count' => $attemptsCount,
                    'max_attempts' => $maxAttempts
                ]);
                return redirect()->route('student.dashboard')->with('error', "You have reached the maximum allowed attempts ({$maxAttempts}) for this test.");
            }

            // Check if the user has an incomplete attempt
            $incompleteAttempt = $mcq_test->attempts()
                ->where('user_id', auth()->id())
                ->whereNull('completed_at')
                ->first();
                
            if ($incompleteAttempt) {
                Log::info('User has an incomplete attempt, resuming', [
                    'test_id' => $mcq_test->id,
                    'user_id' => auth()->id(),
                    'attempt_id' => $incompleteAttempt->id
                ]);
                
                // Check if the attempt has expired
                if ($incompleteAttempt->isExpired()) {
                    $incompleteAttempt->update([
                        'completed_at' => now(),
                        'score' => 0 // Failed due to timeout
                    ]);
                    
                    Log::info('Marking expired attempt as completed', [
                        'attempt_id' => $incompleteAttempt->id
                    ]);
                    
                    // Continue to create a new attempt
                } else {
                    // Resume the existing attempt
                    return view('student.test.attempt', [
                        'mcq_test' => $mcq_test, 
                        'attempt' => $incompleteAttempt,
                        'is_resumed' => true
                    ]);
                }
            }

            if ($mcq_test->questions->count() == 0) {
                Log::warning('User attempted to access test with no questions', [
                    'test_id' => $mcq_test->id, 
                    'user_id' => auth()->id()
                ]);
                return redirect()->route('student.dashboard')->with('error', 'This test does not have any questions yet.');
        }

        $attempt = $mcq_test->attempts()->create([
            'user_id' => auth()->id(),
            'started_at' => now(),
            'completed_at' => null,
            'score' => 0
        ]);

            Log::info('User started test attempt', [
                'test_id' => $mcq_test->id,
                'user_id' => auth()->id(),
                'attempt_id' => $attempt->id,
                'attempt_number' => $attemptsCount + 1,
                'max_attempts' => $maxAttempts
        ]);

        return view('student.test.attempt', compact('mcq_test', 'attempt'));
        } catch (Exception $e) {
            Log::error('Error during test attempt', [
                'test_id' => $mcq_test->id ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('student.dashboard')->with('error', 'An error occurred. Please try again later.');
        }
    }

    public function submit(Request $request, MCQTest $mcq_test)
    {
        // Add debug logging
        Log::info('Test submission initiated', [
            'test_id' => $mcq_test->id,
            'user_id' => auth()->id(),
            'answers' => $request->has('answers') ? count($request->answers) : 0,
            'raw_data' => $request->all()
        ]);

        try {
            DB::beginTransaction();
            
            $attempt = $mcq_test->attempts()
                           ->where('user_id', auth()->id())
                           ->latest()
                           ->firstOrFail();

            if ($attempt->isExpired()) {
                DB::rollBack();
                return back()->with('error', 'Test time has expired.');
            }

            // Process answers
            $answeredQuestions = 0;
            $correctAnswers = 0;
            $totalQuestions = $mcq_test->questions->count();

            if ($request->has('answers')) {
                foreach ($request->answers as $questionId => $answer) {
                    $question = Question::findOrFail($questionId);
                    
                    // Determine selected options and format correctly
                    $selectedOptions = null;
                    if (isset($answer['selected_option']) && !empty($answer['selected_option'])) {
                        // Format selected options as array
                        $selectedOptions = is_array($answer['selected_option']) 
                            ? $answer['selected_option'] 
                            : [$answer['selected_option']];
                        
                        $answeredQuestions++;
                    }
                    
                    // Check if there's an existing response
                    $existingResponse = $attempt->responses()
                        ->where('question_id', $questionId)
                        ->first();
                    
                    $isMarkedForReview = isset($answer['is_marked_for_review']) && $answer['is_marked_for_review'] === 'true';
                    
                    if ($existingResponse) {
                        // Update existing response
                        $existingResponse->update([
                            'selected_option' => $selectedOptions ? json_encode($selectedOptions) : null,
                            'is_marked_for_review' => $isMarkedForReview
                        ]);
                        
                        // is_correct will be calculated by the model's boot method
                        
                        if ($existingResponse->is_correct) {
                            $correctAnswers++;
                        }
                    } else {
                        // Create new response
                        $response = StudentResponse::create([
                            'test_attempt_id' => $attempt->id,
                            'question_id' => $questionId,
                            'selected_option' => $selectedOptions ? json_encode($selectedOptions) : null,
                            'is_marked_for_review' => $isMarkedForReview
                        ]);
                        
                        if ($response->is_correct) {
                            $correctAnswers++;
                        }
                    }
                }
            }

            // Calculate score
            $score = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;
            
            // Update attempt
            $attempt->update([
                'completed_at' => now(),
                'score' => $score
            ]);
            
            DB::commit();
            
            Log::info('Test submission completed successfully', [
                'attempt_id' => $attempt->id,
                'questions_answered' => $answeredQuestions,
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctAnswers,
                'score' => $score
            ]);
            
            return redirect()
                ->route('student.results.show', $attempt->id)
                ->with('success', 'Test submitted successfully!');
                
        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Error submitting test', [
                'test_id' => $mcq_test->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'An error occurred while submitting your test. Please try again.');
        }
    }

    public function importQuestions(Request $request, MCQTest $mcqTest)
    {
        $request->validate([
            'questions_text' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            $questions = $this->questionParser->parseFromText($request->questions_text);
            $importedQuestions = [];

            foreach ($questions as $questionData) {
                $question = $mcqTest->questions()->create([
                    'question_text' => $questionData['question_text'],
                    'options' => json_encode($questionData['options']),
                    'correct_option' => json_encode([]),
                    'explanation' => '',
                    'marks' => 1  // Default marks
                ]);
                
                $importedQuestions[] = $question->id;
            }

            DB::commit();
            
            // Store imported question IDs in session for the next page
            session(['imported_questions' => $importedQuestions]);
            
            return redirect()->route('mcq-tests.questions.mark-correct', $mcqTest)
                ->with('success', count($questions) . ' questions imported successfully. Please mark the correct answers below.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to import questions: ' . $e->getMessage());
            return back()->with('error', 'Failed to import questions. Please check the format and try again.');
        }
    }
    
    public function showMarkCorrectForm(MCQTest $mcqTest)
    {
        $importedQuestionIds = session('imported_questions', []);
        
        if (empty($importedQuestionIds)) {
            return redirect()->route('mcq-tests.show', $mcqTest)
                ->with('info', 'No recently imported questions to mark.');
        }
        
        $questions = Question::whereIn('id', $importedQuestionIds)->get();
        
        return view('teacher.mcq-tests.mark-correct', compact('mcqTest', 'questions'));
    }
    
    public function updateCorrectOptions(Request $request, MCQTest $mcqTest)
    {
        $validated = $request->validate([
            'questions' => 'required|array',
            'questions.*' => 'required|array',
            'questions.*.id' => 'required|exists:questions,id',
            'questions.*.correct_option' => 'required|array'
        ]);
        
        try {
            DB::beginTransaction();
            
            foreach ($validated['questions'] as $questionData) {
                $question = Question::findOrFail($questionData['id']);
                
                // Verify ownership
                if ($question->mcqTest->teacher_id !== auth()->user()->teacher->id) {
                    throw new \Exception('Unauthorized attempt to update question');
                }
                
                // Get options as array
                $options = is_string($question->options) ? 
                    json_decode($question->options, true) : 
                    $question->options;
                
                // Process correct options
                $correctOptions = [];
                foreach ($questionData['correct_option'] as $index) {
                    if (isset($options[$index])) {
                        // For associative arrays like {'a': 'Option A'}
                        if (is_string($index) && !is_numeric($index)) {
                            $correctOptions[] = $options[$index];
                        } 
                        // For sequential arrays
                        else {
                            $correctOptions[] = $options[$index];
                        }
                    }
                }
                
                $question->update([
                    'correct_option' => $correctOptions
                ]);
            }
            
            DB::commit();
            
            // Clear the imported questions session
            session()->forget('imported_questions');
            
            return redirect()->route('mcq-tests.show', $mcqTest)
                ->with('success', 'Correct answers saved successfully for all imported questions.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update correct options: ' . $e->getMessage());
            return back()->with('error', 'Failed to update correct options. Please try again.');
        }
    }

    public function storeQuestions(Request $request, MCQTest $mcqTest)
    {
        $request->validate([
            'questions' => 'required|array',
            'questions.*.question_text' => 'required|string',
            'questions.*.options' => 'required|array',
            'questions.*.options.*' => 'required|string',
            'questions.*.correct_option' => 'required|string|in:a,b,c,d',
            'questions.*.explanation' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->questions as $questionData) {
                $mcqTest->questions()->create([
                    'question_text' => $questionData['question_text'],
                    'options' => json_encode($questionData['options']),
                    'correct_option' => $questionData['correct_option'],
                    'explanation' => $questionData['explanation'] ?? ''
                ]);
            }

            DB::commit();

            return back()->with('success', 'Questions added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to add questions: ' . $e->getMessage());
            return back()->with('error', 'Failed to add questions. Please try again.');
        }
    }

    public function getResults($id)
    {
        try {
            $test = MCQTest::with(['subject', 'questions'])->findOrFail($id);
            
            // Check if the authenticated user is the owner of the test
            if (auth()->user()->teacher->id !== $test->user_id) {
                $this->logWarning('Unauthorized access attempt to test results', [
                    'teacher_id' => auth()->user()->teacher->id,
                    'test_id' => $id,
                    'test_owner_id' => $test->user_id
                ]);
                
                return redirect()->route('teacher.dashboard')
                    ->with('error', 'You can only view results for your own tests.');
            }
            
            // Get all attempts for this test with related data
            $attempts = TestAttempt::with(['user', 'responses'])
                ->where('mcq_test_id', $id)
            ->get();
        
            // Group attempts by user
            $userDataCollection = $attempts->groupBy('user_id');
            
            // Transform the data
            $userData = [];
            $totalStudents = 0;
            $sumBestScores = 0;
            
            foreach ($userDataCollection as $userId => $userAttempts) {
                $totalStudents++;
                $user = $userAttempts->first()->user;
                $bestScore = 0;
                $attemptRecords = [];
                
                foreach ($userAttempts as $attempt) {
                    // Calculate score and other metrics
                    $totalQuestions = $test->questions->count();
            $correctAnswers = $attempt->responses->where('is_correct', true)->count();
            $incorrectAnswers = $attempt->responses->where('is_correct', false)->count();
                    $unansweredQuestions = $totalQuestions - $correctAnswers - $incorrectAnswers;
                    
                    $scorePercentage = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 1) : 0;
                    $isPassed = $scorePercentage >= $test->passing_percentage;
                    
                    // Update best score if this attempt is better
                    if ($scorePercentage > $bestScore) {
                        $bestScore = $scorePercentage;
                    }
                    
                    // Calculate time taken
                    $timeTaken = null;
                    if ($attempt->completed_at) {
                        $timeTaken = ceil($attempt->completed_at->diffInSeconds($attempt->started_at) / 60);
                    }
                    
                    // Add attempt data
                    $attemptRecords[] = [
                'id' => $attempt->id,
                        'created_at' => $attempt->created_at,
                        'score' => $scorePercentage,
                'correct_answers' => $correctAnswers,
                'incorrect_answers' => $incorrectAnswers,
                        'unanswered' => $unansweredQuestions,
                        'time_taken' => $timeTaken,
                        'passed' => $isPassed
                    ];
                }
                
                // Sort attempts by date
                usort($attemptRecords, function($a, $b) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                });
                
                // Add user data with attempts
                $userData[] = [
                    'user_id' => $userId,
                    'student_name' => $user->name,
                    'best_score' => $bestScore,
                    'attempts_count' => count($attemptRecords),
                    'attempts' => $attemptRecords,
                    'passed' => $bestScore >= $test->passing_percentage
                ];
                
                $sumBestScores += $bestScore;
            }
            
            // Sort users by best score descending
            usort($userData, function($a, $b) {
                return $b['best_score'] - $a['best_score'];
            });
            
            // Calculate average best score
            $averageBestScore = $totalStudents > 0 ? round($sumBestScores / $totalStudents, 1) : 0;
            
            $this->logInfo('Teacher viewed test results', [
                'teacher_id' => auth()->user()->teacher->id,
                'test_id' => $id,
                'test_title' => $test->title
            ]);
            
            return view('teacher.mcq-tests.results', compact('test', 'userData', 'totalStudents', 'averageBestScore'));
        } catch (\Exception $e) {
            $this->logError('Error retrieving test results', [
                'test_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('teacher.dashboard')
                ->with('error', 'An error occurred while retrieving test results. Please try again.');
        }
    }

    /**
     * Display the detailed results for a specific test attempt.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function showTestResult($id)
    {
        try {
            $testAttempt = TestAttempt::with(['user', 'mcqTest', 'responses.question.options'])
                ->findOrFail($id);
            
            // Only the teacher who created the test can view results
            if (auth()->user()->role === 'teacher' && auth()->id() !== $testAttempt->mcqTest->teacher_id) {
                Log::warning('Unauthorized attempt to view test results', [
                    'teacher_id' => auth()->id(),
                    'test_owner_id' => $testAttempt->mcqTest->teacher_id,
                    'test_attempt_id' => $id
                ]);
                return redirect()->route('teacher.dashboard')->with('error', 'You can only view results for your own tests.');
            }
            
            // Calculate time taken in minutes
            $timeTaken = 'N/A';
            if ($testAttempt->completed_at && $testAttempt->started_at) {
                $timeTaken = ceil(
                    $testAttempt->completed_at->diffInSeconds($testAttempt->started_at) / 60
                );
            }
            
            // Get total questions for the test
            $totalQuestions = $testAttempt->mcqTest->questions()->count();
            
            // Prepare responses for display
            $responses = [];
            $correctAnswers = 0;
            $incorrectAnswers = 0;
            $unansweredQuestions = 0;
            
            foreach ($testAttempt->responses as $response) {
                $questionData = $response->question;
                $selectedOption = null;
                $status = 'unanswered';
                
                if ($response->selected_option_id) {
                    $selectedOption = $questionData->options->firstWhere('id', $response->selected_option_id);
                    $status = $response->is_correct ? 'correct' : 'incorrect';
                    
                    if ($response->is_correct) {
                        $correctAnswers++;
                    } else {
                        $incorrectAnswers++;
                    }
                } else {
                    $unansweredQuestions++;
                }
                
                $options = $questionData->options->map(function($option) {
                    return [
                        'id' => $option->id,
                        'option_text' => $option->option_text,
                        'is_correct' => $option->is_correct
                    ];
                })->toArray();
                
                $responses[] = [
                    'id' => $response->id,
                    'question_id' => $questionData->id,
                    'question_text' => $questionData->question_text,
                    'question_image' => $questionData->image_path,
                    'selected_option_id' => $response->selected_option_id,
                    'is_correct' => $response->is_correct,
                    'options' => $options,
                    'explanation' => $questionData->explanation,
                    'status' => $status
                ];
            }
            
            // Add missing questions as unanswered
            $answeredQuestionIds = collect($responses)->pluck('question_id')->toArray();
            $unansweredQuestions = $testAttempt->mcqTest->questions()
                ->whereNotIn('id', $answeredQuestionIds)
                ->get();
                
            foreach ($unansweredQuestions as $question) {
                $responses[] = [
                    'id' => null,
                    'question_id' => $question->id,
                    'question_text' => $question->question_text,
                    'question_image' => $question->image_path,
                    'selected_option_id' => null,
                    'is_correct' => false,
                    'options' => $question->options->map(function($option) {
                        return [
                            'id' => $option->id,
                            'option_text' => $option->option_text,
                            'is_correct' => $option->is_correct
                        ];
                    })->toArray(),
                    'explanation' => $question->explanation,
                    'status' => 'unanswered'
                ];
            }
            
            // Calculate percentages for progress bars
            $totalQuestions = max(count($responses), 1); // Avoid division by zero
            $correctPercentage = round(($correctAnswers / $totalQuestions) * 100, 1);
            $incorrectPercentage = round(($incorrectAnswers / $totalQuestions) * 100, 1);
            $unansweredPercentage = round(($unansweredQuestions / $totalQuestions) * 100, 1);
            
            return view('teacher.mcq-tests.result-details', compact(
                'testAttempt',
                'responses',
                'timeTaken',
                'totalQuestions',
                'correctAnswers',
                'incorrectAnswers',
                'unansweredQuestions',
                'correctPercentage',
                'incorrectPercentage',
                'unansweredPercentage'
            ));
        } catch (\Exception $e) {
            Log::error('Error displaying test result details', [
                'test_attempt_id' => $id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Unable to view result details. Please try again.');
        }
    }

    /**
     * Store teacher feedback for a test attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeFeedback(Request $request, $id)
    {
        try {
            // Implement feedback storage logic here
            
            return redirect()->back()->with('success', 'Feedback saved successfully.');
        } catch (\Exception $e) {
            Log::error('Error saving feedback', [
                'test_attempt_id' => $id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Unable to save feedback. Please try again.');
        }
    }

    /**
     * Update the review status for a question.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateReviewStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'attempt_id' => 'required|exists:test_attempts,id',
                'question_id' => 'required|exists:questions,id',
                'is_marked_for_review' => 'required|boolean'
            ]);

            // Find or create the response record
            $response = \App\Models\StudentResponse::updateOrCreate(
                [
                    'test_attempt_id' => $validated['attempt_id'],
                    'question_id' => $validated['question_id']
                ],
                [
                    'is_marked_for_review' => $validated['is_marked_for_review']
                ]
            );

            Log::info('Question review status updated', [
                'attempt_id' => $validated['attempt_id'],
                'question_id' => $validated['question_id'],
                'is_marked_for_review' => $validated['is_marked_for_review'],
                'user_id' => auth()->id()
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error updating question review status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'user_id' => auth()->id()
            ]);
            
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Dashboard for teachers
     */
    public function dashboard()
    {
        try {
            // Get current teacher id
            $teacherId = auth()->user()->teacher->id ?? null;
            
            if (!$teacherId) {
                Log::error('Teacher ID not found for user', [
                    'user_id' => auth()->id()
                ]);
                return view('teacher.dashboard', [
                    'tests' => collect([]),
                    'testsCount' => 0,
                    'activeTestsCount' => 0,
                    'questionsCount' => 0,
                    'recentResults' => collect([]),
                    'error' => 'Teacher profile not found. Please contact administrator.'
                ]);
            }
            
            // Use proper teacher_id column
            $tests = MCQTest::where('teacher_id', $teacherId)
                ->with(['subject', 'questions'])
                ->latest()
                ->paginate(5, ['*'], 'tests_page'); // Paginate with 5 tests per page
            
            $testsCount = MCQTest::where('teacher_id', $teacherId)->count();
            $activeTestsCount = MCQTest::where('teacher_id', $teacherId)->where('is_active', true)->count();
            $questionsCount = Question::whereHas('mcqTest', function ($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
            })->count();
            
            $recentResults = TestAttempt::whereHas('mcqTest', function ($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
            })->with(['user', 'mcqTest'])
              ->latest()
              ->paginate(5, ['*'], 'results_page'); // Paginate with 5 results per page
            
            return view('teacher.dashboard', compact(
                'tests', 
                'testsCount', 
                'activeTestsCount', 
                'questionsCount', 
                'recentResults'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading teacher dashboard', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('errors.custom', [
                'errorTitle' => 'Error Loading Dashboard',
                'errorMessage' => 'We encountered an issue loading your dashboard. Our team has been notified.'
            ], 500);
        }
    }
}