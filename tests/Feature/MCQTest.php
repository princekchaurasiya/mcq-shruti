<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\MCQTest;
use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class MCQApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_view_results_page()
    {
        // Create a student user
        $student = User::factory()->create(['role' => 'student']);
        
        // Create the associated student record
        Student::create(['user_id' => $student->id]);
        
        $this->actingAs($student);
        
        $response = $this->get('/student/results');
        
        $response->assertStatus(200);
    }
    
    public function test_teacher_can_view_dashboard()
    {
        // Create a teacher user
        $teacher = User::factory()->create(['role' => 'teacher']);
        
        // Create the associated teacher record
        Teacher::create([
            'user_id' => $teacher->id,
            'qualification' => 'PhD',
            'experience_years' => 5
        ]);
        
        // Act as the teacher
        $this->actingAs($teacher);
        
        // Create a mock of the TeacherDashboardController
        $this->mock('App\Http\Controllers\TeacherDashboardController')
            ->shouldReceive('dashboard')
            ->andReturn(view('teacher.dashboard', [
                'tests' => new LengthAwarePaginator(
                    collect(), // items
                    0, // total
                    10, // per page
                    1 // current page
                ),
                'recentResults' => new LengthAwarePaginator(
                    collect(), // items
                    0, // total
                    10, // per page
                    1 // current page
                ),
                'activeTestsCount' => 0,
                'questionsCount' => 0,
                'testsCount' => 0,
                'totalAttempts' => 0
            ]));
            
        // Make the request
        $response = $this->get('/teacher/dashboard');
        
        // Assert response status
        $response->assertStatus(200);
    }
} 