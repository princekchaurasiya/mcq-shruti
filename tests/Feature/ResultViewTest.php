<?php

namespace Tests\Feature;

use App\Models\MCQTest;
use App\Models\Question;
use App\Models\StudentResponse;
use App\Models\TestAttempt;
use App\Models\User;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultViewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a subject
        $this->subject = Subject::factory()->create([
            'name' => 'Science',
            'description' => 'Science concepts and facts'
        ]);
        
        // Create a teacher user
        $this->teacherUser = User::factory()->create(['role' => 'teacher']);
        
        // Create a teacher record linked to the teacher user
        $this->teacher = Teacher::create([
            'user_id' => $this->teacherUser->id,
            'subject_id' => $this->subject->id,
            'qualification' => 'PhD in Science',
            'experience_years' => 5
        ]);
        
        // Create a student
        $this->student = User::factory()->create(['role' => 'student']);
        
        // Create a test
        $this->test = MCQTest::factory()->create([
            'teacher_id' => $this->teacher->id,  // Use the actual teacher.id
            'subject_id' => $this->subject->id,
            'title' => 'Science Quiz',
            'duration_minutes' => 30,
            'passing_percentage' => 60,
            'is_active' => true,
            'start_time' => now()->subHour(),
            'end_time' => now()->addHour(),
        ]);
        
        // Create questions
        $this->question1 = Question::factory()->create([
            'mcq_test_id' => $this->test->id,
            'question_text' => 'What is the chemical symbol for water?',
            'options' => json_encode(['H2O', 'CO2', 'O2', 'H2']),
            'correct_option' => json_encode([0]), // H2O is correct
            'explanation' => 'Water is composed of hydrogen and oxygen, hence H2O.'
        ]);
        
        $this->question2 = Question::factory()->create([
            'mcq_test_id' => $this->test->id,
            'question_text' => 'Which planet is known as the Red Planet?',
            'options' => json_encode(['Venus', 'Mars', 'Jupiter', 'Saturn']),
            'correct_option' => json_encode([1]), // Mars is correct
            'explanation' => 'Mars appears red due to iron oxide (rust) on its surface.'
        ]);
        
        // Create a test attempt
        $this->testAttempt = TestAttempt::create([
            'user_id' => $this->student->id,
            'mcq_test_id' => $this->test->id,
            'started_at' => now()->subMinutes(15),
            'completed_at' => now()->subMinutes(5),
            'score' => 50, // Will be updated based on responses
        ]);
        
        // Create student responses - one correct, one incorrect
        $this->correctResponse = StudentResponse::create([
            'test_attempt_id' => $this->testAttempt->id,
            'question_id' => $this->question1->id,
            'selected_option' => json_encode([0]), // Selected H2O - correct
            'is_correct' => true,
        ]);
        
        $this->incorrectResponse = StudentResponse::create([
            'test_attempt_id' => $this->testAttempt->id,
            'question_id' => $this->question2->id,
            'selected_option' => json_encode([0]), // Selected Venus - incorrect
            'is_correct' => false,
        ]);
        
        // Update test attempt score
        $this->testAttempt->score = 50; // 1 correct out of 2 questions
        $this->testAttempt->save();
    }

    public function test_student_can_view_their_results()
    {
        $this->actingAs($this->student);
        
        $response = $this->get(route('student.results.show', $this->testAttempt->id));
        
        $response->assertStatus(200);
        $response->assertViewIs('student.results.show');
        $response->assertViewHas('result', $this->testAttempt);
    }
    
    public function test_result_page_contains_correct_answer_styling()
    {
        $this->actingAs($this->student);
        
        $response = $this->get(route('student.results.show', $this->testAttempt->id));
        
        $response->assertStatus(200);
        $response->assertSee('correct-answer');
        $response->assertSee('badge bg-success');
    }
    
    public function test_result_page_contains_incorrect_answer_styling()
    {
        $this->actingAs($this->student);
        
        $response = $this->get(route('student.results.show', $this->testAttempt->id));
        
        $response->assertStatus(200);
        $response->assertSee('marked-incorrect');
        $response->assertSee('badge bg-danger');
    }
    
    public function test_student_cannot_view_other_students_results()
    {
        // Create another student
        $anotherStudent = User::factory()->create(['role' => 'student']);
        
        $this->actingAs($anotherStudent);
        
        $response = $this->get(route('student.results.show', $this->testAttempt->id));
        
        $response->assertStatus(403); // Forbidden
    }
    
    public function test_teacher_can_view_student_results()
    {
        $this->actingAs($this->teacherUser);
        
        $response = $this->get(route('teacher.results.show', $this->testAttempt->id));
        
        $response->assertStatus(200);
    }
    
    public function test_result_handles_null_selected_options()
    {
        // Create a response with null selected option
        $nullResponse = StudentResponse::create([
            'test_attempt_id' => $this->testAttempt->id,
            'question_id' => $this->question2->id,
            'selected_option' => null,
            'is_correct' => false,
        ]);
        
        $this->actingAs($this->student);
        
        $response = $this->get(route('student.results.show', $this->testAttempt->id));
        
        $response->assertStatus(200);
        // Should not crash with null selected options
    }
} 