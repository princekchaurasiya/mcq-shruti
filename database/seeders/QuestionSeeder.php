<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MCQTest;
use App\Models\Question;
use Illuminate\Support\Facades\Log;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all tests
        $tests = MCQTest::all();
        
        if ($tests->isEmpty()) {
            $this->command->error('Please run TestSeeder first to create tests');
            return;
        }
        
        // General Knowledge Test Questions
        $generalKnowledgeTest = MCQTest::where('title', 'General Knowledge Test')->first();
        if ($generalKnowledgeTest) {
            $this->addGeneralKnowledgeQuestions($generalKnowledgeTest);
        }
        
        // Science Quiz Questions
        $scienceTest = MCQTest::where('title', 'Science Quiz')->first();
        if ($scienceTest) {
            $this->addScienceQuestions($scienceTest);
        }
        
        // Math Test Questions
        $mathTest = MCQTest::where('title', 'Advanced Mathematics')->first();
        if ($mathTest) {
            $this->addMathQuestions($mathTest);
        }
        
        $this->command->info('Added questions to all tests');
    }
    
    private function addGeneralKnowledgeQuestions($test)
    {
        $questions = [
            [
                'question_text' => 'What is the capital of France?',
                'options' => ['London', 'Berlin', 'Paris', 'Madrid'],
                'correct_option' => [2], // Paris
                'explanation' => 'Paris is the capital city of France.',
                'marks' => 1
            ],
            [
                'question_text' => 'Which planet is known as the Red Planet?',
                'options' => ['Venus', 'Mars', 'Jupiter', 'Saturn'],
                'correct_option' => [1], // Mars
                'explanation' => 'Mars is called the Red Planet due to its reddish appearance.',
                'marks' => 1
            ],
            [
                'question_text' => 'Who wrote "Romeo and Juliet"?',
                'options' => ['Charles Dickens', 'William Shakespeare', 'Jane Austen', 'Mark Twain'],
                'correct_option' => [1], // William Shakespeare
                'explanation' => 'Romeo and Juliet is a tragedy written by William Shakespeare.',
                'marks' => 1
            ],
            [
                'question_text' => 'Which of the following are primary colors?',
                'options' => ['Red', 'Green', 'Blue', 'Orange'],
                'correct_option' => [0, 2], // Red and Blue
                'explanation' => 'Red, Blue, and Yellow are the three primary colors.',
                'marks' => 2
            ],
            [
                'question_text' => 'What year did World War II end?',
                'options' => ['1943', '1945', '1947', '1950'],
                'correct_option' => [1], // 1945
                'explanation' => 'World War II ended in 1945 with the surrender of Japan.',
                'marks' => 1
            ]
        ];
        
        $this->createQuestions($test, $questions);
    }
    
    private function addScienceQuestions($test)
    {
        $questions = [
            [
                'question_text' => 'What is the chemical symbol for water?',
                'options' => ['H2O', 'CO2', 'O2', 'NaCl'],
                'correct_option' => [0], // H2O
                'explanation' => 'Water is composed of two hydrogen atoms and one oxygen atom.',
                'marks' => 1
            ],
            [
                'question_text' => 'Which of the following are noble gases?',
                'options' => ['Helium', 'Nitrogen', 'Neon', 'Oxygen'],
                'correct_option' => [0, 2], // Helium and Neon
                'explanation' => 'Noble gases include helium, neon, argon, krypton, xenon, and radon.',
                'marks' => 2
            ],
            [
                'question_text' => 'What is the largest organ in the human body?',
                'options' => ['Heart', 'Liver', 'Skin', 'Brain'],
                'correct_option' => [2], // Skin
                'explanation' => 'The skin is the largest organ of the human body.',
                'marks' => 1
            ],
            [
                'question_text' => 'Which scientist proposed the theory of relativity?',
                'options' => ['Isaac Newton', 'Albert Einstein', 'Niels Bohr', 'Galileo Galilei'],
                'correct_option' => [1], // Albert Einstein
                'explanation' => 'Albert Einstein published the theory of relativity in the early 20th century.',
                'marks' => 1
            ],
            [
                'question_text' => 'What is the process by which plants make their food called?',
                'options' => ['Respiration', 'Photosynthesis', 'Digestion', 'Fermentation'],
                'correct_option' => [1], // Photosynthesis
                'explanation' => 'Photosynthesis is the process used by plants to convert light energy into chemical energy.',
                'marks' => 1
            ]
        ];
        
        $this->createQuestions($test, $questions);
    }
    
    private function addMathQuestions($test)
    {
        $questions = [
            [
                'question_text' => 'What is the value of π (pi) to two decimal places?',
                'options' => ['3.14', '3.16', '3.12', '3.18'],
                'correct_option' => [0], // 3.14
                'explanation' => 'The value of π to two decimal places is 3.14.',
                'marks' => 1
            ],
            [
                'question_text' => 'If x² + 3x + 2 = 0, what are the values of x?',
                'options' => ['1 and 2', '-1 and -2', '1 and -2', '-1 and 2'],
                'correct_option' => [1], // -1 and -2
                'explanation' => 'Factoring the equation gives (x+1)(x+2) = 0, so x = -1 or x = -2.',
                'marks' => 2
            ],
            [
                'question_text' => 'Which of the following are prime numbers?',
                'options' => ['11', '15', '23', '27'],
                'correct_option' => [0, 2], // 11 and 23
                'explanation' => '11 and 23 are prime numbers. 15 is divisible by 3 and 5, and 27 is divisible by 3 and 9.',
                'marks' => 2
            ],
            [
                'question_text' => 'What is the derivative of f(x) = x²?',
                'options' => ['f\'(x) = x', 'f\'(x) = 2x', 'f\'(x) = x²', 'f\'(x) = 2'],
                'correct_option' => [1], // 2x
                'explanation' => 'The derivative of x² is 2x.',
                'marks' => 1
            ],
            [
                'question_text' => 'What is the value of log₁₀(100)?',
                'options' => ['1', '2', '10', '100'],
                'correct_option' => [1], // 2
                'explanation' => 'log₁₀(100) = log₁₀(10²) = 2',
                'marks' => 1
            ],
            [
                'question_text' => 'Solve the inequality: 2x - 5 > 7',
                'options' => ['x > 6', 'x > 5', 'x < 6', 'x < -1'],
                'correct_option' => [0], // x > 6
                'explanation' => '2x - 5 > 7, so 2x > 12, which means x > 6',
                'marks' => 1
            ]
        ];
        
        $this->createQuestions($test, $questions);
    }
    
    private function createQuestions($test, $questions)
    {
        foreach ($questions as $questionData) {
            $question = Question::firstOrCreate(
                [
                    'mcq_test_id' => $test->id,
                    'question_text' => $questionData['question_text']
                ],
                [
                    'options' => $questionData['options'],
                    'correct_option' => $questionData['correct_option'],
                    'explanation' => $questionData['explanation'],
                    'marks' => $questionData['marks']
                ]
            );
            
            Log::info("Created question: {$questionData['question_text']} for test: {$test->title}");
        }
    }
} 