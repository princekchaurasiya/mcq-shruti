<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Test;
use App\Models\Question;
use App\Models\Option;
use App\Models\TestAttempt;
use App\Models\TestResponse;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class StudentTestExperienceTest extends DuskTestCase
{
    /**
     * Test that a student can browse available tests
     */
    public function test_student_can_browse_available_tests(): void
    {
        // Create a teacher and student user
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);
        
        $student = User::factory()->create([
            'email' => 'student_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        // Create published tests
        $test1 = Test::factory()->create([
            'title' => 'Math Quiz',
            'description' => 'Basic arithmetic quiz',
            'user_id' => $teacher->id,
            'status' => 'published',
            'time_limit' => 30,
            'pass_score' => 70,
        ]);

        $test2 = Test::factory()->create([
            'title' => 'Science Test',
            'description' => 'General science knowledge',
            'user_id' => $teacher->id,
            'status' => 'published',
            'time_limit' => 45,
            'pass_score' => 60,
        ]);

        // Create a draft test (should not be visible to students)
        $draftTest = Test::factory()->create([
            'title' => 'Draft Test',
            'user_id' => $teacher->id,
            'status' => 'draft',
        ]);

        $this->browse(function (Browser $browser) use ($student, $test1, $test2, $draftTest) {
            $browser->loginAs($student)
                    ->visit('/student/tests')
                    ->assertSee('Available Tests')
                    ->screenshot('student-test-list')
                    
                    // Should see published tests
                    ->assertSee('Math Quiz')
                    ->assertSee('Basic arithmetic quiz')
                    ->assertSee('Science Test')
                    ->assertSee('General science knowledge')
                    
                    // Should not see draft tests
                    ->assertDontSee('Draft Test')
                    
                    // Should see test details
                    ->assertSee('30 minutes')
                    ->assertSee('45 minutes')
                    ->assertSee('70%')
                    ->assertSee('60%')
                    
                    // Should see action buttons
                    ->assertPresent('@start-test-' . $test1->id)
                    ->assertPresent('@start-test-' . $test2->id);
        });
    }

    /**
     * Test that a student can start a test
     */
    public function test_student_can_start_a_test(): void
    {
        // Create a teacher and student user
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);
        
        $student = User::factory()->create([
            'email' => 'student_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        // Create a test with questions
        $test = Test::factory()->create([
            'title' => 'Geography Quiz',
            'description' => 'Test your knowledge of world geography',
            'user_id' => $teacher->id,
            'status' => 'published',
            'time_limit' => 15,
            'pass_score' => 70,
            'shuffle_questions' => true,
        ]);

        // Create questions
        $question1 = Question::factory()->create([
            'test_id' => $test->id,
            'question_text' => 'What is the capital of France?',
            'question_type' => 'multiple_choice',
            'marks' => 5,
            'order' => 1,
        ]);

        Option::factory()->create([
            'question_id' => $question1->id,
            'text' => 'London',
            'is_correct' => 0,
        ]);

        Option::factory()->create([
            'question_id' => $question1->id,
            'text' => 'Paris',
            'is_correct' => 1,
        ]);

        Option::factory()->create([
            'question_id' => $question1->id,
            'text' => 'Berlin',
            'is_correct' => 0,
        ]);

        Option::factory()->create([
            'question_id' => $question1->id,
            'text' => 'Madrid',
            'is_correct' => 0,
        ]);

        $question2 = Question::factory()->create([
            'test_id' => $test->id,
            'question_text' => 'Which ocean is the largest?',
            'question_type' => 'multiple_choice',
            'marks' => 5,
            'order' => 2,
        ]);

        Option::factory()->create([
            'question_id' => $question2->id,
            'text' => 'Atlantic',
            'is_correct' => 0,
        ]);

        Option::factory()->create([
            'question_id' => $question2->id,
            'text' => 'Pacific',
            'is_correct' => 1,
        ]);

        Option::factory()->create([
            'question_id' => $question2->id,
            'text' => 'Indian',
            'is_correct' => 0,
        ]);

        Option::factory()->create([
            'question_id' => $question2->id,
            'text' => 'Arctic',
            'is_correct' => 0,
        ]);

        $this->browse(function (Browser $browser) use ($student, $test) {
            $browser->loginAs($student)
                    ->visit('/student/tests')
                    ->assertSee('Geography Quiz')
                    ->screenshot('pre-test-start')
                    
                    // View test details
                    ->click('@view-test-' . $test->id)
                    ->assertSee('Geography Quiz')
                    ->assertSee('Test your knowledge of world geography')
                    ->assertSee('15 minutes')
                    ->assertSee('Passing score: 70%')
                    ->screenshot('test-details')
                    
                    // Start the test
                    ->click('@start-test')
                    ->assertSee('Test Instructions')
                    ->assertSee('Once you start, the timer will begin')
                    ->screenshot('test-instructions')
                    ->click('@begin-test')
                    
                    // Should now be on the test page
                    ->assertSee('Geography Quiz - In Progress')
                    ->assertSee('Time Remaining:')
                    ->assertSee('Question 1 of 2')
                    ->screenshot('test-in-progress')
                    
                    // Verify we can see the first question
                    ->assertSee('What is the capital of France?')
                    
                    // Answer the first question
                    ->radio('answer', '2') // Select "Paris"
                    ->screenshot('answered-question1')
                    ->press('Next')
                    
                    // Should now be on the second question
                    ->assertSee('Which ocean is the largest?')
                    ->assertSee('Question 2 of 2')
                    ->screenshot('test-question2')
                    
                    // Answer the second question
                    ->radio('answer', '2') // Select "Pacific"
                    ->screenshot('answered-question2')
                    ->press('Submit Test')
                    
                    // Confirm submission
                    ->acceptDialog()
                    
                    // Should now be on the test results page
                    ->assertSee('Test Results')
                    ->assertSee('Geography Quiz')
                    ->assertSee('Score: 100%')
                    ->assertSee('Status: Passed')
                    ->screenshot('test-results');
            
            // Verify test attempt was recorded in database
            $this->assertDatabaseHas('test_attempts', [
                'user_id' => $student->id,
                'test_id' => $test->id,
                'status' => 'completed',
                'score' => 100,
            ]);
        });
    }

    /**
     * Test that a student can navigate between questions and review answers
     */
    public function test_student_can_navigate_and_review_questions(): void
    {
        // Create a teacher and student user
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);
        
        $student = User::factory()->create([
            'email' => 'student_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        // Create a test with questions
        $test = Test::factory()->create([
            'title' => 'History Quiz',
            'user_id' => $teacher->id,
            'status' => 'published',
            'time_limit' => 20,
            'allow_review' => true,
        ]);

        // Create 3 questions
        $question1 = Question::factory()->create([
            'test_id' => $test->id,
            'question_text' => 'Who was the first president of the United States?',
            'question_type' => 'multiple_choice',
            'order' => 1,
        ]);

        Option::factory()->create([
            'question_id' => $question1->id,
            'text' => 'George Washington',
            'is_correct' => 1,
        ]);

        Option::factory()->create([
            'question_id' => $question1->id,
            'text' => 'Thomas Jefferson',
            'is_correct' => 0,
        ]);

        $question2 = Question::factory()->create([
            'test_id' => $test->id,
            'question_text' => 'In what year did World War II end?',
            'question_type' => 'multiple_choice',
            'order' => 2,
        ]);

        Option::factory()->create([
            'question_id' => $question2->id,
            'text' => '1943',
            'is_correct' => 0,
        ]);

        Option::factory()->create([
            'question_id' => $question2->id,
            'text' => '1945',
            'is_correct' => 1,
        ]);

        $question3 = Question::factory()->create([
            'test_id' => $test->id,
            'question_text' => 'Who wrote the Declaration of Independence?',
            'question_type' => 'multiple_choice',
            'order' => 3,
        ]);

        Option::factory()->create([
            'question_id' => $question3->id,
            'text' => 'Benjamin Franklin',
            'is_correct' => 0,
        ]);

        Option::factory()->create([
            'question_id' => $question3->id,
            'text' => 'Thomas Jefferson',
            'is_correct' => 1,
        ]);

        $this->browse(function (Browser $browser) use ($student, $test) {
            $browser->loginAs($student)
                    ->visit('/student/tests')
                    ->click('@start-test-' . $test->id)
                    ->click('@begin-test')
                    ->screenshot('test-navigation-q1')
                    
                    // Question 1
                    ->assertSee('Who was the first president of the United States?')
                    ->assertSee('Question 1 of 3')
                    
                    // Answer question 1
                    ->radio('answer', '1')
                    ->press('Next')
                    ->screenshot('test-navigation-q2')
                    
                    // Question 2
                    ->assertSee('In what year did World War II end?')
                    ->assertSee('Question 2 of 3')
                    
                    // Go back to question 1
                    ->press('Previous')
                    ->screenshot('test-navigation-back-to-q1')
                    ->assertSee('Who was the first president of the United States?')
                    ->assertSee('Question 1 of 3')
                    
                    // Verify answer was saved
                    ->assertRadioSelected('answer', '1')
                    
                    // Go forward again
                    ->press('Next')
                    ->assertSee('In what year did World War II end?')
                    
                    // Answer question 2
                    ->radio('answer', '2')
                    ->press('Next')
                    ->screenshot('test-navigation-q3')
                    
                    // Question 3
                    ->assertSee('Who wrote the Declaration of Independence?')
                    ->assertSee('Question 3 of 3')
                    
                    // Answer question 3
                    ->radio('answer', '2')
                    
                    // Open review panel
                    ->click('@review-toggle')
                    ->screenshot('test-review-panel')
                    
                    // Verify review panel shows question status
                    ->assertSee('Question 1: Answered')
                    ->assertSee('Question 2: Answered')
                    ->assertSee('Question 3: Not submitted')
                    
                    // Click on question 1 in review panel
                    ->click('@review-question-1')
                    ->assertSee('Who was the first president of the United States?')
                    ->assertRadioSelected('answer', '1')
                    ->screenshot('review-question-1')
                    
                    // Go back to question 3
                    ->click('@review-question-3')
                    ->assertSee('Who wrote the Declaration of Independence?')
                    
                    // Submit the test
                    ->press('Submit Test')
                    ->acceptDialog()
                    ->assertSee('Test Results')
                    ->screenshot('test-navigation-results');
        });
    }

    /**
     * Test that a student can view test explanations after completion
     */
    public function test_student_can_view_explanations_after_test(): void
    {
        // Create a teacher and student user
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);
        
        $student = User::factory()->create([
            'email' => 'student_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        // Create a test with questions and explanations
        $test = Test::factory()->create([
            'title' => 'Science Quiz',
            'user_id' => $teacher->id,
            'status' => 'published',
            'time_limit' => 10,
            'show_explanations' => true,
        ]);

        // Create question with explanation
        $question = Question::factory()->create([
            'test_id' => $test->id,
            'question_text' => 'What is the chemical symbol for water?',
            'question_type' => 'multiple_choice',
            'explanation' => 'Water is made up of hydrogen and oxygen, with the chemical formula H₂O.',
        ]);

        Option::factory()->create([
            'question_id' => $question->id,
            'text' => 'H₂O',
            'is_correct' => 1,
        ]);

        Option::factory()->create([
            'question_id' => $question->id,
            'text' => 'CO₂',
            'is_correct' => 0,
        ]);

        Option::factory()->create([
            'question_id' => $question->id,
            'text' => 'NaCl',
            'is_correct' => 0,
        ]);

        // Create a test attempt
        $testAttempt = TestAttempt::factory()->create([
            'user_id' => $student->id,
            'test_id' => $test->id,
            'status' => 'completed',
            'score' => 100,
        ]);

        // Create test response
        TestResponse::factory()->create([
            'test_attempt_id' => $testAttempt->id,
            'question_id' => $question->id,
            'selected_option_id' => $question->options->where('is_correct', 1)->first()->id,
            'is_correct' => true,
        ]);

        $this->browse(function (Browser $browser) use ($student, $test, $testAttempt, $question) {
            $browser->loginAs($student)
                    ->visit('/student/test-attempt/' . $testAttempt->id . '/results')
                    ->assertSee('Test Results')
                    ->assertSee('Science Quiz')
                    ->assertSee('Score: 100%')
                    ->screenshot('explanation-results-page')
                    
                    // View detailed results
                    ->click('@view-detailed-results')
                    ->assertSee('Detailed Results')
                    ->assertSee('What is the chemical symbol for water?')
                    ->assertSee('Your answer: H₂O')
                    ->assertSee('Correct')
                    ->screenshot('explanation-detailed-results')
                    
                    // Expand explanation
                    ->click('@view-explanation-' . $question->id)
                    ->assertSee('Explanation')
                    ->assertSee('Water is made up of hydrogen and oxygen, with the chemical formula H₂O.')
                    ->screenshot('explanation-expanded');
        });
    }

    /**
     * Test that a student can resume an interrupted test
     */
    public function test_student_can_resume_interrupted_test(): void
    {
        // Create a teacher and student user
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);
        
        $student = User::factory()->create([
            'email' => 'student_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        // Create a test with questions
        $test = Test::factory()->create([
            'title' => 'English Quiz',
            'user_id' => $teacher->id,
            'status' => 'published',
            'time_limit' => 30,
            'allow_resume' => true,
        ]);

        // Create questions
        $question1 = Question::factory()->create([
            'test_id' => $test->id,
            'question_text' => 'What is a synonym for "happy"?',
            'question_type' => 'multiple_choice',
            'order' => 1,
        ]);

        Option::factory()->create([
            'question_id' => $question1->id,
            'text' => 'Sad',
            'is_correct' => 0,
        ]);

        Option::factory()->create([
            'question_id' => $question1->id,
            'text' => 'Joyful',
            'is_correct' => 1,
        ]);

        $question2 = Question::factory()->create([
            'test_id' => $test->id,
            'question_text' => 'What part of speech is "quickly"?',
            'question_type' => 'multiple_choice',
            'order' => 2,
        ]);

        Option::factory()->create([
            'question_id' => $question2->id,
            'text' => 'Adverb',
            'is_correct' => 1,
        ]);

        Option::factory()->create([
            'question_id' => $question2->id,
            'text' => 'Adjective',
            'is_correct' => 0,
        ]);

        // Create an in-progress test attempt
        $testAttempt = TestAttempt::factory()->create([
            'user_id' => $student->id,
            'test_id' => $test->id,
            'status' => 'in_progress',
            'start_time' => now()->subMinutes(5),
            'end_time' => null,
            'current_question' => 1,
        ]);

        // Create response for first question
        TestResponse::factory()->create([
            'test_attempt_id' => $testAttempt->id,
            'question_id' => $question1->id,
            'selected_option_id' => $question1->options->where('is_correct', 1)->first()->id,
            'is_correct' => true,
        ]);

        $this->browse(function (Browser $browser) use ($student, $test, $testAttempt, $question1, $question2) {
            $browser->loginAs($student)
                    ->visit('/student/tests')
                    ->assertSee('English Quiz')
                    ->assertSee('Resume Test')
                    ->screenshot('resume-test-list')
                    
                    // Resume the test
                    ->click('@resume-test-' . $testAttempt->id)
                    ->assertSee('English Quiz - In Progress')
                    ->assertSee('Time Remaining:')
                    ->screenshot('resuming-test')
                    
                    // Should be on question 1 with saved answer
                    ->assertSee('What is a synonym for "happy"?')
                    ->assertRadioSelected('answer', '2') // Joyful
                    
                    // Go to next question
                    ->press('Next')
                    ->assertSee('What part of speech is "quickly"?')
                    ->screenshot('resumed-test-q2')
                    
                    // Answer question 2
                    ->radio('answer', '1') // Adverb
                    ->press('Submit Test')
                    ->acceptDialog()
                    
                    // Should see results
                    ->assertSee('Test Results')
                    ->assertSee('English Quiz')
                    ->assertSee('Score: 100%')
                    ->screenshot('resume-test-results');
                    
            // Verify test attempt was updated in database
            $this->assertDatabaseHas('test_attempts', [
                'id' => $testAttempt->id,
                'status' => 'completed',
                'score' => 100,
            ]);
            
            // Verify responses were recorded
            $this->assertDatabaseHas('test_responses', [
                'test_attempt_id' => $testAttempt->id,
                'question_id' => $question2->id,
                'is_correct' => true,
            ]);
        });
    }

    /**
     * Test that a student can view their test history
     */
    public function test_student_can_view_test_history(): void
    {
        // Create a teacher and student user
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);
        
        $student = User::factory()->create([
            'email' => 'student_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        // Create tests
        $test1 = Test::factory()->create([
            'title' => 'Math Quiz',
            'user_id' => $teacher->id,
            'status' => 'published',
        ]);

        $test2 = Test::factory()->create([
            'title' => 'Science Test',
            'user_id' => $teacher->id,
            'status' => 'published',
        ]);

        // Create completed test attempts
        $pastAttempt1 = TestAttempt::factory()->create([
            'user_id' => $student->id,
            'test_id' => $test1->id,
            'status' => 'completed',
            'score' => 80,
            'start_time' => now()->subDays(5),
            'end_time' => now()->subDays(5)->addMinutes(20),
        ]);

        $pastAttempt2 = TestAttempt::factory()->create([
            'user_id' => $student->id,
            'test_id' => $test1->id,
            'status' => 'completed',
            'score' => 90,
            'start_time' => now()->subDays(2),
            'end_time' => now()->subDays(2)->addMinutes(15),
        ]);

        $pastAttempt3 = TestAttempt::factory()->create([
            'user_id' => $student->id,
            'test_id' => $test2->id,
            'status' => 'completed',
            'score' => 75,
            'start_time' => now()->subDay(),
            'end_time' => now()->subDay()->addMinutes(25),
        ]);

        $this->browse(function (Browser $browser) use ($student, $test1, $test2, $pastAttempt1, $pastAttempt2, $pastAttempt3) {
            $browser->loginAs($student)
                    ->visit('/student/history')
                    ->assertSee('Test History')
                    ->assertSee('Math Quiz')
                    ->assertSee('Science Test')
                    ->screenshot('test-history')
                    
                    // Should show all attempts
                    ->assertSee('80%')
                    ->assertSee('90%')
                    ->assertSee('75%')
                    
                    // Should show dates
                    ->assertPresent('@attempt-date-' . $pastAttempt1->id)
                    ->assertPresent('@attempt-date-' . $pastAttempt2->id)
                    ->assertPresent('@attempt-date-' . $pastAttempt3->id)
                    
                    // Filter by test
                    ->select('test_filter', $test1->id)
                    ->press('Filter')
                    ->assertSee('Math Quiz')
                    ->assertDontSee('Science Test')
                    ->assertSee('80%')
                    ->assertSee('90%')
                    ->assertDontSee('75%')
                    ->screenshot('test-history-filtered')
                    
                    // View detailed results of an attempt
                    ->click('@view-attempt-' . $pastAttempt2->id)
                    ->assertSee('Test Results')
                    ->assertSee('Math Quiz')
                    ->assertSee('Score: 90%')
                    ->screenshot('test-history-details');
        });
    }

    /**
     * Test that a student can see time warnings during a test
     */
    public function test_student_receives_time_warnings(): void
    {
        // Create a teacher and student user
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);
        
        $student = User::factory()->create([
            'email' => 'student_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        // Create a test with a short time limit
        $test = Test::factory()->create([
            'title' => 'Quick Quiz',
            'user_id' => $teacher->id,
            'status' => 'published',
            'time_limit' => 3, // 3 minutes
            'show_time_warnings' => true,
        ]);

        // Create a simple question
        $question = Question::factory()->create([
            'test_id' => $test->id,
            'question_text' => 'What is 1+1?',
            'question_type' => 'multiple_choice',
        ]);

        Option::factory()->create([
            'question_id' => $question->id,
            'text' => '2',
            'is_correct' => 1,
        ]);

        Option::factory()->create([
            'question_id' => $question->id,
            'text' => '3',
            'is_correct' => 0,
        ]);

        $this->browse(function (Browser $browser) use ($student, $test) {
            // Due to Dusk limitations for testing time-based events, 
            // we'll mock the test start time to be 2.5 minutes ago
            $testAttempt = TestAttempt::factory()->create([
                'user_id' => $student->id,
                'test_id' => $test->id,
                'status' => 'in_progress',
                'start_time' => now()->subMinutes(2)->subSeconds(30), // 30 seconds remaining
                'end_time' => null,
            ]);
            
            $browser->loginAs($student)
                    ->visit('/student/test-attempt/' . $testAttempt->id)
                    ->assertSee('Quick Quiz - In Progress')
                    
                    // Should see time warning (less than 1 minute remaining)
                    ->assertPresent('@time-warning')
                    ->assertSee('Less than 1 minute remaining!')
                    ->screenshot('time-warning')
                    
                    // Answer the question
                    ->radio('answer', '1')
                    ->press('Submit Test')
                    ->acceptDialog()
                    
                    // Should see results
                    ->assertSee('Test Results')
                    ->assertSee('Quick Quiz')
                    ->screenshot('time-warning-results');
        });
    }
} 