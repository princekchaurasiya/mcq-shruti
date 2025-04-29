<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MCQTest;
use App\Models\Question;
use App\Models\TestAttempt;
use App\Models\StudentResponse;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TestAttemptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get student user
        $student = User::where('email', 'student@example.com')->first();
        
        if (!$student) {
            $this->command->error('Please run UserSeeder first to create a student');
            return;
        }
        
        // Get tests
        $completedTest = MCQTest::where('title', 'Science Quiz')->first();
        $activeTest = MCQTest::where('title', 'General Knowledge Test')->first();
        
        if (!$completedTest || !$activeTest) {
            $this->command->error('Please run TestSeeder first to create tests');
            return;
        }
        
        // Create a completed test attempt for Science Quiz
        if ($completedTest) {
            $testAttempt = TestAttempt::firstOrCreate(
                [
                    'user_id' => $student->id,
                    'mcq_test_id' => $completedTest->id
                ],
                [
                    'started_at' => Carbon::now()->subDays(9),
                    'completed_at' => Carbon::now()->subDays(9)->addMinutes(20),
                    'score' => 80 // We'll calculate this more accurately after adding responses
                ]
            );
            
            $this->createCompletedTestResponses($testAttempt, $completedTest);
            $this->updateTestScore($testAttempt);
        }
        
        // Create an active test attempt for General Knowledge Test
        if ($activeTest) {
            $testAttempt = TestAttempt::firstOrCreate(
                [
                    'user_id' => $student->id,
                    'mcq_test_id' => $activeTest->id
                ],
                [
                    'started_at' => Carbon::now()->subMinutes(15),
                    'completed_at' => null,
                    'score' => null
                ]
            );
            
            $this->createInProgressTestResponses($testAttempt, $activeTest);
        }
        
        $this->command->info('Created test attempts for the student');
    }
    
    private function createCompletedTestResponses($testAttempt, $test)
    {
        // Get all questions for test
        $questions = Question::where('mcq_test_id', $test->id)->get();
        
        foreach ($questions as $index => $question) {
            // For demonstration purposes, student gets most questions right
            $isCorrect = $index < 4; // Get first 4 questions right
            
            // Determine the selected answer - if it's correct or simulated wrong
            $selectedOption = $isCorrect 
                ? $question->correct_option 
                : $this->getIncorrectAnswer($question);
            
            StudentResponse::firstOrCreate(
                [
                    'test_attempt_id' => $testAttempt->id,
                    'question_id' => $question->id
                ],
                [
                    'selected_option' => json_encode($selectedOption),
                    'is_correct' => $isCorrect,
                    'is_marked_for_review' => false
                ]
            );
        }
    }
    
    private function createInProgressTestResponses($testAttempt, $test)
    {
        // Get all questions for test
        $questions = Question::where('mcq_test_id', $test->id)->get();
        
        foreach ($questions as $index => $question) {
            // For in-progress test, answer first 2 questions
            if ($index < 2) {
                // Student gets first question right, second question wrong
                $isCorrect = $index === 0;
                
                // Determine the selected answer
                $selectedOption = $isCorrect 
                    ? $question->correct_option 
                    : $this->getIncorrectAnswer($question);
                
                StudentResponse::firstOrCreate(
                    [
                        'test_attempt_id' => $testAttempt->id,
                        'question_id' => $question->id
                    ],
                    [
                        'selected_option' => json_encode($selectedOption),
                        'is_correct' => $isCorrect,
                        'is_marked_for_review' => false
                    ]
                );
            } elseif ($index === 2) {
                // Mark the 3rd question for review but don't answer
                StudentResponse::firstOrCreate(
                    [
                        'test_attempt_id' => $testAttempt->id,
                        'question_id' => $question->id
                    ],
                    [
                        'selected_option' => null,
                        'is_correct' => null,
                        'is_marked_for_review' => true
                    ]
                );
            }
            // Leave the rest unanswered
        }
    }
    
    private function getIncorrectAnswer($question)
    {
        if (!isset($question->options) || count($question->options) < 2) {
            return [0]; // Default fallback
        }
        
        // Find an option that's not in the correct_option array
        $wrongOptions = [];
        foreach (array_keys($question->options) as $index) {
            if (!in_array($index, $question->correct_option)) {
                $wrongOptions[] = $index;
            }
        }
        
        if (empty($wrongOptions)) {
            return [0]; // Default fallback
        }
        
        // Return a random wrong option
        return [array_values($wrongOptions)[0]];
    }
    
    private function updateTestScore($testAttempt)
    {
        // Calculate actual score based on responses
        $correctResponses = StudentResponse::where('test_attempt_id', $testAttempt->id)
            ->where('is_correct', true)
            ->count();
            
        $totalQuestions = StudentResponse::where('test_attempt_id', $testAttempt->id)->count();
        
        if ($totalQuestions > 0) {
            $score = ($correctResponses / $totalQuestions) * 100;
            $testAttempt->score = $score;
            $testAttempt->save();
        }
    }
} 