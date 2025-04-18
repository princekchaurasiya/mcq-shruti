<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\MCQTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Traits\LoggableTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class QuestionController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, LoggableTrait;

    public function __construct()
    {
        // No middleware here - it's handled in routes
    }

    public function index()
    {
        try {
            $questions = Question::whereHas('mcqTest', function($query) {
                $query->where('teacher_id', Auth::user()->teacher->id);
            })
            ->with(['mcqTest'])
            ->latest()
            ->paginate(10);

            $this->logInfo('Teacher viewed questions list', [
                'count' => $questions->count()
            ]);

            return view('teacher.questions.index', compact('questions'));
        } catch (\Exception $e) {
            $this->logError('Error retrieving questions list', [
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Unable to retrieve questions. Please try again.');
        }
    }

    public function create()
    {
        try {
            $tests = MCQTest::where('teacher_id', Auth::user()->teacher->id)->get();
            
            $this->logInfo('Teacher accessed question creation form', [
                'available_tests' => $tests->count()
            ]);

            return view('teacher.questions.create', compact('tests'));
        } catch (\Exception $e) {
            $this->logError('Error accessing question creation form', [
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Unable to access question creation form. Please try again.');
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'mcq_test_id' => 'required|exists:mcq_tests,id',
                'question_text' => 'required|string',
                'options' => 'required|array|min:2',
                'options.*' => 'required|string|distinct',
                'correct_option' => 'required|array|min:1',
                'correct_option.*' => 'required|integer|distinct|min:0',
                'marks' => 'required|integer|min:1',
                'explanation' => 'nullable|string',
                'image' => 'nullable|image|max:2048'
            ]);

            // Verify the test belongs to the teacher
            $test = MCQTest::findOrFail($validated['mcq_test_id']);
            if ($test->teacher_id !== Auth::user()->teacher->id) {
                $this->logWarning('Unauthorized attempt to add question to test', [
                    'test_id' => $test->id,
                    'attempted_by' => Auth::user()->teacher->id
                ]);
                abort(403, 'Unauthorized action. You can only add questions to your own tests.');
            }

            // Process options and correct answers
            $options = $validated['options'];
            $correctOptions = collect($validated['correct_option'])
                ->map(function($index) use ($options) {
                    return $options[$index] ?? null;
                })
                ->filter()
                ->values()
                ->all();

            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('question-images', 'public');
            }

            $question = Question::create([
                'mcq_test_id' => $validated['mcq_test_id'],
                'question_text' => $validated['question_text'],
                'options' => $options,
                'correct_option' => $correctOptions,
                'explanation' => $validated['explanation'],
                'marks' => $validated['marks'],
                'image_path' => $imagePath
            ]);

            $this->logInfo('Question created successfully', [
                'question_id' => $question->id,
                'test_id' => $test->id,
                'has_image' => !is_null($imagePath)
            ]);

            return redirect()->route('mcq-tests.show', $test->id)
                ->with('success', 'Question created successfully.');
        } catch (\Exception $e) {
            $this->logError('Error creating question', [
                'error' => $e->getMessage(),
                'test_id' => $request->mcq_test_id ?? null
            ]);
            return back()->with('error', 'Unable to create question. Please try again.')
                ->withInput();
        }
    }

    public function show(Question $question)
    {
        try {
            $this->authorize('view', $question);
            
            $this->logInfo('Question details viewed', [
                'question_id' => $question->id,
                'test_id' => $question->mcq_test_id
            ]);

            return redirect()->route('mcq-tests.show', $question->mcq_test_id)
                ->with('success', 'Question details viewed successfully.');
        } catch (\Exception $e) {
            $this->logError('Error viewing question details', [
                'error' => $e->getMessage(),
                'question_id' => $question->id
            ]);
            return back()->with('error', 'Unable to view question details. Please try again.');
        }
    }

    public function edit(Question $question)
    {
        try {
            $this->authorize('update', $question);
            $tests = MCQTest::where('teacher_id', Auth::user()->teacher->id)->get();
            
            // Handle JSON-encoded options if needed
            if (is_string($question->options)) {
                $question->options = json_decode($question->options, true);
            }
            
            // If options is an associative array (like {"a":"Option A"}), convert to indexed array
            if (is_array($question->options) && array_keys($question->options) !== range(0, count($question->options) - 1)) {
                $question->options = array_values($question->options);
            }
            
            // Handle JSON-encoded correct_option if needed
            if (is_string($question->correct_option)) {
                $question->correct_option = json_decode($question->correct_option, true);
            }

            $this->logInfo('Question edit form accessed', [
                'question_id' => $question->id,
                'test_id' => $question->mcq_test_id
            ]);

            return view('teacher.questions.edit', compact('question', 'tests'));
        } catch (\Exception $e) {
            $this->logError('Error accessing question edit form', [
                'error' => $e->getMessage(),
                'question_id' => $question->id
            ]);
            return back()->with('error', 'Unable to access question edit form. Please try again.');
        }
    }

    public function update(Request $request, Question $question)
    {
        try {
            $this->authorize('update', $question);

            $validated = $request->validate([
                'mcq_test_id' => 'required|exists:mcq_tests,id',
                'question_text' => 'required|string',
                'options' => 'required|array|min:2',
                'options.*' => 'required|string|distinct',
                'correct_option' => 'required|array|min:1',
                'correct_option.*' => 'required|integer|distinct|min:0',
                'marks' => 'required|integer|min:1',
                'explanation' => 'nullable|string',
                'image' => 'nullable|image|max:2048'
            ]);

            // Verify the test belongs to the teacher
            $test = MCQTest::findOrFail($validated['mcq_test_id']);
            if ($test->teacher_id !== Auth::user()->teacher->id) {
                $this->logWarning('Unauthorized attempt to update question test', [
                    'question_id' => $question->id,
                    'attempted_test_id' => $test->id,
                    'attempted_by' => Auth::user()->teacher->id
                ]);
                abort(403, 'Unauthorized action. You can only move questions to your own tests.');
            }

            // Process options and correct answers
            $options = $validated['options'];
            $correctOptionIndices = $validated['correct_option'];
            $correctOptions = [];
            
            foreach ($correctOptionIndices as $index) {
                if (isset($options[$index])) {
                    $correctOptions[] = $options[$index];
                }
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($question->image_path) {
                    Storage::disk('public')->delete($question->image_path);
                }
                $imagePath = $request->file('image')->store('question-images', 'public');
                $question->image_path = $imagePath;
            }

            $question->update([
                'mcq_test_id' => $validated['mcq_test_id'],
                'question_text' => $validated['question_text'],
                'options' => $options,
                'correct_option' => $correctOptions,
                'explanation' => $validated['explanation'],
                'marks' => $validated['marks']
            ]);

            $this->logInfo('Question updated successfully', [
                'question_id' => $question->id,
                'test_id' => $test->id,
                'image_updated' => $request->hasFile('image')
            ]);

            return redirect()->route('mcq-tests.show', $test)
                ->with('success', 'Question updated successfully.');
        } catch (\Exception $e) {
            $this->logError('Error updating question', [
                'error' => $e->getMessage(),
                'question_id' => $question->id
            ]);
            return back()->with('error', 'Unable to update question. Please try again.')
                ->withInput();
        }
    }

    public function destroy(Question $question)
    {
        try {
            $this->authorize('delete', $question);
            
            $questionId = $question->id;
            $testId = $question->mcq_test_id;
            $mcqTest = $question->mcqTest;

            // Delete the image if it exists
            if ($question->image_path) {
                Storage::disk('public')->delete($question->image_path);
            }

            $question->delete();

            $this->logInfo('Question deleted successfully', [
                'question_id' => $questionId,
                'test_id' => $testId
            ]);

            return redirect()->route('mcq-tests.show', $mcqTest)
                ->with('success', 'Question deleted successfully.');
        } catch (\Exception $e) {
            $this->logError('Error deleting question', [
                'error' => $e->getMessage(),
                'question_id' => $question->id
            ]);
            return back()->with('error', 'Unable to delete question. Please try again.');
        }
    }
} 