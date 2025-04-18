<?php

namespace App\Services;

class MCQQuestionParser
{
    public function parseFromText(string $text): array
    {
        $questions = [];
        $currentQuestion = null;
        $options = [];
        
        // Split text into lines and remove empty lines
        $lines = array_filter(explode("\n", $text), function($line) {
            return trim($line) !== '';
        });
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip empty lines
            if (empty($line)) {
                continue;
            }
            
            // Check if line is an option (starts with a), b), c), d) or similar)
            if (preg_match('/^[a-d]\)?\s+(.+)$/i', $line, $matches)) {
                $optionText = trim($matches[1]);
                $optionKey = strtolower(substr($line, 0, 1));
                $options[$optionKey] = $optionText;
                
                // If we have all options, add the question
                if (count($options) === 4 && $currentQuestion) {
                    $questions[] = [
                        'question_text' => $currentQuestion,
                        'options' => $options,
                        'correct_option' => null // Teacher will need to mark the correct option
                    ];
                    $currentQuestion = null;
                    $options = [];
                }
            } else {
                // If we have a previous question that wasn't added, add it
                if ($currentQuestion && !empty($options)) {
                    $questions[] = [
                        'question_text' => $currentQuestion,
                        'options' => $options,
                        'correct_option' => null
                    ];
                }
                
                // Start a new question
                $currentQuestion = $line;
                $options = [];
            }
        }
        
        // Add the last question if exists
        if ($currentQuestion && !empty($options)) {
            $questions[] = [
                'question_text' => $currentQuestion,
                'options' => $options,
                'correct_option' => null
            ];
        }
        
        return $questions;
    }
} 