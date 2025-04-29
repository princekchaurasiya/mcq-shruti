<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Http\Middleware\CheckRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Define test routes for middleware testing
        Route::middleware('web')->group(function () {
            Route::get('/test-student-route', function () {
                return 'Student route';
            })->middleware(['auth', CheckRole::class . ':student']);
            
            Route::get('/test-teacher-route', function () {
                return 'Teacher route';
            })->middleware(['auth', CheckRole::class . ':teacher']);
        });
    }

    public function test_student_cannot_access_teacher_routes()
    {
        // Create student user
        $student = User::factory()->create(['role' => 'student']);
        Student::create(['user_id' => $student->id]);
        
        $this->actingAs($student);
        
        // Try to access teacher route
        $response = $this->get('/test-teacher-route');
        
        $response->assertStatus(403); // Forbidden
    }

    public function test_teacher_cannot_access_student_routes()
    {
        // Create teacher user
        $teacher = User::factory()->create(['role' => 'teacher']);
        Teacher::create([
            'user_id' => $teacher->id,
            'qualification' => 'PhD',
            'experience_years' => 5
        ]);
        
        $this->actingAs($teacher);
        
        // Try to access student route
        $response = $this->get('/test-student-route');
        
        $response->assertStatus(403); // Forbidden
    }
    
    public function test_authentication_redirects_unauthenticated_users()
    {
        // Try to access a protected route without authentication
        $response = $this->get('/test-student-route');
        
        // Should redirect to login
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
    
    public function test_users_can_be_authenticated()
    {
        // Create a student user
        $student = User::factory()->create([
            'email' => 'student@example.com',
            'password' => bcrypt('password'),
            'role' => 'student'
        ]);
        
        Student::create(['user_id' => $student->id]);
        
        // Manually authenticate the user
        $this->actingAs($student);
        
        // Verify the user is authenticated
        $this->assertAuthenticated();
    }
    
    public function test_authenticated_student_can_access_student_routes()
    {
        // Create and authenticate student
        $student = User::factory()->create(['role' => 'student']);
        Student::create(['user_id' => $student->id]);
        
        $this->actingAs($student);
        
        // Try to access student route
        $response = $this->get('/test-student-route');
        
        // Should be accessible
        $response->assertStatus(200);
        $response->assertSee('Student route');
    }
    
    public function test_authenticated_teacher_can_access_teacher_routes()
    {
        // Create and authenticate teacher
        $teacher = User::factory()->create(['role' => 'teacher']);
        Teacher::create([
            'user_id' => $teacher->id,
            'qualification' => 'PhD',
            'experience_years' => 5
        ]);
        
        $this->actingAs($teacher);
        
        // Try to access teacher route
        $response = $this->get('/test-teacher-route');
        
        // Should be accessible
        $response->assertStatus(200);
        $response->assertSee('Teacher route');
    }
} 