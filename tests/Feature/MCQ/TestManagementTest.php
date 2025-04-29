<?php

namespace Tests\Feature\MCQ;

use Tests\TestCase;
use App\Models\User;
use App\Models\McqTest;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class TestManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a teacher user
        $this->user = User::factory()->create([
            'role' => 'teacher'
        ]);

        // Create a teacher profile
        $this->teacher = Teacher::create([
            'user_id' => $this->user->id,
            'qualification' => 'PhD',
            'experience_years' => 5,
            'subject_id' => null
        ]);

        // Create a subject
        $this->subject = Subject::create([
            'name' => 'Mathematics',
            'description' => 'Mathematics subject for testing'
        ]);
    }

    public function test_teacher_can_create_test()
    {
        $this->actingAs($this->user);

        $testData = [
            'title' => 'Test Mathematics Quiz',
            'description' => 'A basic math quiz',
            'subject_id' => $this->subject->id,
            'duration_minutes' => 30,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDays(2),
            'passing_percentage' => 60,
            'is_active' => true,
            'teacher_id' => $this->teacher->id
        ];

        $response = $this->post('/teacher/mcq-tests', $testData);

        $response->assertRedirect()
                ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('mcq_tests', [
            'title' => 'Test Mathematics Quiz',
            'subject_id' => $this->subject->id,
            'duration_minutes' => 30,
            'passing_percentage' => 60,
            'is_active' => true,
            'teacher_id' => $this->teacher->id
        ]);
    }

    public function test_teacher_cannot_create_test_with_invalid_dates()
    {
        $this->actingAs($this->user);

        $testData = [
            'title' => 'Invalid Date Test',
            'description' => 'This test has invalid dates',
            'subject_id' => $this->subject->id,
            'duration_minutes' => 30,
            'start_time' => now()->addDays(3),
            'end_time' => now()->addDay(),
            'passing_percentage' => 60,
            'is_active' => true,
            'teacher_id' => $this->teacher->id
        ];

        $response = $this->post('/teacher/mcq-tests', $testData);

        $response->assertSessionHasErrors('end_time');
        
        $this->assertDatabaseMissing('mcq_tests', [
            'title' => 'Invalid Date Test'
        ]);
    }

    public function test_teacher_can_edit_test()
    {
        $this->actingAs($this->user);

        // Create a test
        $test = McqTest::create([
            'title' => 'Original Test Title',
            'description' => 'Original description',
            'subject_id' => $this->subject->id,
            'duration_minutes' => 30,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDays(2),
            'passing_percentage' => 60,
            'is_active' => true,
            'teacher_id' => $this->teacher->id
        ]);

        $updatedData = [
            'title' => 'Updated Test Title',
            'description' => 'Updated description',
            'duration_minutes' => 45,
            'passing_percentage' => 70,
            'start_time' => now()->addDays(1)->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'is_active' => false
        ];

        $response = $this->put('/teacher/mcq-tests/' . $test->id, $updatedData);

        $response->assertRedirect()
                ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('mcq_tests', [
            'id' => $test->id,
            'title' => 'Updated Test Title',
            'description' => 'Updated description',
            'duration_minutes' => 45,
            'passing_percentage' => 70,
            'is_active' => 0,
            'teacher_id' => $this->teacher->id
        ]);
    }

    public function test_teacher_can_delete_test()
    {
        $this->actingAs($this->user);

        $test = McqTest::create([
            'title' => 'Test to Delete',
            'description' => 'This test will be deleted',
            'subject_id' => $this->subject->id,
            'duration_minutes' => 30,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDays(2),
            'passing_percentage' => 60,
            'is_active' => true,
            'teacher_id' => $this->teacher->id
        ]);

        $response = $this->delete('/teacher/mcq-tests/' . $test->id);

        $response->assertRedirect()
                ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('mcq_tests', [
            'id' => $test->id
        ]);
    }

    public function test_teacher_cannot_edit_test_in_progress()
    {
        $this->actingAs($this->user);

        // Create a test that has already started
        $test = McqTest::create([
            'title' => 'Test in Progress',
            'description' => 'This test has already started',
            'subject_id' => $this->subject->id,
            'duration_minutes' => 30,
            'start_time' => now()->subHour(),
            'end_time' => now()->addDay(),
            'passing_percentage' => 60,
            'is_active' => true,
            'teacher_id' => $this->teacher->id
        ]);

        $updatedData = [
            'title' => 'Updated Test Title',
            'duration_minutes' => 45,
            'passing_percentage' => 70,
            'is_active' => false
        ];

        $response = $this->put('/teacher/mcq-tests/' . $test->id, $updatedData);

        $response->assertSessionHasErrors();
        
        $this->assertDatabaseHas('mcq_tests', [
            'id' => $test->id,
            'title' => 'Test in Progress'
        ]);
    }

    public function test_non_teacher_cannot_manage_tests()
    {
        // Create a student user
        $student = User::factory()->create([
            'role' => 'student'
        ]);

        $this->actingAs($student);

        // Try to create a test
        $testData = [
            'title' => 'Unauthorized Test',
            'subject_id' => $this->subject->id,
            'duration_minutes' => 30,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDays(2),
            'passing_percentage' => 60,
            'teacher_id' => $this->teacher->id
        ];

        $response = $this->post('/teacher/mcq-tests', $testData);
        $response->assertForbidden();

        // Create a test as teacher first
        $this->actingAs($this->user);
        $test = McqTest::create([
            'title' => 'Existing Test',
            'subject_id' => $this->subject->id,
            'duration_minutes' => 30,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDays(2),
            'passing_percentage' => 60,
            'is_active' => true,
            'teacher_id' => $this->teacher->id
        ]);

        // Switch back to student and try to edit/delete
        $this->actingAs($student);
        
        $response = $this->put('/teacher/mcq-tests/' . $test->id, ['title' => 'Hacked Test']);
        $response->assertForbidden();

        $response = $this->delete('/teacher/mcq-tests/' . $test->id);
        $response->assertForbidden();
    }
} 