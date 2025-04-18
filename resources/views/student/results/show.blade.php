<x-student-layout>
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Test Result Details</h1>
            <a href="{{ route('results.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                <i class="bi bi-arrow-left me-2"></i> Back to Results
            </a>
        </div>

        <!-- Result Summary Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">{{ $result->mcqTest->title }}</h6>
                
                <!-- Score Badge -->
                <div class="score-badge rounded px-3 py-2 {{ $result->score >= $result->mcqTest->passing_percentage ? 'bg-success' : 'bg-danger' }} text-white">
                    <strong>Score: {{ number_format($result->score, 1) }}%</strong>
                    <span class="ms-2 badge bg-light text-dark">
                        {{ $result->score >= $result->mcqTest->passing_percentage ? 'PASSED' : 'FAILED' }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>Subject:</strong> {{ $result->mcqTest->subject->name }}</p>
                        <p><strong>Total Questions:</strong> {{ $result->mcqTest->questions->count() }}</p>
                        <p><strong>Duration:</strong> {{ $result->mcqTest->duration_minutes }} minutes</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Completed At:</strong> {{ $result->completed_at->format('M d, Y H:i') }}</p>
                        <p><strong>Time Taken:</strong> {{ round($result->created_at->diffInMinutes($result->completed_at)) }} minutes</p>
                        <p><strong>Pass Requirement:</strong> {{ $result->mcqTest->passing_percentage }}%</p>
                    </div>
                </div>
                
                <!-- Performance Summary -->
                <div class="performance-summary border rounded p-3 mb-4">
                    <div class="d-flex justify-content-between mb-3">
                        <h6 class="font-weight-bold mb-0">Performance Summary</h6>
                        <div>
                            <button id="show-all-btn" class="btn btn-sm btn-outline-secondary active">All Questions</button>
                            <button id="show-correct-btn" class="btn btn-sm btn-outline-success">Correct Only</button>
                            <button id="show-incorrect-btn" class="btn btn-sm btn-outline-danger">Incorrect Only</button>
                        </div>
                    </div>
                    
                    @php
                        $totalQuestions = $result->mcqTest->questions->count();
                        $correct = $answers->where('is_correct', true)->count();
                        $incorrect = $answers->where('is_correct', false)->count();
                        
                        // Check if review column exists
                        $reviewCount = 0;
                        try {
                            $reviewCount = $answers->where('is_marked_for_review', true)->count();
                        } catch (\Exception $e) {
                            // Column might not exist
                        }
                        
                        $correctPercent = $totalQuestions > 0 ? ($correct / $totalQuestions) * 100 : 0;
                        $incorrectPercent = $totalQuestions > 0 ? ($incorrect / $totalQuestions) * 100 : 0;
                        $reviewPercent = $totalQuestions > 0 ? ($reviewCount / $totalQuestions) * 100 : 0;
                    @endphp
                    
                    <!-- Progress bar showing correct/incorrect ratio -->
                    <div class="progress mb-3" style="height: 25px;">
                        <div class="progress-bar bg-success d-flex align-items-center justify-content-center" 
                             style="width: {{ $correctPercent }}%">
                            <span class="px-2">{{ $correct }} Correct</span>
                        </div>
                        <div class="progress-bar bg-danger d-flex align-items-center justify-content-center" 
                             style="width: {{ $incorrectPercent }}%">
                            <span class="px-2">{{ $incorrect }} Incorrect</span>
                        </div>
                        @if($reviewPercent > 0)
                            <div class="progress-bar bg-warning" style="width: {{ $reviewPercent }}%">
                                {{ $reviewCount }} Reviewed
                            </div>
                        @endif
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-md-4 mb-2">
                            <div id="correct-card" class="border border-success rounded py-2 stat-card" data-filter="correct">
                                <h4 class="mb-0 text-success">{{ $correct }}</h4>
                                <small>Correct Answers</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div id="incorrect-card" class="border border-danger rounded py-2 stat-card" data-filter="incorrect">
                                <h4 class="mb-0 text-danger">{{ $incorrect }}</h4>
                                <small>Incorrect Answers</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="border rounded py-2 stat-card" data-filter="all">
                                <h4 class="mb-0">{{ $totalQuestions > 0 ? number_format(($correct / $totalQuestions) * 100, 0) : 0 }}%</h4>
                                <small>Accuracy</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Questions and Answers -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Questions and Answers</h6>
                <div id="filter-status" class="badge bg-secondary px-3 py-2">
                    Showing All Questions
                </div>
            </div>
            
            <!-- Answer Legend Card -->
            <div class="card-body border-bottom pb-2 mb-3">
                <div class="answer-legend bg-light p-3 rounded border">
                    <h6 class="mb-2 fw-bold">How to read your answers:</h6>
                    <div class="d-flex flex-wrap">
                        <div class="me-4 mb-2">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span class="badge bg-success ms-1">Your Answer (Correct)</span>
                            <small class="ms-1">You selected the right answer</small>
                        </div>
                        <div class="me-4 mb-2">
                            <i class="bi bi-x-circle-fill text-danger"></i>
                            <span class="badge bg-danger ms-1">Your Answer (Incorrect)</span>
                            <small class="ms-1">You selected the wrong answer</small>
                        </div>
                        <div class="me-4 mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            <span class="badge bg-success ms-1">Correct Answer</span>
                            <small class="ms-1">The right answer when yours was wrong</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @foreach($answers as $index => $answer)
                    <div class="question-block mb-4 p-3 border rounded {{ $answer->is_correct ? 'border-success' : 'border-danger' }} bg-light question-item {{ $answer->is_correct ? 'correct-question' : 'incorrect-question' }}">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 fw-bold">Question {{ $index + 1 }}</h6>
                            <span class="badge {{ $answer->is_correct ? 'bg-success' : 'bg-danger' }} px-3 py-2">
                                {{ $answer->is_correct ? 'Correct' : 'Incorrect' }}
                            </span>
                        </div>
                        
                        <div class="question-text mb-3 p-2 bg-white rounded">
                            {{ $answer->question->question_text }}
                        </div>
                        
                        <div class="options-list">
                            @php
                                $options = is_string($answer->question->options) 
                                    ? json_decode($answer->question->options, true) 
                                    : $answer->question->options;
                                
                                // Get correct option
                                $correctOption = is_string($answer->question->correct_option) 
                                    ? json_decode($answer->question->correct_option, true) 
                                    : $answer->question->correct_option;
                                
                                // Convert to arrays for consistency
                                $optionsArray = is_array($options) ? $options : (array)$options;
                                
                                // Handle selected option - convert from letter if needed
                                $selectedOption = $answer->selected_option;
                                if (is_array($selectedOption) && count($selectedOption) > 0) {
                                    $selectedOption = $selectedOption[0];
                                }
                                
                                // If selected option is a letter (a, b, c, etc.), convert to the option text
                                $letterOptions = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];
                                $selectedOptionText = null;
                                $selectedLetterIndex = null;
                                
                                if (is_string($selectedOption) && in_array(strtolower($selectedOption), $letterOptions)) {
                                    $letterIndex = array_search(strtolower($selectedOption), $letterOptions);
                                    if ($letterIndex !== false) {
                                        $selectedLetterIndex = (int)$letterIndex;
                                        if (isset($optionsArray[$selectedLetterIndex])) {
                                            $selectedOptionText = $optionsArray[$selectedLetterIndex];
                                        }
                                    }
                                } else {
                                    // If it's not a letter, it might be the actual text
                                    $selectedOptionText = $selectedOption;
                                }
                                
                                // Process correct options - normalize all formats to option text
                                $normalizedCorrectOptions = [];
                                $correctLetterIndices = [];
                                
                                // First ensure correctOption is an array
                                if (!is_array($correctOption)) {
                                    $correctOption = [$correctOption];
                                }
                                
                                foreach ($correctOption as $co) {
                                    if (is_string($co) && in_array(strtolower($co), $letterOptions)) {
                                        $letterIndex = array_search(strtolower($co), $letterOptions);
                                        if ($letterIndex !== false) {
                                            $intLetterIndex = (int)$letterIndex;
                                            $correctLetterIndices[] = $intLetterIndex;
                                            if (isset($optionsArray[$intLetterIndex])) {
                                                $normalizedCorrectOptions[] = $optionsArray[$intLetterIndex];
                                            }
                                        }
                                    } else {
                                        $normalizedCorrectOptions[] = $co;
                                    }
                                }
                                
                                // Now correctOption contains the actual option text, not letters
                                $correctOptionText = $normalizedCorrectOptions;
                                
                                // Debug info
                                $questionNumber = (int)$index + 1;
                                $selectedLetterInfo = "";
                                
                                if (is_string($selectedOption) && strlen($selectedOption) == 1) {
                                    // If it's a single letter, show which option it should correspond to
                                    $letterIndex = ord(strtolower($selectedOption)) - ord('a');
                                    if ($letterIndex >= 0 && $letterIndex < count($optionsArray)) {
                                        $selectedLetterInfo = " (Letter '" . strtolower($selectedOption) . "' corresponds to option " . ($letterIndex + 1) . ")";
                                    }
                                }
                                
                                $debugInfo = "Question {$questionNumber}: Selected: " . json_encode($selectedOption) . 
                                             $selectedLetterInfo .
                                             ", Normalized Correct: " . json_encode($correctOptionText) . 
                                             ", Is Correct: " . ($answer->is_correct ? 'Yes' : 'No');
                            @endphp
                            
                            @if(env('APP_DEBUG', false))
                            <div class="alert alert-info mb-3 small">
                                <strong>Debug:</strong> {{ $debugInfo }}
                                <br><strong>Raw Selected Option:</strong> {{ is_string($selectedOption) ? "\"$selectedOption\"" : json_encode($selectedOption) }}
                                <br><strong>Selected Index:</strong> {{ $selectedLetterIndex !== null ? $selectedLetterIndex : 'none' }}
                                <br><strong>Correct Indices:</strong> {{ json_encode($correctLetterIndices) }}
                                <br><strong>Options:</strong> 
                                @php
                                    foreach($optionsArray as $idx => $opt) {
                                        $letter = chr(ord('a') + (int)$idx);
                                        $isSelectedByLetter = ($selectedLetterIndex !== null && $selectedLetterIndex === $idx);
                                        $isCorrectByIndex = in_array($idx, $correctLetterIndices);
                                        $status = [];
                                        if ($isSelectedByLetter) $status[] = "SELECTED";
                                        if ($isCorrectByIndex) $status[] = "CORRECT";
                                        $statusStr = !empty($status) ? " - <strong>" . implode(", ", $status) . "</strong>" : "";
                                        echo "<br>$letter ($idx): $opt$statusStr";
                                    }
                                @endphp
                            </div>
                            @endif
                            
                            @foreach($optionsArray as $index => $option)
                                @php
                                    // Determine if this option is correct by index
                                    $isCorrectOption = in_array($index, $correctLetterIndices);
                                    
                                    // Determine if this option was selected by the student by index
                                    $isSelectedOption = ($selectedLetterIndex !== null && $selectedLetterIndex === $index);
                                @endphp
                                
                                <!-- Display each option with appropriate styling -->
                                <div class="option p-2 mb-2 rounded d-flex align-items-center
                                    {{ $isSelectedOption ? 'selected-option' : '' }}
                                    {{ $isCorrectOption ? 'correct-option' : '' }}
                                    {{ $isSelectedOption && !$isCorrectOption ? 'incorrect-option' : '' }}">
                                    
                                    <!-- Option indicator icon -->
                                    <div class="option-indicator me-2">
                                        @if($isSelectedOption && $isCorrectOption)
                                            <!-- Student selected correct answer -->
                                            <i class="bi bi-check-circle-fill text-success"></i>
                                        @elseif($isSelectedOption && !$isCorrectOption)
                                            <!-- Student selected wrong answer -->
                                            <i class="bi bi-x-circle-fill text-danger"></i>
                                        @elseif($isCorrectOption && !$isSelectedOption)
                                            <!-- This is the correct answer student didn't select -->
                                            <i class="bi bi-check-circle text-success"></i>
                                        @else
                                            <!-- Unselected option -->
                                            <i class="bi bi-circle"></i>
                                        @endif
                                    </div>
                                    
                                    <!-- Option text -->
                                    <div class="option-text flex-grow-1">
                                    {{ $option }}
                                    </div>
                                    
                                    <!-- Clear labels for student's answer and correct answer -->
                                    <div class="ms-2">
                                        @if($isSelectedOption && !$isCorrectOption)
                                            <!-- Student's incorrect answer -->
                                            <span class="badge bg-danger">Your Answer (Incorrect)</span>
                                        @elseif($isSelectedOption && $isCorrectOption)
                                            <!-- Student's correct answer -->
                                            <span class="badge bg-success">Your Answer (Correct)</span>
                                        @elseif($isCorrectOption && !$isSelectedOption)
                                            <!-- The correct answer student didn't choose -->
                                            <span class="badge bg-success">Correct Answer</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            <!-- Add a note if no answer was selected -->
                            @if(!$selectedOption && !$answer->is_correct)
                                <div class="alert alert-warning mt-2">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <strong>Note:</strong> You didn't select an answer for this question. The correct answer is highlighted above.
                                </div>
                            @endif
                        </div>

                        @if($answer->question->explanation)
                            <div class="explanation mt-3 p-2 border-top">
                                <strong>Explanation:</strong>
                                <p class="mb-0">{{ $answer->question->explanation }}</p>
                            </div>
                        @endif
                        
                        @if($answer->is_marked_for_review ?? false)
                            <div class="mt-2">
                                <span class="badge bg-warning">Marked for Review</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <style>
        .selected-option {
            background-color: rgba(0, 0, 0, 0.05);
            font-weight: 500;
        }
        
        .correct-option {
            border-left: 4px solid #28a745;
        }
        
        .incorrect-option {
            border-left: 4px solid #dc3545;
            background-color: rgba(220, 53, 69, 0.1);
        }
        
        /* Enhance the appearance of correct answers when displayed alongside incorrect answers */
        .question-block:not(.correct-question) .correct-option {
            border: 1px solid #28a745;
            background-color: rgba(40, 167, 69, 0.1);
            position: relative;
        }
        
        /* Add a subtle indicator to make it super clear */
        .question-block:not(.correct-question) .correct-option::after {
            content: "✓";
            position: absolute;
            top: 5px;
            right: 5px;
            color: #28a745;
            font-weight: bold;
        }
        
        .option {
            transition: all 0.2s;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .option:hover {
            background-color: rgba(0, 0, 0, 0.03);
        }
        
        .score-badge {
            font-size: 1.1rem;
        }

        /* Make cards clickable */
        .stat-card {
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .stat-card.active {
            background-color: rgba(0,0,0,0.05);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get all filter elements
            const allBtn = document.getElementById('show-all-btn');
            const correctBtn = document.getElementById('show-correct-btn');
            const incorrectBtn = document.getElementById('show-incorrect-btn');
            const correctCard = document.getElementById('correct-card');
            const incorrectCard = document.getElementById('incorrect-card');
            const filterStatus = document.getElementById('filter-status');
            
            // Get all question items
            const allQuestions = document.querySelectorAll('.question-item');
            const correctQuestions = document.querySelectorAll('.correct-question');
            const incorrectQuestions = document.querySelectorAll('.incorrect-question');
            
            // Function to update active button state
            function updateActiveButton(activeBtn) {
                [allBtn, correctBtn, incorrectBtn].forEach(btn => {
                    btn.classList.remove('active');
                });
                activeBtn.classList.add('active');
            }
            
            // Function to update active card state
            function updateActiveCard(activeCard) {
                [correctCard, incorrectCard].forEach(card => {
                    card.classList.remove('active');
                });
                if (activeCard) {
                    activeCard.classList.add('active');
                }
            }
            
            // Show all questions
            function showAllQuestions() {
                allQuestions.forEach(q => q.style.display = '');
                updateActiveButton(allBtn);
                updateActiveCard(null);
                filterStatus.textContent = 'Showing All Questions';
                filterStatus.className = 'badge bg-secondary px-3 py-2';
            }
            
            // Show only correct questions
            function showCorrectQuestions() {
                allQuestions.forEach(q => q.style.display = 'none');
                correctQuestions.forEach(q => q.style.display = '');
                updateActiveButton(correctBtn);
                updateActiveCard(correctCard);
                filterStatus.textContent = 'Showing Correct Answers Only';
                filterStatus.className = 'badge bg-success px-3 py-2';
            }
            
            // Show only incorrect questions
            function showIncorrectQuestions() {
                allQuestions.forEach(q => q.style.display = 'none');
                incorrectQuestions.forEach(q => q.style.display = '');
                updateActiveButton(incorrectBtn);
                updateActiveCard(incorrectCard);
                filterStatus.textContent = 'Showing Incorrect Answers Only';
                filterStatus.className = 'badge bg-danger px-3 py-2';
            }
            
            // Add event listeners
            allBtn.addEventListener('click', showAllQuestions);
            correctBtn.addEventListener('click', showCorrectQuestions);
            incorrectBtn.addEventListener('click', showIncorrectQuestions);
            correctCard.addEventListener('click', showCorrectQuestions);
            incorrectCard.addEventListener('click', showIncorrectQuestions);
        });
    </script>
</x-student-layout> 