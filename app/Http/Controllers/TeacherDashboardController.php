<?php

namespace App\Http\Controllers;

use App\Models\MCQTest;
use App\Models\Question;
use App\Models\TestAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TeacherDashboardController extends Controller
{
    /**
     * Display the teacher dashboard with paginated data.
     *
     * @return \Illuminate\View\View
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