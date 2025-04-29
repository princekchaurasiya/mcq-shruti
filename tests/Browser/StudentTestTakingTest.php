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

class StudentTestTakingTest extends DuskTestCase
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

        // Create some tests
        $test1 = Test::factory()->create([
            'title' => 'Available Test 1 ' . Str::random(5),
            'user_id' => $teacher->id,
            'status' => 'published',
        ]);

        $test2 = Test::factory()->create([
            'title' => 'Available Test 2 ' . Str::random(5),
            'user_id' => $teacher->id,
            'status' => 'published',
        ]);

        // Create a draft test that shouldn't be visible to students
        $draftTest = Test::factory()->create([
            'title' => 'Draft Test ' . Str::random(5),
            'user_id' => $teacher->id,
            'status' => 'draft',
        ]);

        $this->browse(function (Browser $browser) use ($student, $test1, $test2, $draftTest) {
            $browser->loginAs($student)
                    ->visit('/student/dashboard')
                    ->screenshot('student-dashboard')
                    ->assertSee('Available Tests')
                    ->assertSee($test1->title)
                    ->assertSee($test2->title)
                    ->assertDontSee($draftTest->title)
                    ->screenshot('available-tests');
            
            // Check test details are visible
            $browser->click('@view-test-' . $test1->id)
                    ->assertSee($test1->title)
                    ->assertSee($test1->description)
                    ->assertSee($test1->duration . ' minutes')
                    ->assertSee('Start Test')
                    ->screenshot('test-details');
        });
    }

    /**
     * Test student can take a test
     */
    public function test_student_can_take_test(): void
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
            'title' => 'Test to Take ' . Str::random(5),
            'description' => 'Description for test to take',
            'duration' => 30,
            'user_id' => $teacher->id,
            'status' => 'published',
        ]);

        // Create questions for the test
        $question1 = Question::factory()->create([
            'test_id' => $test->id,
            'text' => 'What is the capital of France?',
            'type' => 'multiple_choice',
            'points' => 5,
        ]);

        // Create options for question 1
        Option::factory()->create([
            'question_id' => $question1->id,
            'text' => 'Paris',
            'is_correct' => true,
        ]);

        Option::factory()->create([
            'question_id' => $question1->id,
            'text' => 'London',
            'is_correct' => false,
        ]);

        Option::factory()->create([
            'question_id' => $question1->id,
            'text' => 'Berlin',
            'is_correct' => false,
        ]);

        $question2 = Question::factory()->create([
            'test_id' => $test->id,
            'text' => 'What is 2 + 2?',
            'type' => 'multiple_choice',
            'points' => 5,
        ]);

        // Create options for question 2
        Option::factory()->create([
            'question_id' => $question2->id,
            'text' => '3',
            'is_correct' => false,
        ]);

        Option::factory()->create([
            'question_id' => $question2->id,
            'text' => '4',
            'is_correct' => true,
        ]);

        Option::factory()->create([
            'question_id' => $question2->id,
            'text' => '5',
            'is_correct' => false,
        ]);

        $this->browse(function (Browser $browser) use ($student, $test, $question1, $question2) {
            $browser->loginAs($student)
                    ->visit('/student/dashboard')
                    ->screenshot('student-dashboard-before-test')
                    ->click('@view-test-' . $test->id)
                    ->screenshot('test-details-before-start')
                    ->click('@start-test')
                    ->assertSee('Time Remaining:')
                    ->assertSee($question1->text)
                    ->screenshot('test-in-progress-q1');

            // Answer question 1
            $browser->radio('answers[' . $question1->id . ']', '1') // Select correct answer (Paris)
                    ->click('@next-question')
                    ->assertSee($question2->text)
                    ->screenshot('test-in-progress-q2');

            // Answer question 2
            $browser->radio('answers[' . $question2->id . ']', '2') // Select correct answer (4)
                    ->click('@submit-test')
                    ->assertPathIs('/student/tests/' . $test->id . '/result')
                    ->assertSee('Test Completed')
                    ->assertSee('100%') // Expecting 100% as all answers were correct
                    ->screenshot('test-result');

            // Verify test attempt was created in database
            $testAttempt = TestAttempt::where('user_id', $student->id)
                                     ->where('test_id', $test->id)
                                     ->first();

            $this->assertNotNull($testAttempt);
            $this->assertEquals(100, $testAttempt->score);
            $this->assertNotNull($testAttempt->completed_at);
        });
    }

    /**
     * Test student can view their test history
     */
    public function test_student_can_view_test_history(): void
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

        // Create tests
        $test1 = Test::factory()->create([
            'title' => 'History Test 1 ' . Str::random(5),
            'user_id' => $teacher->id,
            'status' => 'published',
        ]);

        $test2 = Test::factory()->create([
            'title' => 'History Test 2 ' . Str::random(5),
            'user_id' => $teacher->id,
            'status' => 'published',
        ]);

        // Create completed test attempts
        $attempt1 = TestAttempt::factory()->create([
            'user_id' => $student->id,
            'test_id' => $test1->id,
            'score' => 85,
            'started_at' => now()->subDays(5),
            'completed_at' => now()->subDays(5)->addMinutes(20),
        ]);

        $attempt2 = TestAttempt::factory()->create([
            'user_id' => $student->id,
            'test_id' => $test2->id,
            'score' => 70,
            'started_at' => now()->subDays(2),
            'completed_at' => now()->subDays(2)->addMinutes(25),
        ]);

        $this->browse(function (Browser $browser) use ($student, $test1, $test2, $attempt1, $attempt2) {
            $browser->loginAs($student)
                    ->visit('/student/dashboard')
                    ->click('@view-history')
                    ->assertPathIs('/student/history')
                    ->assertSee('My Test History')
                    ->assertSee($test1->title)
                    ->assertSee('85%')
                    ->assertSee($test2->title)
                    ->assertSee('70%')
                    ->screenshot('test-history');

            // View detailed result for a test
            $browser->click('@view-result-' . $attempt1->id)
                    ->assertPathIs('/student/test-attempts/' . $attempt1->id)
                    ->assertSee($test1->title)
                    ->assertSee('Score: 85%')
                    ->assertSee('Time Taken:')
                    ->screenshot('detailed-test-result');
        });
    }

    /**
     * Test student can receive a certificate for a high score
     */
    public function test_student_can_receive_certificate(): void
    {
        // Create a teacher user
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        // Create a student user
        $student = User::factory()->create([
            'name' => 'Certificate Student',
            'email' => 'certificate_student_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        // Create a test
        $test = Test::factory()->create([
            'title' => 'Certificate Test ' . Str::random(5),
            'description' => 'Test with certificate',
            'duration' => 30,
            'user_id' => $teacher->id,
            'status' => 'published',
            'certificate_passing_score' => 80, // Passing score for certificate
        ]);

        // Create a completed test attempt with high score
        $attempt = TestAttempt::factory()->create([
            'user_id' => $student->id,
            'test_id' => $test->id,
            'score' => 90, // Score above passing score
            'started_at' => now()->subDay(),
            'completed_at' => now()->subDay()->addMinutes(20),
        ]);

        $this->browse(function (Browser $browser) use ($student, $test, $attempt) {
            $browser->loginAs($student)
                    ->visit('/student/test-attempts/' . $attempt->id)
                    ->assertSee('Score: 90%')
                    ->assertSee('Congratulations!')
                    ->assertSee('You have earned a certificate')
                    ->screenshot('result-with-certificate')
                    
                    // Download certificate
                    ->click('@download-certificate')
                    ->assertPathIs('/student/certificates/' . $attempt->id)
                    ->screenshot('certificate-page');
                    
            // Verify certificate exists
            $this->assertDatabaseHas('certificates', [
                'test_attempt_id' => $attempt->id,
                'user_id' => $student->id,
                'test_id' => $test->id,
            ]);
        });
    }

    /**
     * Test student dashboard shows progress summary
     */
    public function test_student_dashboard_shows_progress(): void
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

        // Create tests
        $tests = Test::factory()->count(5)->create([
            'user_id' => $teacher->id,
            'status' => 'published',
        ]);

        // Create completed test attempts with varying scores
        $scores = [65, 70, 85, 90, 95];
        foreach ($tests as $index => $test) {
            TestAttempt::factory()->create([
                'user_id' => $student->id,
                'test_id' => $test->id,
                'score' => $scores[$index],
                'started_at' => now()->subDays($index + 1),
                'completed_at' => now()->subDays($index + 1)->addMinutes(rand(15, 30)),
            ]);
        }

        $this->browse(function (Browser $browser) use ($student) {
            $browser->loginAs($student)
                    ->visit('/student/dashboard')
                    ->assertSee('My Progress')
                    ->assertSee('Average Score:')
                    ->assertSee('81%') // Average of [65, 70, 85, 90, 95] = 81
                    ->assertSee('Tests Taken: 5')
                    ->assertSee('Highest Score: 95%')
                    ->assertSee('Recent Tests')
                    ->screenshot('student-dashboard-progress');
        });
    }
} 