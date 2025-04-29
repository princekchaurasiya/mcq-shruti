<?php

namespace Tests\Feature\MCQ;

use Tests\TestCase;
use App\Models\User;
use App\Models\McqTest;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BooleanEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Teacher $teacher;
    private Subject $subject;
    private McqTest $test;

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
            'name' => 'Boolean Testing',
            'description' => 'Testing boolean edge cases'
        ]);

        // Create a base test that we'll update in various ways
        $this->test = McqTest::create([
            'title' => 'Boolean Edge Case Test',
            'description' => 'Testing various boolean inputs',
            'subject_id' => $this->subject->id,
            'duration_minutes' => 30,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDays(2),
            'passing_percentage' => 60,
            'is_active' => true,
            'teacher_id' => $this->teacher->id
        ]);
        
        $this->actingAs($this->user);
    }

    /**
     * Test with various truthy string values for is_active
     */
    public function test_truthy_string_values()
    {
        $truthyValues = ['1', 'true', 'yes', 'on'];
        
        foreach ($truthyValues as $value) {
            $updateData = [
                'title' => 'Boolean Test - Truthy: ' . $value,
                'description' => 'Testing with truthy value: ' . $value,
                'duration_minutes' => 30,
                'passing_percentage' => 60,
                'start_time' => now()->addDay()->format('Y-m-d H:i:s'),
                'end_time' => now()->addDays(2)->format('Y-m-d H:i:s'),
                'is_active' => $value
            ];
            
            $response = $this->put('/teacher/mcq-tests/' . $this->test->id, $updateData);
            $response->assertRedirect();
            
            $this->test->refresh();
            $this->assertTrue($this->test->is_active, "Value '{$value}' should be treated as true");
            
            $dbValue = DB::table('mcq_tests')->where('id', $this->test->id)->value('is_active');
            $this->assertSame(1, $dbValue, "Value '{$value}' should be stored as 1 in database");
        }
    }
    
    /**
     * Test with various falsy string values for is_active
     */
    public function test_falsy_string_values()
    {
        $falsyValues = ['0', 'false', 'no', 'off'];
        
        foreach ($falsyValues as $value) {
            $updateData = [
                'title' => 'Boolean Test - Falsy: ' . $value,
                'description' => 'Testing with falsy value: ' . $value,
                'duration_minutes' => 30,
                'passing_percentage' => 60,
                'start_time' => now()->addDay()->format('Y-m-d H:i:s'),
                'end_time' => now()->addDays(2)->format('Y-m-d H:i:s'),
                'is_active' => $value
            ];
            
            $response = $this->put('/teacher/mcq-tests/' . $this->test->id, $updateData);
            $response->assertRedirect();
            
            $this->test->refresh();
            $this->assertFalse($this->test->is_active, "Value '{$value}' should be treated as false");
            
            $dbValue = DB::table('mcq_tests')->where('id', $this->test->id)->value('is_active');
            $this->assertSame(0, $dbValue, "Value '{$value}' should be stored as 0 in database");
        }
    }
    
    /**
     * Test with omitted is_active field, simulating an unchecked checkbox
     */
    public function test_omitted_field()
    {
        // First ensure it's set to true
        $this->test->is_active = true;
        $this->test->save();
        $this->test->refresh();
        $this->assertTrue($this->test->is_active);
        
        // Then update without including is_active
        $updateData = [
            'title' => 'Boolean Test - Omitted Field',
            'description' => 'Testing with omitted is_active field',
            'duration_minutes' => 30,
            'passing_percentage' => 60,
            'start_time' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(2)->format('Y-m-d H:i:s')
            // is_active deliberately omitted
        ];
        
        $response = $this->put('/teacher/mcq-tests/' . $this->test->id, $updateData);
        $response->assertRedirect();
        
        $this->test->refresh();
        $this->assertFalse($this->test->is_active, "Omitted field should be treated as false");
        
        $dbValue = DB::table('mcq_tests')->where('id', $this->test->id)->value('is_active');
        $this->assertSame(0, $dbValue, "Omitted field should be stored as 0 in database");
    }
    
    /**
     * Test with null value for is_active
     */
    public function test_null_value()
    {
        $updateData = [
            'title' => 'Boolean Test - Null Value',
            'description' => 'Testing with null is_active value',
            'duration_minutes' => 30,
            'passing_percentage' => 60,
            'start_time' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'is_active' => null
        ];
        
        $response = $this->put('/teacher/mcq-tests/' . $this->test->id, $updateData);
        $response->assertRedirect();
        
        $this->test->refresh();
        $this->assertFalse($this->test->is_active, "Null value should be treated as false");
        
        $dbValue = DB::table('mcq_tests')->where('id', $this->test->id)->value('is_active');
        $this->assertSame(0, $dbValue, "Null value should be stored as 0 in database");
    }
    
    /**
     * Test with empty string value for is_active
     */
    public function test_empty_string_value()
    {
        $updateData = [
            'title' => 'Boolean Test - Empty String',
            'description' => 'Testing with empty string is_active value',
            'duration_minutes' => 30,
            'passing_percentage' => 60,
            'start_time' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'is_active' => ''
        ];
        
        $response = $this->put('/teacher/mcq-tests/' . $this->test->id, $updateData);
        $response->assertRedirect();
        
        $this->test->refresh();
        $this->assertFalse($this->test->is_active, "Empty string should be treated as false");
        
        $dbValue = DB::table('mcq_tests')->where('id', $this->test->id)->value('is_active');
        $this->assertSame(0, $dbValue, "Empty string should be stored as 0 in database");
    }
} 