<?php

namespace Tests\Feature\MCQ;

use Tests\TestCase;
use App\Models\User;
use App\Models\McqTest;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;

class TestActiveStatusTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private Subject $subject;
    private Teacher $teacher;

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

    public function test_active_status_is_true_when_set_to_true()
    {
        $this->actingAs($this->user);

        // Create a test with is_active set to true
        $test = McqTest::create([
            'title' => 'Active Test',
            'description' => 'Test with active status true',
            'subject_id' => $this->subject->id,
            'duration_minutes' => 30,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDays(2),
            'passing_percentage' => 60,
            'is_active' => true,
            'teacher_id' => $this->teacher->id
        ]);

        $this->assertDatabaseHas('mcq_tests', [
            'id' => $test->id,
            'is_active' => true
        ]);
    }

    public function test_active_status_is_false_when_set_to_false()
    {
        $this->actingAs($this->user);

        // Create a test with is_active set to false
        $test = McqTest::create([
            'title' => 'Inactive Test',
            'description' => 'Test with active status false',
            'subject_id' => $this->subject->id,
            'duration_minutes' => 30,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDays(2),
            'passing_percentage' => 60,
            'is_active' => false,
            'teacher_id' => $this->teacher->id
        ]);

        $this->assertDatabaseHas('mcq_tests', [
            'id' => $test->id,
            'is_active' => false
        ]);
    }

    public function test_active_status_updates_correctly()
    {
        $this->actingAs($this->user);

        // Create a test with is_active set to true initially using direct SQL
        $testId = DB::table('mcq_tests')->insertGetId([
            'title' => 'Test to Update',
            'description' => 'Test with active status to be updated',
            'subject_id' => $this->subject->id,
            'duration_minutes' => 30,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDays(2),
            'passing_percentage' => 60,
            'is_active' => true,
            'teacher_id' => $this->teacher->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Verify it's set correctly in the database
        $this->assertDatabaseHas('mcq_tests', [
            'id' => $testId,
            'teacher_id' => $this->teacher->id,
            'is_active' => true
        ]);

        // Get the model
        $test = McqTest::find($testId);

        // Update the test with is_active set to false
        $updatedData = [
            'title' => 'Test to Update',
            'description' => 'Test with active status to be updated',
            'duration_minutes' => 30,
            'passing_percentage' => 60,
            'start_time' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'is_active' => false
        ];

        $response = $this->put('/teacher/mcq-tests/' . $test->id, $updatedData);
        
        // Check for errors
        if ($response->isRedirection() && $response->getSession()->has('errors')) {
            $this->fail('Validation errors: ' . json_encode($response->getSession()->get('errors')));
        }
        
        // Verify redirection
        $this->assertTrue($response->isRedirect(), 'Response is not a redirect');
        
        // Check database values directly
        $dbValue = DB::table('mcq_tests')->where('id', $testId)->value('is_active');
        $this->assertEquals(0, $dbValue, "is_active in the database should be 0 but is: {$dbValue}");
        
        // Refresh the model
        $test->refresh();
        $this->assertFalse($test->is_active, "is_active in the model should be false but is: " . ($test->is_active ? 'true' : 'false'));

        // Update the test with is_active explicitly set to true
        $updatedData['is_active'] = true;
        $response = $this->put('/teacher/mcq-tests/' . $test->id, $updatedData);

        // Check database values directly
        $dbValue = DB::table('mcq_tests')->where('id', $testId)->value('is_active');
        $this->assertEquals(1, $dbValue, "is_active in the database should be 1 but is: {$dbValue}");
        
        // Refresh the model
        $test->refresh();
        $this->assertTrue($test->is_active, "is_active in the model should be true but is: " . ($test->is_active ? 'true' : 'false'));
    }
} 