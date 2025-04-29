<?php

namespace Tests\Feature\MCQ;

use Tests\TestCase;
use App\Models\User;
use App\Models\McqTest;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BooleanHandlingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Teacher $teacher;
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

    public function test_direct_database_update_with_values()
    {
        $this->actingAs($this->user);
        
        // Create test with true initially
        $test = McqTest::create([
            'title' => 'Boolean Test',
            'description' => 'Testing boolean values',
            'subject_id' => $this->subject->id,
            'duration_minutes' => 30,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDays(2),
            'passing_percentage' => 60,
            'is_active' => true,
            'teacher_id' => $this->teacher->id
        ]);
        
        // Verify it's true initially
        $this->assertTrue($test->is_active);
        $this->assertDatabaseHas('mcq_tests', [
            'id' => $test->id,
            'is_active' => true
        ]);
        
        // Test 1: Update through Eloquent model assignment
        $test->is_active = false;
        $test->save();
        
        // Refresh and verify
        $test->refresh();
        $this->assertFalse($test->is_active);
        $this->assertDatabaseHas('mcq_tests', [
            'id' => $test->id,
            'is_active' => false
        ]);
        
        // Reset to true
        $test->is_active = true;
        $test->save();
        $test->refresh();
        
        // Test 2: Update through direct DB update
        DB::table('mcq_tests')
            ->where('id', $test->id)
            ->update(['is_active' => false]);
            
        // Refresh and verify
        $test->refresh();
        $this->assertFalse($test->is_active);
        $this->assertDatabaseHas('mcq_tests', [
            'id' => $test->id,
            'is_active' => false
        ]);
    }
    
    public function test_controller_update_with_values()
    {
        $this->actingAs($this->user);
        
        // Create test with true initially using direct SQL to guarantee the values
        $testId = DB::table('mcq_tests')->insertGetId([
            'title' => 'Controller Test',
            'description' => 'Testing controller updates',
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
        
        // Verify the teacher_id is set correctly in the database
        $this->assertDatabaseHas('mcq_tests', [
            'id' => $testId,
            'teacher_id' => $this->teacher->id,
            'is_active' => true
        ]);
        
        // Now use Eloquent to get the model
        $test = McqTest::find($testId);
        $this->assertNotNull($test);
        $this->assertEquals($this->teacher->id, $test->teacher_id);
        
        // Test 1: Update with is_active=false in request
        $updatedData = [
            'title' => 'Controller Test',
            'description' => 'Testing controller updates',
            'duration_minutes' => 30,
            'passing_percentage' => 60,
            'start_time' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'is_active' => false
        ];
        
        $response = $this->put('/teacher/mcq-tests/' . $test->id, $updatedData);
        
        // Check for unauthorized response 
        if ($response->status() === 302) {
            // Check if we got redirected due to authorization error
            $session = $response->getSession();
            if ($session && $session->has('error')) {
                $this->fail('Authorization error: ' . $session->get('error'));
            }
        }
        
        // Get fresh data directly from DB and model
        $freshDbData = DB::table('mcq_tests')->where('id', $testId)->first();
        $test->refresh();
        
        // Print debugging information
        $debugInfo = [
            'test_id' => $test->id,
            'teacher_id' => $test->teacher_id,
            'auth_teacher_id' => $this->teacher->id,
            'is_active_after_update_model' => $test->is_active,
            'is_active_after_update_db' => $freshDbData->is_active,
            'response_status' => $response->status(),
            'data_sent' => $updatedData
        ];
        
        // Log and print the debug info
        \Illuminate\Support\Facades\Log::info('Test debug info', $debugInfo);
        dump($debugInfo);
        
        // Test 2: Update with is_active completely omitted from request
        $updatedData2 = [
            'title' => 'Controller Test Update 2',
            'description' => 'Testing controller updates - omitting is_active',
            'duration_minutes' => 30,
            'passing_percentage' => 60,
            'start_time' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(2)->format('Y-m-d H:i:s')
        ];
        
        $response2 = $this->put('/teacher/mcq-tests/' . $test->id, $updatedData2);
        
        // Get fresh data again
        $freshDbData2 = DB::table('mcq_tests')->where('id', $testId)->first();
        $test->refresh();
        
        // Print debugging information for second test
        $debugInfo2 = [
            'test_id' => $test->id,
            'teacher_id' => $test->teacher_id,
            'auth_teacher_id' => $this->teacher->id,
            'is_active_after_update2_model' => $test->is_active,
            'is_active_after_update2_db' => $freshDbData2->is_active,
            'response_status2' => $response2->status(),
            'data_sent2' => $updatedData2
        ];
        
        \Illuminate\Support\Facades\Log::info('Test debug info 2', $debugInfo2);
        dump($debugInfo2);
        
        // We can assert directly against the database value
        $finalIsActive = (bool)DB::table('mcq_tests')->where('id', $testId)->value('is_active');
        $this->assertFalse($finalIsActive, 'is_active should be false but is: ' . ($finalIsActive ? 'true' : 'false'));
    }
} 