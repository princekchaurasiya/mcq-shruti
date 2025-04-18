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

        // Load necessary relationships
        $result->load(['mcqTest.subject', 'responses.question']);
        
        // Manually get questions to avoid potential relationship issues
        $questions = Question::where('mcq_test_id', $result->mcq_test_id)
            ->get();
        
        $result->mcqTest->setRelation('questions', $questions);
        
        // Set answers for the view to avoid null error
        $answers = $result->responses;
        
        return view('student.results.show', compact('result', 'answers'));
    }
} 