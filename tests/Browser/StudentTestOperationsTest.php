<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\MCQTest;
use App\Models\Question;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Support\Str;

class StudentTestOperationsTest extends DuskTestCase
{
    /**
     * Test student can view available tests
     */
    public function test_student_can_view_available_tests(): void
    {
        // Create a student with random email
        $student = User::factory()->create([
            'email' => 'student_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        // Create a teacher with random email
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        // Create a test
        $test = MCQTest::create([
            'title' => 'Sample Test',
            'description' => 'This is a sample test for testing',
            'duration' => 30,
            'created_by' => $teacher->id,
        ]);

        $this->browse(function (Browser $browser) use ($student, $test) {
            $browser->loginAs($student)
                    ->visit('/dashboard')
                    ->screenshot('student-dashboard')
                    ->visit('/student/available-tests')
                    ->assertSee('Available Tests')
                    ->screenshot('student-available-tests');
        });
    }

    /**
     * Test student can view test details
     */
    public function test_student_can_view_test_details(): void
    {
        // Create a student with random email
        $student = User::factory()->create([
            'email' => 'student_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        // Create a teacher with random email
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        // Create a test
        $test = MCQTest::create([
            'title' => 'Math Test',
            'description' => 'Basic mathematics test',
            'duration' => 30,
            'created_by' => $teacher->id,
        ]);

        // Add a question to the test
        $question = Question::create([
            'test_id' => $test->id,
            'question_text' => 'What is 2+2?',
            'option_a' => '3',
            'option_b' => '4',
            'option_c' => '5',
            'option_d' => '6',
            'correct_option' => 'b',
            'marks' => 1,
        ]);

        $this->browse(function (Browser $browser) use ($student, $test) {
            $browser->loginAs($student)
                    ->visit('/student/available-tests')
                    ->screenshot('student-tests-list')
                    ->clickLink('Math Test')
                    ->assertSee('Test Details')
                    ->assertSee('Math Test')
                    ->assertSee('Duration: 30 minutes')
                    ->screenshot('student-test-details');
        });
    }
} 