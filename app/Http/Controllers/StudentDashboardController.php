<?php

namespace App\Http\Controllers;

use App\Models\MCQTest;
use App\Models\TestAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StudentDashboardController extends Controller
{
    /**
     * Display the student dashboard with paginated data.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        try {
            // Get recent test attempts for the student
            $recentAttempts = TestAttempt::where('user_id', auth()->id())
                ->with(['mcqTest.subject', 'responses'])
                ->whereNotNull('completed_at')
                ->latest()
                ->paginate(3, ['*'], 'results_page');
            
            // Get in-progress test attempts
            $inProgressAttempts = TestAttempt::where('user_id', auth()->id())
                ->whereNull('completed_at')
                ->with('mcqTest')
                ->latest()
                ->take(2)
                ->get();
            
            // Get available tests
            $availableTests = MCQTest::where('end_time', '>', now())
                ->where('is_active', true)
                ->has('questions')
                ->with(['subject', 'attempts' => function($query) {
                    $query->where('user_id', auth()->id());
                }])
                ->latest('start_time')
                ->paginate(6, ['*'], 'tests_page');
            
            // Get stats
            $student = Auth::user()->student;
            $testAttempts = Auth::user()->testAttempts()->count();
            $avgScore = Auth::user()->testAttempts()->avg('score') ?? 0;
            $completedTests = Auth::user()->testAttempts()->whereNotNull('completed_at')->count();
            $availableCount = MCQTest::where('end_time', '>', now())
                ->where('is_active', true)
                ->has('questions')
                ->count();
            
            return view('student.dashboard', compact(
                'recentAttempts',
                'inProgressAttempts',
                'availableTests',
                'student',
                'testAttempts',
                'avgScore',
                'completedTests',
                'availableCount'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading student dashboard', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return view('errors.custom', [
                'errorTitle' => 'Error Loading Dashboard',
                'errorMessage' => 'We encountered an issue loading your dashboard. Our team has been notified.'
            ], 500);
        }
    }
} 