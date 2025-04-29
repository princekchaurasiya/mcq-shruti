<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Test;
use App\Models\Question;
use App\Models\Option;
use App\Models\TestAttempt;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Support\Str;

class StudentTestAttemptTest extends DuskTestCase
{
    /**
     * Test student can view available tests
     */
    public function test_student_can_view_available_tests(): void
    {
        // Create a teacher user
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        // Create a student user
        $student = User::factory()->create([
            'email' => 'student_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        // Create two tests
        $test1 = Test::factory()->create([
            'title' => 'Math Test ' . Str::random(5),
            'description' => 'Basic arithmetic and algebra',
            'duration' => 30,
            'user_id' => $teacher->id,
            'status' => 'published',
        ]);

        $test2 = Test::factory()->create([
            'title' => 'Science Test ' . Str::random(5),
            'description' => 'Basic science concepts',
            'duration' => 45,
            'user_id' => $teacher->id,
            'status' => 'published',
        ]);

        $this->browse(function (Browser $browser) use ($student, $test1, $test2) {
            $browser->loginAs($student)
                    ->visit('/dashboard')
                    ->screenshot('student-dashboard')
                    ->visit('/student/tests')
                    ->screenshot('student-available-tests')
                    ->assertSee('Available Tests')
                    ->assertSee($test1->title)
                    ->assertSee($test1->description)
                    ->assertSee($test2->title)
                    ->assertSee($test2->description);
        });
    }

    /**
     * Test student can start a test
     */
    public function test_student_can_start_test(): void
    {
        // Create a teacher user
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        // Create a student user
        $student = User::factory()->create([
            'email' => 'student_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        // Create a test
        $test = Test::factory()->create([
            'title' => 'English Test ' . Str::random(5),
            'description' => 'Grammar and vocabulary',
            'duration' => 30,
            'user_id' => $teacher->id,
            'status' => 'published',
        ]);

        // Add a question to the test
        $question = Question::factory()->create([
            'test_id' => $test->id,
            'question_text' => 'Which is the correct spelling?',
            'question_type' => 'multiple_choice',
            'marks' => 5,
        ]);

        // Add options to the question
        Option::factory()->create([
            'question_id' => $question->id,
            'text' => 'Accomodate',
            'is_correct' => false,
        ]);

        Option::factory()->create([
            'question_id' => $question->id,
            'text' => 'Accommodate',
            'is_correct' => true,
        ]);

        Option::factory()->create([
            'question_id' => $question->id,
            'text' => 'Acommodate',
            'is_correct' => false,
        ]);

        Option::factory()->create([
            'question_id' => $question->id,
            'text' => 'Acomodate',
            'is_correct' => false,
        ]);

        $this->browse(function (Browser $browser) use ($student, $test) {
            $browser->loginAs($student)
                    ->visit('/student/tests')
                    ->screenshot('before-start-test')
                    ->click('@start-test-' . $test->id)
                    ->assertSee('Test Instructions')
                    ->assertSee($test->title)
                    ->assertSee('Duration: ' . $test->duration . ' minutes')
                    ->screenshot('test-instructions')
                    ->press('Start Test')
                    ->assertPathIs('/student/tests/' . $test->id . '/attempt')
                    ->assertSee('Which is the correct spelling?')
                    ->screenshot('test-in-progress');

            // Verify a test attempt was created
            $this->assertDatabaseHas('test_attempts', [
                'user_id' => $student->id,
                'test_id' => $test->id,
                'completed_at' => null, // Not completed yet
            ]);
        });
    }

    /**
     * Test student can submit answers to a test
     */
    public function test_student_can_submit_test_answers(): void
    {
        // Create a teacher user
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        // Create a student user
        $student = User::factory()->create([
            'email' => 'student_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        // Create a test
        $test = Test::factory()->create([
            'title' => 'Geography Test ' . Str::random(5),
            'description' => 'World geography',
            'duration' => 15,
            'user_id' => $teacher->id,
            'status' => 'published',
        ]);

        // Add two questions to the test
        $question1 = Question::factory()->create([
            'test_id' => $test->id,
            'question_text' => 'What is the capital of France?',
            'question_type' => 'multiple_choice',
            'marks' => 5,
        ]);

        // Add options to question 1
        $option1_1 = Option::factory()->create([
            'question_id' => $question1->id,
            'text' => 'London',
            'is_correct' => false,
        ]);

        $option1_2 = Option::factory()->create([
            'question_id' => $question1->id,
            'text' => 'Paris',
            'is_correct' => true,
        ]);

        $option1_3 = Option::factory()->create([
            'question_id' => $question1->id,
            'text' => 'Berlin',
            'is_correct' => false,
        ]);

        $option1_4 = Option::factory()->create([
            'question_id' => $question1->id,
            'text' => 'Madrid',
            'is_correct' => false,
        ]);

        $question2 = Question::factory()->create([
            'test_id' => $test->id,
            'question_text' => 'Which country has the largest population?',
            'question_type' => 'multiple_choice',
            'marks' => 5,
        ]);

        // Add options to question 2
        $option2_1 = Option::factory()->create([
            'question_id' => $question2->id,
            'text' => 'India',
            'is_correct' => false,
        ]);

        $option2_2 = Option::factory()->create([
            'question_id' => $question2->id,
            'text' => 'USA',
            'is_correct' => false,
        ]);

        $option2_3 = Option::factory()->create([
            'question_id' => $question2->id,
            'text' => 'China',
            'is_correct' => true,
        ]);

        $option2_4 = Option::factory()->create([
            'question_id' => $question2->id,
            'text' => 'Brazil',
            'is_correct' => false,
        ]);

        // Create a test attempt
        $testAttempt = TestAttempt::factory()->create([
            'user_id' => $student->id,
            'test_id' => $test->id,
            'started_at' => now(),
            'completed_at' => null,
        ]);

        $this->browse(function (Browser $browser) use ($student, $test, $question1, $question2, $option1_2, $option2_3, $testAttempt) {
            $browser->loginAs($student)
                    ->visit('/student/tests/' . $test->id . '/attempt')
                    ->screenshot('test-questions')
                    // Answer question 1
                    ->radio('answers[' . $question1->id . ']', $option1_2->id)
                    // Answer question 2
                    ->radio('answers[' . $question2->id . ']', $option2_3->id)
                    ->screenshot('test-with-answers')
                    ->press('Submit Test')
                    ->assertSee('Test Completed')
                    ->assertSee('Your answers have been submitted successfully')
                    ->screenshot('test-submission-confirmation');

            // Verify the test attempt was updated
            $this->assertDatabaseHas('test_attempts', [
                'id' => $testAttempt->id,
                'user_id' => $student->id,
                'test_id' => $test->id,
                'completed_at' => now()->format('Y-m-d'),
                'score' => 10, // Both answers correct, 5 marks each
            ]);

            // Verify the student's answers were recorded
            $this->assertDatabaseHas('student_answers', [
                'test_attempt_id' => $testAttempt->id,
                'question_id' => $question1->id,
                'option_id' => $option1_2->id,
            ]);

            $this->assertDatabaseHas('student_answers', [
                'test_attempt_id' => $testAttempt->id,
                'question_id' => $question2->id,
                'option_id' => $option2_3->id,
            ]);
        });
    }

    /**
     * Test student can view their test results
     */
    public function test_student_can_view_test_results(): void
    {
        // Create a teacher user
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        // Create a student user
        $student = User::factory()->create([
            'email' => 'student_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        // Create a test
        $test = Test::factory()->create([
            'title' => 'History Test ' . Str::random(5),
            'description' => 'World history',
            'duration' => 30,
            'user_id' => $teacher->id,
            'status' => 'published',
        ]);

        // Create a completed test attempt
        $testAttempt = TestAttempt::factory()->create([
            'user_id' => $student->id,
            'test_id' => $test->id,
            'started_at' => now()->subHour(),
            'completed_at' => now()->subMinutes(30),
            'score' => 75,
        ]);

        $this->browse(function (Browser $browser) use ($student, $test, $testAttempt) {
            $browser->loginAs($student)
                    ->visit('/student/results')
                    ->screenshot('student-results-list')
                    ->assertSee('My Test Results')
                    ->assertSee($test->title)
                    ->assertSee('75')  // Score
                    ->click('@view-result-' . $testAttempt->id)
                    ->assertSee('Test Result Details')
                    ->assertSee($test->title)
                    ->assertSee('Score: 75')
                    ->screenshot('test-result-details');
        });
    }

    /**
     * Test student can resume an incomplete test
     */
    public function test_student_can_resume_incomplete_test(): void
    {
        // Create a teacher user
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        // Create a student user
        $student = User::factory()->create([
            'email' => 'student_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        // Create a test
        $test = Test::factory()->create([
            'title' => 'Computer Science Test ' . Str::random(5),
            'description' => 'Programming basics',
            'duration' => 60,
            'user_id' => $teacher->id,
            'status' => 'published',
        ]);

        // Add a question to the test
        $question = Question::factory()->create([
            'test_id' => $test->id,
            'question_text' => 'What does HTML stand for?',
            'question_type' => 'multiple_choice',
            'marks' => 5,
        ]);

        // Add options to the question
        Option::factory()->create([
            'question_id' => $question->id,
            'text' => 'Hyper Text Markup Language',
            'is_correct' => true,
        ]);

        Option::factory()->create([
            'question_id' => $question->id,
            'text' => 'High Tech Modern Language',
            'is_correct' => false,
        ]);

        Option::factory()->create([
            'question_id' => $question->id,
            'text' => 'Hyper Transfer Markup Language',
            'is_correct' => false,
        ]);

        // Create an incomplete test attempt (started but not completed)
        $testAttempt = TestAttempt::factory()->create([
            'user_id' => $student->id,
            'test_id' => $test->id,
            'started_at' => now()->subMinutes(10),
            'completed_at' => null,
        ]);

        $this->browse(function (Browser $browser) use ($student, $test, $testAttempt) {
            $browser->loginAs($student)
                    ->visit('/student/dashboard')
                    ->assertSee('In Progress Tests')
                    ->assertSee($test->title)
                    ->screenshot('dashboard-with-incomplete-test')
                    ->click('@resume-test-' . $testAttempt->id)
                    ->assertPathIs('/student/tests/' . $test->id . '/attempt')
                    ->assertSee('Time Remaining:')
                    ->assertSee('What does HTML stand for?')
                    ->screenshot('resumed-test');
                    
            // Verify the test attempt was not duplicated
            $this->assertCount(1, TestAttempt::where('user_id', $student->id)
                                              ->where('test_id', $test->id)
                                              ->where('completed_at', null)
                                              ->get());
        });
    }
} 