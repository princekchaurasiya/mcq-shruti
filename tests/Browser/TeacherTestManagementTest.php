<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Test;
use App\Models\Question;
use App\Models\Option;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Support\Str;

class TeacherTestManagementTest extends DuskTestCase
{
    /**
     * Test teacher can create a new test
     */
    public function test_teacher_can_create_test(): void
    {
        // Create a teacher user
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        $testTitle = 'Test ' . Str::random(8);
        $testDescription = 'Description for ' . $testTitle;
        $testDuration = 60;

        $this->browse(function (Browser $browser) use ($teacher, $testTitle, $testDescription, $testDuration) {
            $browser->loginAs($teacher)
                    ->visit('/teacher/dashboard')
                    ->screenshot('teacher-dashboard')
                    ->click('@create-test')
                    ->assertPathIs('/teacher/tests/create')
                    ->screenshot('create-test-form')
                    ->type('title', $testTitle)
                    ->type('description', $testDescription)
                    ->type('duration', $testDuration)
                    ->screenshot('filled-test-form')
                    ->press('Create Test')
                    ->assertPathIs('/teacher/tests/*') // Redirects to the test detail page
                    ->assertSee($testTitle)
                    ->assertSee($testDescription)
                    ->assertSee($testDuration . ' minutes')
                    ->screenshot('after-test-created');

            // Verify test was created in database
            $this->assertDatabaseHas('tests', [
                'title' => $testTitle,
                'description' => $testDescription,
                'duration' => $testDuration,
                'user_id' => $teacher->id,
            ]);
        });
    }

    /**
     * Test teacher can add questions to a test
     */
    public function test_teacher_can_add_questions(): void
    {
        // Create a teacher user
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        // Create a test
        $test = Test::factory()->create([
            'title' => 'Test for Adding Questions ' . Str::random(5),
            'user_id' => $teacher->id,
            'status' => 'draft',
        ]);

        $questionText = 'What is the capital of France?';
        $options = [
            ['text' => 'Paris', 'is_correct' => true],
            ['text' => 'London', 'is_correct' => false],
            ['text' => 'Berlin', 'is_correct' => false],
            ['text' => 'Madrid', 'is_correct' => false],
        ];

        $this->browse(function (Browser $browser) use ($teacher, $test, $questionText, $options) {
            $browser->loginAs($teacher)
                    ->visit('/teacher/tests/' . $test->id)
                    ->screenshot('test-detail-before-questions')
                    ->click('@add-question')
                    ->assertSee('Add Question')
                    ->screenshot('add-question-form')
                    ->type('text', $questionText)
                    ->select('type', 'multiple_choice')
                    ->type('points', 5)
                    ->screenshot('question-details-filled');

            // Add options
            for ($i = 0; $i < count($options); $i++) {
                $browser->type('options[' . $i . '][text]', $options[$i]['text']);
                if ($options[$i]['is_correct']) {
                    $browser->check('options[' . $i . '][is_correct]');
                }
            }

            $browser->screenshot('options-filled')
                    ->press('Add Question')
                    ->assertPathIs('/teacher/tests/' . $test->id)
                    ->assertSee('Question added successfully')
                    ->assertSee($questionText)
                    ->screenshot('after-question-added');

            // Verify question was created in database
            $question = Question::where('test_id', $test->id)
                                ->where('text', $questionText)
                                ->first();

            $this->assertNotNull($question);
            $this->assertEquals(5, $question->points);

            // Verify options were created
            foreach ($options as $option) {
                $this->assertDatabaseHas('options', [
                    'question_id' => $question->id,
                    'text' => $option['text'],
                    'is_correct' => $option['is_correct'],
                ]);
            }
        });
    }

    /**
     * Test teacher can edit a test
     */
    public function test_teacher_can_edit_test(): void
    {
        // Create a teacher user
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        // Create a test
        $test = Test::factory()->create([
            'title' => 'Original Test Title ' . Str::random(5),
            'description' => 'Original Description',
            'duration' => 45,
            'user_id' => $teacher->id,
            'status' => 'draft',
        ]);

        $updatedTitle = 'Updated Test Title ' . Str::random(5);
        $updatedDescription = 'Updated test description';
        $updatedDuration = 60;

        $this->browse(function (Browser $browser) use ($teacher, $test, $updatedTitle, $updatedDescription, $updatedDuration) {
            $browser->loginAs($teacher)
                    ->visit('/teacher/tests/' . $test->id)
                    ->screenshot('test-detail-before-edit')
                    ->click('@edit-test')
                    ->assertPathIs('/teacher/tests/' . $test->id . '/edit')
                    ->assertInputValue('title', $test->title)
                    ->assertInputValue('description', $test->description)
                    ->assertInputValue('duration', $test->duration)
                    ->screenshot('edit-test-form')
                    ->type('title', $updatedTitle)
                    ->type('description', $updatedDescription)
                    ->type('duration', $updatedDuration)
                    ->screenshot('edit-test-form-filled')
                    ->press('Update Test')
                    ->assertPathIs('/teacher/tests/' . $test->id)
                    ->assertSee('Test updated successfully')
                    ->assertSee($updatedTitle)
                    ->assertSee($updatedDescription)
                    ->assertSee($updatedDuration . ' minutes')
                    ->screenshot('after-test-updated');

            // Verify test was updated in database
            $this->assertDatabaseHas('tests', [
                'id' => $test->id,
                'title' => $updatedTitle,
                'description' => $updatedDescription,
                'duration' => $updatedDuration,
            ]);
        });
    }

    /**
     * Test teacher can edit a question
     */
    public function test_teacher_can_edit_question(): void
    {
        // Create a teacher user
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        // Create a test
        $test = Test::factory()->create([
            'title' => 'Test for Editing Question ' . Str::random(5),
            'user_id' => $teacher->id,
            'status' => 'draft',
        ]);

        // Create a question with options
        $question = Question::factory()->create([
            'test_id' => $test->id,
            'text' => 'Original question text',
            'type' => 'multiple_choice',
            'points' => 3,
        ]);

        // Create options
        $options = [
            Option::factory()->create([
                'question_id' => $question->id,
                'text' => 'Option A',
                'is_correct' => true,
            ]),
            Option::factory()->create([
                'question_id' => $question->id,
                'text' => 'Option B',
                'is_correct' => false,
            ]),
            Option::factory()->create([
                'question_id' => $question->id,
                'text' => 'Option C',
                'is_correct' => false,
            ]),
        ];

        $updatedQuestionText = 'Updated question text ' . Str::random(5);
        $updatedPoints = 5;

        $this->browse(function (Browser $browser) use ($teacher, $test, $question, $options, $updatedQuestionText, $updatedPoints) {
            $browser->loginAs($teacher)
                    ->visit('/teacher/tests/' . $test->id)
                    ->screenshot('test-detail-with-question')
                    ->click('@edit-question-' . $question->id)
                    ->assertPathIs('/teacher/questions/' . $question->id . '/edit')
                    ->assertInputValue('text', $question->text)
                    ->assertInputValue('points', $question->points)
                    ->screenshot('edit-question-form')
                    ->type('text', $updatedQuestionText)
                    ->type('points', $updatedPoints)
                    // Update first option to be incorrect and second option to be correct
                    ->uncheck('options[0][is_correct]')
                    ->check('options[1][is_correct]')
                    ->screenshot('edit-question-form-filled')
                    ->press('Update Question')
                    ->assertPathIs('/teacher/tests/' . $test->id)
                    ->assertSee('Question updated successfully')
                    ->assertSee($updatedQuestionText)
                    ->screenshot('after-question-updated');

            // Verify question was updated in database
            $this->assertDatabaseHas('questions', [
                'id' => $question->id,
                'text' => $updatedQuestionText,
                'points' => $updatedPoints,
            ]);

            // Verify options were updated
            $this->assertDatabaseHas('options', [
                'id' => $options[0]->id,
                'is_correct' => false,
            ]);

            $this->assertDatabaseHas('options', [
                'id' => $options[1]->id,
                'is_correct' => true,
            ]);
        });
    }

    /**
     * Test teacher can delete a question
     */
    public function test_teacher_can_delete_question(): void
    {
        // Create a teacher user
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        // Create a test
        $test = Test::factory()->create([
            'title' => 'Test for Deleting Question ' . Str::random(5),
            'user_id' => $teacher->id,
            'status' => 'draft',
        ]);

        // Create a question
        $question = Question::factory()->create([
            'test_id' => $test->id,
            'text' => 'Question to be deleted ' . Str::random(5),
        ]);

        // Create options
        Option::factory()->count(3)->create([
            'question_id' => $question->id,
        ]);

        $this->browse(function (Browser $browser) use ($teacher, $test, $question) {
            $browser->loginAs($teacher)
                    ->visit('/teacher/tests/' . $test->id)
                    ->screenshot('test-detail-before-delete-question')
                    ->assertSee($question->text)
                    ->click('@delete-question-' . $question->id)
                    ->acceptDialog() // Confirm the deletion
                    ->assertPathIs('/teacher/tests/' . $test->id)
                    ->assertSee('Question deleted successfully')
                    ->assertDontSee($question->text)
                    ->screenshot('after-question-deleted');

            // Verify question was deleted from database
            $this->assertDatabaseMissing('questions', [
                'id' => $question->id,
            ]);

            // Verify options were also deleted
            $this->assertDatabaseMissing('options', [
                'question_id' => $question->id,
            ]);
        });
    }

    /**
     * Test teacher can publish a test
     */
    public function test_teacher_can_publish_test(): void
    {
        // Create a teacher user
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        // Create a test
        $test = Test::factory()->create([
            'title' => 'Test for Publishing ' . Str::random(5),
            'user_id' => $teacher->id,
            'status' => 'draft',
        ]);

        // Add a question to the test
        Question::factory()->create([
            'test_id' => $test->id,
        ]);

        $this->browse(function (Browser $browser) use ($teacher, $test) {
            $browser->loginAs($teacher)
                    ->visit('/teacher/tests/' . $test->id)
                    ->screenshot('test-detail-before-publish')
                    ->assertSee('Draft')
                    ->press('@publish-test')
                    ->assertPathIs('/teacher/tests/' . $test->id)
                    ->assertSee('Test published successfully')
                    ->assertSee('Published')
                    ->screenshot('after-test-published');

            // Verify test status was updated in database
            $this->assertDatabaseHas('tests', [
                'id' => $test->id,
                'status' => 'published',
            ]);
        });
    }

    /**
     * Test teacher can view test results
     */
    public function test_teacher_can_view_test_results(): void
    {
        // Create a teacher user
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        // Create a test
        $test = Test::factory()->create([
            'title' => 'Test with Results ' . Str::random(5),
            'user_id' => $teacher->id,
            'status' => 'published',
        ]);

        // Create students
        $students = User::factory()->count(3)->create([
            'role' => 'student',
        ]);

        // Create test attempts for each student
        foreach ($students as $index => $student) {
            TestAttempt::factory()->create([
                'user_id' => $student->id,
                'test_id' => $test->id,
                'started_at' => now()->subDays($index + 1),
                'completed_at' => now()->subDays($index + 1)->addMinutes(30),
                'score' => 60 + ($index * 15), // 60, 75, 90
            ]);
        }

        $this->browse(function (Browser $browser) use ($teacher, $test, $students) {
            $browser->loginAs($teacher)
                    ->visit('/teacher/tests/' . $test->id . '/results')
                    ->screenshot('test-results')
                    ->assertSee('Test Results: ' . $test->title)
                    ->assertSee($students[0]->name)
                    ->assertSee('60%') // First student's score
                    ->assertSee($students[1]->name)
                    ->assertSee('75%') // Second student's score
                    ->assertSee($students[2]->name)
                    ->assertSee('90%') // Third student's score
                    ->assertSee('Average Score: 75%') // (60 + 75 + 90) / 3 = 75
                    ->screenshot('detailed-test-results');

            // View individual student result
            $browser->click('@view-result-' . $students[2]->id . '-' . $test->id)
                    ->assertPathIs('/teacher/test-attempts/*')
                    ->assertSee($students[2]->name)
                    ->assertSee('Score: 90%')
                    ->assertSee('Time Taken:')
                    ->screenshot('individual-student-result');
        });
    }

    /**
     * Test teacher can duplicate a test
     */
    public function test_teacher_can_duplicate_test(): void
    {
        // Create a teacher user
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        // Create a test with question and options
        $originalTest = Test::factory()->create([
            'title' => 'Original Test ' . Str::random(5),
            'description' => 'Original test description',
            'duration' => 45,
            'user_id' => $teacher->id,
            'status' => 'published',
        ]);

        // Add questions
        $question = Question::factory()->create([
            'test_id' => $originalTest->id,
            'text' => 'Sample question ' . Str::random(5),
            'type' => 'multiple_choice',
            'points' => 5,
        ]);

        // Add options
        Option::factory()->create([
            'question_id' => $question->id,
            'text' => 'Option A',
            'is_correct' => true,
        ]);

        Option::factory()->create([
            'question_id' => $question->id,
            'text' => 'Option B',
            'is_correct' => false,
        ]);

        $this->browse(function (Browser $browser) use ($teacher, $originalTest, $question) {
            $browser->loginAs($teacher)
                    ->visit('/teacher/tests/' . $originalTest->id)
                    ->screenshot('original-test-detail')
                    ->click('@duplicate-test')
                    ->acceptDialog() // Confirm duplication
                    ->assertPathIs('/teacher/tests/*')
                    ->assertUrlDoesntContain('/teacher/tests/' . $originalTest->id)
                    ->assertSee('Test duplicated successfully')
                    ->assertSee('Copy of ' . $originalTest->title)
                    ->assertSee('Draft') // Duplicated test should be a draft
                    ->assertSee($question->text) // Question should be duplicated
                    ->screenshot('duplicated-test');

            // Verify duplicated test in database
            $duplicatedTest = Test::where('title', 'Copy of ' . $originalTest->title)
                                  ->where('user_id', $teacher->id)
                                  ->first();

            $this->assertNotNull($duplicatedTest);
            $this->assertEquals($originalTest->description, $duplicatedTest->description);
            $this->assertEquals($originalTest->duration, $duplicatedTest->duration);
            $this->assertEquals('draft', $duplicatedTest->status);

            // Verify questions were duplicated
            $duplicatedQuestion = Question::where('test_id', $duplicatedTest->id)
                                         ->where('text', $question->text)
                                         ->first();

            $this->assertNotNull($duplicatedQuestion);
            $this->assertEquals($question->type, $duplicatedQuestion->type);
            $this->assertEquals($question->points, $duplicatedQuestion->points);

            // Verify options were duplicated
            $optionCount = Option::where('question_id', $duplicatedQuestion->id)->count();
            $this->assertEquals(2, $optionCount);
        });
    }
} 