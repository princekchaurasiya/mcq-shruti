<?php

namespace Tests\Feature\MCQ;

use Tests\TestCase;
use App\Models\User;
use App\Models\McqTest;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TestLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;
    private Teacher $teacher;
    private User $studentUser;
    private Student $student;
    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a teacher
        $this->teacherUser = User::factory()->create([
            'role' => 'teacher'
        ]);

        $this->teacher = Teacher::create([
            'user_id' => $this->teacherUser->id,
            'qualification' => 'PhD',
            'experience_years' => 5,
            'subject_id' => null
        ]);

        // Create a student
        $this->studentUser = User::factory()->create([
            'role' => 'student'
        ]);

        $this->student = Student::create([
            'user_id' => $this->studentUser->id,
            'education_level' => 'Undergraduate',
            'year' => 2
        ]);

        // Create a subject
        $this->subject = Subject::create([
            'name' => 'Integration Testing',
            'description' => 'Testing the integration of components'
        ]);
    }

    /**
     * Test the entire lifecycle of an MCQ test, from creation through updates
     * to the is_active field, and checking student visibility based on status.
     */
    public function test_mcq_test_lifecycle_with_active_status_changes()
    {
        // STEP 1: Teacher creates a test that is initially active
        $this->actingAs($this->teacherUser);
        
        $testData = [
            'title' => 'Comprehensive MCQ Test',
            'description' => 'Testing the full lifecycle with active status changes',
            'subject_id' => $this->subject->id,
            'duration_minutes' => 30,
            'start_time' => now()->addHour()->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'passing_percentage' => 60,
            'is_active' => true,
            'teacher_id' => $this->teacher->id
        ];

        $response = $this->post('/teacher/mcq-tests', $testData);
        $response->assertRedirect();
        
        // Verify the test was created with is_active true
        $test = McqTest::latest()->first();
        $this->assertNotNull($test);
        $this->assertTrue($test->is_active);
        $this->assertEquals('Comprehensive MCQ Test', $test->title);
        
        // STEP 2: Student should see the active test in available tests
        $this->actingAs($this->studentUser);
        
        // Use the correct route for available tests
        $response = $this->get('/student/available-tests');
        $response->assertSee('Comprehensive MCQ Test');
        
        // STEP 3: Teacher updates the test to inactive
        $this->actingAs($this->teacherUser);
        
        $updateData = [
            'title' => 'Comprehensive MCQ Test',
            'description' => 'Test updated to inactive',
            'subject_id' => $this->subject->id,
            'duration_minutes' => 30,
            'start_time' => now()->addHour()->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'passing_percentage' => 60,
            'is_active' => false  // Setting to inactive
        ];
        
        $response = $this->put('/teacher/mcq-tests/' . $test->id, $updateData);
        $response->assertRedirect();
        
        // Verify the test was updated to inactive
        $test->refresh();
        $this->assertFalse($test->is_active);
        
        // STEP 4: Student should NOT see the inactive test
        $this->actingAs($this->studentUser);
        
        // Use the correct route for available tests
        $response = $this->get('/student/available-tests');
        $response->assertDontSee('Comprehensive MCQ Test');
        
        // STEP 5: Teacher updates it to active again, but this time using a request without is_active
        // This simulates adding is_active checkbox back after it was removed
        $this->actingAs($this->teacherUser);
        
        $updateData2 = [
            'title' => 'Comprehensive MCQ Test',
            'description' => 'Test updated to active again',
            'subject_id' => $this->subject->id,
            'duration_minutes' => 30,
            'start_time' => now()->addHour()->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'passing_percentage' => 60,
            'is_active' => true  // Setting back to active
        ];
        
        $response = $this->put('/teacher/mcq-tests/' . $test->id, $updateData2);
        $response->assertRedirect();
        
        // Verify the test was updated to active again
        $test->refresh();
        $this->assertTrue($test->is_active);
        
        // STEP 6: Student should see the active test again
        $this->actingAs($this->studentUser);
        
        // Use the correct route for available tests
        $response = $this->get('/student/available-tests');
        $response->assertSee('Comprehensive MCQ Test');
        
        // STEP 7: Test direct database access to boolean value to verify type integrity
        $dbValue = DB::table('mcq_tests')->where('id', $test->id)->value('is_active');
        $this->assertSame(1, $dbValue);
        
        // And API style update with explicit false
        $this->actingAs($this->teacherUser);
        $updateData3 = [
            'title' => 'Comprehensive MCQ Test',
            'description' => 'API style update with explicit false',
            'subject_id' => $this->subject->id,
            'duration_minutes' => 30,
            'start_time' => now()->addHour()->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'passing_percentage' => 60,
            'is_active' => false  // Setting to inactive with explicit false
        ];
        
        $response = $this->put('/teacher/mcq-tests/' . $test->id, $updateData3);
        $response->assertRedirect();
        
        // Verify the test was updated to inactive
        $test->refresh();
        $this->assertFalse($test->is_active);
        
        $dbValue = DB::table('mcq_tests')->where('id', $test->id)->value('is_active');
        $this->assertSame(0, $dbValue);
    }
} 