<?php

namespace App\Http\Controllers;

use App\Models\MCQTest;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard with paginated data.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        try {
            // Fetch teachers with pagination
            $recentTeachers = Teacher::with('user', 'subject')
                ->latest()
                ->paginate(5, ['*'], 'teachers_page');
            
            // Fetch students with pagination
            $recentStudents = Student::with('user')
                ->latest()
                ->paginate(5, ['*'], 'students_page');
            
            // Fetch tests with pagination
            $recentTests = MCQTest::with(['teacher.user', 'subject', 'questions', 'attempts'])
                ->latest()
                ->paginate(10, ['*'], 'tests_page');
            
            return view('admin.dashboard', compact(
                'recentTeachers',
                'recentStudents',
                'recentTests'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading admin dashboard', [
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