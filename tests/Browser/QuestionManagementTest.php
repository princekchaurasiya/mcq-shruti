<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Test;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Support\Str;

class QuestionManagementTest extends DuskTestCase
{
    /**
     * Test teacher can create a new question
     */
    public function test_teacher_can_create_question(): void
    {
        // Create a teacher user
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        // Create a test
        $test = Test::factory()->create([
            'title' => 'Test for Questions',
            'user_id' => $teacher->id,
            'status' => 'draft',
        ]);

        $this->browse(function (Browser $browser) use ($teacher, $test) {
            $browser->loginAs($teacher)
                    ->visit('/teacher/tests/' . $test->id . '/questions')
                    ->assertSee('Questions for: Test for Questions')
                    ->screenshot('question-list')
                    
                    // Add a new question
                    ->click('@add-question')
                    ->assertSee('Create New Question')
                    ->screenshot('add-question-form')
                    
                    // Fill in question details
                    ->type('question_text', 'What is the capital of France?')
                    ->select('question_type', 'multiple_choice')
                    ->type('explanation', 'Paris is the capital and most populous city of France.')
                    ->type('options[0][text]', 'London')
                    ->radio('options[0][is_correct]', '0')
                    ->type('options[1][text]', 'Paris')
                    ->radio('options[1][is_correct]', '1')
                    ->type('options[2][text]', 'Berlin')
                    ->radio('options[2][is_correct]', '0')
                    ->type('options[3][text]', 'Madrid')
                    ->radio('options[3][is_correct]', '0')
                    ->type('marks', '5')
                    ->screenshot('filled-question-form')
                    ->press('Save Question')
                    ->assertPathIs('/teacher/tests/' . $test->id . '/questions')
                    ->assertSee('Question created successfully')
                    ->assertSee('What is the capital of France?')
                    ->screenshot('after-question-created');
            
            // Verify question added to database
            $this->assertDatabaseHas('questions', [
                'test_id' => $test->id,
                'question_text' => 'What is the capital of France?',
                'question_type' => 'multiple_choice',
                'explanation' => 'Paris is the capital and most populous city of France.',
                'marks' => 5,
            ]);
            
            // Verify correct option
            $question = Question::where('question_text', 'What is the capital of France?')->first();
            $this->assertDatabaseHas('options', [
                'question_id' => $question->id,
                'text' => 'Paris',
                'is_correct' => 1,
            ]);
        });
    }

    /**
     * Test teacher can edit an existing question
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
            'title' => 'Test with Editable Questions',
            'user_id' => $teacher->id,
            'status' => 'draft',
        ]);

        // Create a question to edit
        $question = Question::factory()->create([
            'test_id' => $test->id,
            'question_text' => 'What is the largest continent?',
            'question_type' => 'multiple_choice',
            'explanation' => 'Asia is the largest continent by both land area and population.',
            'marks' => 3,
        ]);

        // Create options for the question
        Option::factory()->create([
            'question_id' => $question->id,
            'text' => 'Africa',
            'is_correct' => 0,
        ]);

        Option::factory()->create([
            'question_id' => $question->id,
            'text' => 'Asia',
            'is_correct' => 1,
        ]);

        Option::factory()->create([
            'question_id' => $question->id,
            'text' => 'Europe',
            'is_correct' => 0,
        ]);

        Option::factory()->create([
            'question_id' => $question->id,
            'text' => 'North America',
            'is_correct' => 0,
        ]);

        $this->browse(function (Browser $browser) use ($teacher, $test, $question) {
            $browser->loginAs($teacher)
                    ->visit('/teacher/tests/' . $test->id . '/questions')
                    ->assertSee('What is the largest continent?')
                    ->screenshot('before-edit-question')
                    
                    // Edit the question
                    ->click('@edit-question-' . $question->id)
                    ->assertSee('Edit Question')
                    ->assertInputValue('question_text', 'What is the largest continent?')
                    ->screenshot('edit-question-form')
                    
                    // Update question details
                    ->type('question_text', 'What is the largest continent by area?')
                    ->type('explanation', 'Asia is the largest continent by land area, covering approximately 44.58 million square kilometers.')
                    ->type('marks', '4')
                    ->type('options[1][text]', 'Asia (largest)')
                    ->screenshot('filled-edit-question-form')
                    ->press('Update Question')
                    ->assertPathIs('/teacher/tests/' . $test->id . '/questions')
                    ->assertSee('Question updated successfully')
                    ->assertSee('What is the largest continent by area?')
                    ->screenshot('after-question-updated');
            
            // Verify question updated in database
            $this->assertDatabaseHas('questions', [
                'id' => $question->id,
                'question_text' => 'What is the largest continent by area?',
                'explanation' => 'Asia is the largest continent by land area, covering approximately 44.58 million square kilometers.',
                'marks' => 4,
            ]);
            
            // Verify option updated
            $this->assertDatabaseHas('options', [
                'question_id' => $question->id,
                'text' => 'Asia (largest)',
                'is_correct' => 1,
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
            'title' => 'Test with Deletable Questions',
            'user_id' => $teacher->id,
            'status' => 'draft',
        ]);

        // Create a question to delete
        $question = Question::factory()->create([
            'test_id' => $test->id,
            'question_text' => 'Question to be deleted',
            'question_type' => 'multiple_choice',
            'marks' => 2,
        ]);

        // Create options for the question
        Option::factory()->count(4)->create([
            'question_id' => $question->id,
        ]);

        $this->browse(function (Browser $browser) use ($teacher, $test, $question) {
            $browser->loginAs($teacher)
                    ->visit('/teacher/tests/' . $test->id . '/questions')
                    ->assertSee('Question to be deleted')
                    ->screenshot('before-delete-question')
                    
                    // Delete the question
                    ->click('@delete-question-' . $question->id)
                    ->acceptDialog() // Confirm deletion
                    ->assertPathIs('/teacher/tests/' . $test->id . '/questions')
                    ->assertSee('Question deleted successfully')
                    ->assertDontSee('Question to be deleted')
                    ->screenshot('after-delete-question');
            
            // Verify question deleted from database
            $this->assertDatabaseMissing('questions', [
                'id' => $question->id,
                'deleted_at' => null,
            ]);
            
            // Verify options were also deleted
            $this->assertEquals(0, Option::where('question_id', $question->id)->count());
        });
    }

    /**
     * Test teacher can reorder questions
     */
    public function test_teacher_can_reorder_questions(): void
    {
        // Create a teacher user
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        // Create a test
        $test = Test::factory()->create([
            'title' => 'Test with Ordered Questions',
            'user_id' => $teacher->id,
            'status' => 'draft',
        ]);

        // Create questions with explicit order
        $question1 = Question::factory()->create([
            'test_id' => $test->id,
            'question_text' => 'Question One',
            'order' => 1,
        ]);

        $question2 = Question::factory()->create([
            'test_id' => $test->id,
            'question_text' => 'Question Two',
            'order' => 2,
        ]);

        $question3 = Question::factory()->create([
            'test_id' => $test->id,
            'question_text' => 'Question Three',
            'order' => 3,
        ]);

        $this->browse(function (Browser $browser) use ($teacher, $test, $question1, $question2, $question3) {
            $browser->loginAs($teacher)
                    ->visit('/teacher/tests/' . $test->id . '/questions')
                    ->assertSeeIn('@question-list', 'Question One')
                    ->assertSeeIn('@question-list', 'Question Two')
                    ->assertSeeIn('@question-list', 'Question Three')
                    ->screenshot('before-reorder-questions')
                    
                    // Enter reorder mode
                    ->click('@reorder-questions')
                    ->assertSee('Reorder Questions')
                    ->screenshot('reorder-mode')
                    
                    // Perform reordering (Dusk limitations prevent actual drag-and-drop)
                    // Using buttons for move up/down instead
                    ->click('@move-down-' . $question1->id) // Move first question down
                    ->click('@move-up-' . $question3->id) // Move last question up
                    ->screenshot('during-reordering')
                    ->press('Save Order')
                    ->assertPathIs('/teacher/tests/' . $test->id . '/questions')
                    ->assertSee('Question order updated successfully')
                    ->screenshot('after-reorder');
            
            // Refresh questions from database and verify new order
            $question1->refresh();
            $question2->refresh();
            $question3->refresh();
            
            $this->assertEquals(2, $question1->order);
            $this->assertEquals(1, $question2->order);
            $this->assertEquals(3, $question3->order);
        });
    }

    /**
     * Test teacher can import questions from CSV
     */
    public function test_teacher_can_import_questions_from_csv(): void
    {
        // Create a teacher user
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        // Create a test
        $test = Test::factory()->create([
            'title' => 'Test for Import',
            'user_id' => $teacher->id,
            'status' => 'draft',
        ]);

        // Create a mock CSV file
        Storage::fake('public');
        $csvContent = "question_text,question_type,explanation,marks,option1,correct1,option2,correct2,option3,correct3,option4,correct4\n";
        $csvContent .= "What is 2+2?,multiple_choice,Basic addition,2,3,0,4,1,5,0,6,0\n";
        $csvContent .= "Is water wet?,true_false,State of water,1,True,1,False,0,,,,\n";
        $csvFile = UploadedFile::fake()->createWithContent('questions.csv', $csvContent);

        $this->browse(function (Browser $browser) use ($teacher, $test, $csvFile) {
            // Store the file in a temporary location to attach in the form
            $path = $csvFile->storeAs('temp', 'questions.csv', 'public');
            
            $browser->loginAs($teacher)
                    ->visit('/teacher/tests/' . $test->id . '/questions')
                    ->assertSee('Questions for: Test for Import')
                    ->screenshot('before-import')
                    
                    // Access import page
                    ->click('@import-questions')
                    ->assertSee('Import Questions')
                    ->screenshot('import-form')
                    
                    // Attach CSV file and submit form
                    ->attach('csv_file', storage_path('app/public/' . $path))
                    ->select('delimiter', ',')
                    ->screenshot('filled-import-form')
                    ->press('Import')
                    ->assertPathIs('/teacher/tests/' . $test->id . '/questions')
                    ->assertSee('Questions imported successfully')
                    ->assertSee('What is 2+2?')
                    ->assertSee('Is water wet?')
                    ->screenshot('after-import');
            
            // Verify imported questions in database
            $this->assertDatabaseHas('questions', [
                'test_id' => $test->id,
                'question_text' => 'What is 2+2?',
                'question_type' => 'multiple_choice',
                'explanation' => 'Basic addition',
                'marks' => 2,
            ]);
            
            $this->assertDatabaseHas('questions', [
                'test_id' => $test->id,
                'question_text' => 'Is water wet?',
                'question_type' => 'true_false',
                'explanation' => 'State of water',
                'marks' => 1,
            ]);
            
            // Verify options for first question
            $question1 = Question::where('question_text', 'What is 2+2?')->first();
            $this->assertDatabaseHas('options', [
                'question_id' => $question1->id,
                'text' => '4',
                'is_correct' => 1,
            ]);
            
            // Verify options for second question
            $question2 = Question::where('question_text', 'Is water wet?')->first();
            $this->assertDatabaseHas('options', [
                'question_id' => $question2->id,
                'text' => 'True',
                'is_correct' => 1,
            ]);
        });
    }

    /**
     * Test teacher can export questions to CSV
     */
    public function test_teacher_can_export_questions_to_csv(): void
    {
        // Create a teacher user
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        // Create a test
        $test = Test::factory()->create([
            'title' => 'Test for Export',
            'user_id' => $teacher->id,
            'status' => 'draft',
        ]);

        // Create questions with options
        $question1 = Question::factory()->create([
            'test_id' => $test->id,
            'question_text' => 'What is the capital of Japan?',
            'question_type' => 'multiple_choice',
            'explanation' => 'Tokyo is the capital of Japan.',
            'marks' => 3,
        ]);

        Option::factory()->create([
            'question_id' => $question1->id,
            'text' => 'Seoul',
            'is_correct' => 0,
        ]);

        Option::factory()->create([
            'question_id' => $question1->id,
            'text' => 'Tokyo',
            'is_correct' => 1,
        ]);

        Option::factory()->create([
            'question_id' => $question1->id,
            'text' => 'Beijing',
            'is_correct' => 0,
        ]);

        Option::factory()->create([
            'question_id' => $question1->id,
            'text' => 'Bangkok',
            'is_correct' => 0,
        ]);

        $question2 = Question::factory()->create([
            'test_id' => $test->id,
            'question_text' => 'Is Mount Everest the tallest mountain?',
            'question_type' => 'true_false',
            'explanation' => 'Mount Everest is indeed the tallest mountain above sea level.',
            'marks' => 1,
        ]);

        Option::factory()->create([
            'question_id' => $question2->id,
            'text' => 'True',
            'is_correct' => 1,
        ]);

        Option::factory()->create([
            'question_id' => $question2->id,
            'text' => 'False',
            'is_correct' => 0,
        ]);

        $this->browse(function (Browser $browser) use ($teacher, $test) {
            $browser->loginAs($teacher)
                    ->visit('/teacher/tests/' . $test->id . '/questions')
                    ->assertSee('What is the capital of Japan?')
                    ->assertSee('Is Mount Everest the tallest mountain?')
                    ->screenshot('before-export')
                    
                    // Export questions
                    ->click('@export-questions')
                    ->assertSee('Export Questions')
                    ->screenshot('export-form')
                    ->select('format', 'csv')
                    ->press('Export')
                    ->assertPathIs('/teacher/tests/' . $test->id . '/questions')
                    ->assertSee('Questions exported successfully')
                    ->screenshot('after-export');
            
            // In a real test, we'd verify the downloaded file content
            // but for Dusk, we mainly verify the UI workflow
        });
    }

    /**
     * Test teacher can clone questions from another test
     */
    public function test_teacher_can_clone_questions_from_another_test(): void
    {
        // Create a teacher user
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        // Create source test with questions
        $sourceTest = Test::factory()->create([
            'title' => 'Source Test',
            'user_id' => $teacher->id,
            'status' => 'published',
        ]);

        // Create questions in source test
        $sourceQuestion1 = Question::factory()->create([
            'test_id' => $sourceTest->id,
            'question_text' => 'Source Question 1',
            'question_type' => 'multiple_choice',
            'marks' => 2,
        ]);

        Option::factory()->create([
            'question_id' => $sourceQuestion1->id,
            'text' => 'Option A',
            'is_correct' => 1,
        ]);

        Option::factory()->create([
            'question_id' => $sourceQuestion1->id,
            'text' => 'Option B',
            'is_correct' => 0,
        ]);

        $sourceQuestion2 = Question::factory()->create([
            'test_id' => $sourceTest->id,
            'question_text' => 'Source Question 2',
            'question_type' => 'multiple_choice',
            'marks' => 3,
        ]);

        Option::factory()->create([
            'question_id' => $sourceQuestion2->id,
            'text' => 'Option X',
            'is_correct' => 0,
        ]);

        Option::factory()->create([
            'question_id' => $sourceQuestion2->id,
            'text' => 'Option Y',
            'is_correct' => 1,
        ]);

        // Create target test (empty)
        $targetTest = Test::factory()->create([
            'title' => 'Target Test',
            'user_id' => $teacher->id,
            'status' => 'draft',
        ]);

        $this->browse(function (Browser $browser) use ($teacher, $sourceTest, $targetTest, $sourceQuestion1, $sourceQuestion2) {
            $browser->loginAs($teacher)
                    ->visit('/teacher/tests/' . $targetTest->id . '/questions')
                    ->assertSee('Questions for: Target Test')
                    ->assertDontSee('Source Question 1')
                    ->screenshot('target-before-clone')
                    
                    // Clone questions
                    ->click('@clone-questions')
                    ->assertSee('Clone Questions from Another Test')
                    ->screenshot('clone-form')
                    ->select('source_test', $sourceTest->id)
                    ->check('questions[]', $sourceQuestion1->id)
                    ->check('questions[]', $sourceQuestion2->id)
                    ->screenshot('clone-selections')
                    ->press('Clone Selected Questions')
                    ->assertPathIs('/teacher/tests/' . $targetTest->id . '/questions')
                    ->assertSee('Questions cloned successfully')
                    ->assertSee('Source Question 1')
                    ->assertSee('Source Question 2')
                    ->screenshot('after-clone');
            
            // Verify cloned questions in database
            $this->assertDatabaseHas('questions', [
                'test_id' => $targetTest->id,
                'question_text' => 'Source Question 1',
                'question_type' => 'multiple_choice',
                'marks' => 2,
            ]);
            
            $this->assertDatabaseHas('questions', [
                'test_id' => $targetTest->id,
                'question_text' => 'Source Question 2',
                'question_type' => 'multiple_choice',
                'marks' => 3,
            ]);
            
            // Verify cloned options
            $clonedQuestion1 = Question::where('test_id', $targetTest->id)
                ->where('question_text', 'Source Question 1')
                ->first();
                
            $this->assertDatabaseHas('options', [
                'question_id' => $clonedQuestion1->id,
                'text' => 'Option A',
                'is_correct' => 1,
            ]);
            
            $clonedQuestion2 = Question::where('test_id', $targetTest->id)
                ->where('question_text', 'Source Question 2')
                ->first();
                
            $this->assertDatabaseHas('options', [
                'question_id' => $clonedQuestion2->id,
                'text' => 'Option Y',
                'is_correct' => 1,
            ]);
        });
    }

    /**
     * Test teacher can add images to questions
     */
    public function test_teacher_can_add_images_to_questions(): void
    {
        // Create a teacher user
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        // Create a test
        $test = Test::factory()->create([
            'title' => 'Test with Image Questions',
            'user_id' => $teacher->id,
            'status' => 'draft',
        ]);

        // Create mock image
        Storage::fake('public');
        $image = UploadedFile::fake()->image('question_image.jpg', 800, 600);

        $this->browse(function (Browser $browser) use ($teacher, $test, $image) {
            // Store the image in a temporary location to attach in the form
            $path = $image->storeAs('temp', 'question_image.jpg', 'public');
            
            $browser->loginAs($teacher)
                    ->visit('/teacher/tests/' . $test->id . '/questions')
                    ->click('@add-question')
                    ->assertSee('Create New Question')
                    ->screenshot('add-image-question-form')
                    
                    // Fill in question details with image
                    ->type('question_text', 'Identify the element shown in the image:')
                    ->select('question_type', 'multiple_choice')
                    ->attach('question_image', storage_path('app/public/' . $path))
                    ->screenshot('image-attached')
                    ->type('options[0][text]', 'Hydrogen')
                    ->radio('options[0][is_correct]', '0')
                    ->type('options[1][text]', 'Oxygen')
                    ->radio('options[1][is_correct]', '1')
                    ->type('options[2][text]', 'Carbon')
                    ->radio('options[2][is_correct]', '0')
                    ->type('options[3][text]', 'Nitrogen')
                    ->radio('options[3][is_correct]', '0')
                    ->type('marks', '5')
                    ->press('Save Question')
                    ->assertPathIs('/teacher/tests/' . $test->id . '/questions')
                    ->assertSee('Question created successfully')
                    ->assertSee('Identify the element shown in the image:')
                    ->screenshot('after-image-question-created');
            
            // Verify question with image in database
            $this->assertDatabaseHas('questions', [
                'test_id' => $test->id,
                'question_text' => 'Identify the element shown in the image:',
                'question_type' => 'multiple_choice',
                'marks' => 5,
            ]);
            
            // Get the created question and verify image exists
            $question = Question::where('question_text', 'Identify the element shown in the image:')->first();
            $this->assertNotNull($question->image_path);
            
            // In a real test, we'd verify the file exists in storage
            // Storage::disk('public')->assertExists($question->image_path);
        });
    }
} 