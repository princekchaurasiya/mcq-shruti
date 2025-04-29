<x-student-layout>
<div class="container py-4">
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
                </div>
    @endif

    <div class="mb-4">
        <div class="card shadow-sm">
            <div class="card-header py-3">
                <h5 class="mb-0 fw-bold">Test Result Summary</h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <h4 class="mb-3">{{ $result->mcqTest->title }}</h4>
                        <h2 class="display-6 fw-bold mb-3">{{ number_format($result->score, 1) }}%</h2>
                        
                        <!-- Basic test info -->
                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-2">
                                <div class="me-3" style="width: 28px;"><i class="bi bi-book fs-5"></i></div>
                                <div>
                                    <div class="small text-muted">Subject</div>
                                    <div>{{ $result->mcqTest->subject->name }}</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <div class="me-3" style="width: 28px;"><i class="bi bi-question-circle fs-5"></i></div>
                                <div>
                                    <div class="small text-muted">Total Questions</div>
                                    <div>{{ $result->mcqTest->questions()->count() }}</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <div class="me-3" style="width: 28px;"><i class="bi bi-clock fs-5"></i></div>
                                <div>
                                    <div class="small text-muted">Duration</div>
                                    <div>{{ $result->mcqTest->duration_minutes }} minutes</div>
                                </div>
                    </div>
                            <div class="d-flex align-items-center mb-2">
                                <div class="me-3" style="width: 28px;"><i class="bi bi-calendar-check fs-5"></i></div>
                                <div>
                                    <div class="small text-muted">Completed</div>
                                    <div>{{ $result->completed_at ? $result->completed_at->format('M d, Y h:i A') : 'In Progress' }}</div>
                    </div>
                </div>
                            <div class="d-flex align-items-center">
                                <div class="me-3" style="width: 28px;"><i class="bi bi-award fs-5"></i></div>
                        <div>
                                    <div class="small text-muted">Pass Requirement</div>
                                    <div>{{ $result->mcqTest->passing_percentage }}%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body p-4">
                                <h5 class="mb-3">Performance</h5>
                                
                                @php
                                    // Calculate metrics from formatted responses
                                    $formattedResponses = $result->formattedResponses;
                                    $totalQuestions = $formattedResponses->count();
                                    $correctAnswers = $formattedResponses->where('is_correct', true)->count();
                                    $incorrectAnswers = $totalQuestions - $correctAnswers;
                                    
                                    $percentCorrect = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;
                                    $percentIncorrect = $totalQuestions > 0 ? ($incorrectAnswers / $totalQuestions) * 100 : 0;
                    @endphp
                    
                                <!-- Performance summary -->
                                <div class="mb-4">
                                    <div class="btn-group btn-group-sm w-100 mb-3" role="group">
                                        <button type="button" class="btn btn-outline-primary active" data-filter="all">All ({{ $totalQuestions }})</button>
                                        <button type="button" class="btn btn-outline-success" data-filter="correct">Correct ({{ $correctAnswers }})</button>
                                        <button type="button" class="btn btn-outline-danger" data-filter="incorrect">Incorrect ({{ $incorrectAnswers }})</button>
                        </div>
                                    
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentCorrect }}%" aria-valuenow="{{ $percentCorrect }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $percentIncorrect }}%" aria-valuenow="{{ $percentIncorrect }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                    </div>
                    
                                <!-- Stats -->
                                <div class="mb-3">
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>Correct</div>
                                            <div class="fw-bold text-success">{{ $correctAnswers }} ({{ number_format($percentCorrect, 1) }}%)</div>
                            </div>
                        </div>
                                    <div class="mb-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>Incorrect</div>
                                            <div class="fw-bold text-danger">{{ $incorrectAnswers }} ({{ number_format($percentIncorrect, 1) }}%)</div>
                                        </div>
                            </div>
                        </div>
                                
                                <!-- Status -->
                                <div class="mt-4 text-center">
                                    @if($result->score >= $result->mcqTest->passing_percentage)
                                        <div class="alert alert-success mb-0">
                                            <i class="bi bi-emoji-smile fs-4 me-2"></i>
                                            <span class="fw-bold">Passed!</span>
                                        </div>
                                    @else
                                        <div class="alert alert-danger mb-0">
                                            <i class="bi bi-emoji-frown fs-4 me-2"></i>
                                            <span class="fw-bold">Not Passed</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <div class="mb-4">
        <div class="card shadow-sm">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Questions and Answers</h5>
            </div>
            <div class="card-body p-0">
                <div class="p-3 bg-light border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="text-muted small me-4">
                            <div class="d-flex align-items-center mb-1">
                                <div class="me-2 option-marker-circle selected-marker">
                                    <i class="fas fa-circle"></i>
                                </div>
                                <span>Your Selection</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="me-2 option-marker-circle correct-marker">
                                    <i class="fas fa-check-circle"></i>
                        </div>
                                <span>Correct Answer</span>
                        </div>
                        </div>
                    </div>
                        </div>
                        
                @foreach($result->formattedResponses as $index => $answer)
                    <div class="question-item p-3 border-bottom {{ $answer['is_correct'] ? 'correct-question' : 'incorrect-question' }}">
                        <div class="question-block mb-2">
                            <!-- Question header with status badge -->
                            <div class="mb-3 d-flex justify-content-between align-items-start">
                                <h5 class="mb-0">Question {{ $index + 1 }}</h5>
                                
                                @if($answer['is_correct'])
                                    <span class="badge bg-success-subtle text-success px-2 py-1">
                                        <i class="bi bi-check-circle-fill me-1"></i> Correct
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger px-2 py-1">
                                        <i class="bi bi-x-circle-fill me-1"></i> Incorrect
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Question text -->
                            <div class="question-text mb-3">
                                <p>{{ $answer['question']['text'] }}</p>
                            </div>
                            
                            <!-- Options list -->
                            <div class="question-options">
                                @foreach($answer['options'] as $optIndex => $option)
                                <div class="question-option
                                    {{ $option['is_selected'] && !$option['is_correct'] ? 'marked-incorrect' : '' }} 
                                    {{ $option['is_correct'] ? 'correct-answer' : '' }}">
                                    
                                    <div class="option-indicator">
                                        <span class="option-letter">{{ chr(65 + $optIndex) }}</span>
                                    </div>
                                    
                                    <div class="option-text">
                                        {{ $option['text'] }}
                                    </div>
                                    
                                    @if($option['is_selected'] && !$option['is_correct'])
                                        <div class="marked-label">
                                            <span class="badge bg-danger">Incorrect</span>
                                        </div>
                                        @endif
                                    
                                    @if($option['is_correct'])
                                        <div class="correct-label">
                                            <span class="badge bg-success">Correct answer</span>
                                    </div>
                                    @endif
                                </div>
                            @endforeach
                            </div>

                            <!-- Explanation if present -->
                            @if(!empty($answer['question']['explanation']))
                                <div class="explanation p-2 mt-3">
                                    <div class="fw-bold mb-1">Explanation:</div>
                                    <div>{{ $answer['question']['explanation'] }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
                
                <!-- Empty state if no answers -->
                @if($result->formattedResponses->isEmpty())
                    <div class="p-4 text-center">
                        <div class="text-muted">No answers available for this test.</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <style>
        /* Question block styling */
        .question-block {
            background-color: #f8f9fa;
            border-radius: 8px;
            position: relative;
            overflow: hidden;
        }
        
        .question-text {
            border-radius: 8px;
        }
        
        .question-text h5 {
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        /* Mobile-style option styling */
        .option {
            transition: all 0.15s;
            border: 1px solid #dee2e6;
            background-color: white;
            position: relative;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .option-correct {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }
        
        .option-incorrect {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        
        .option-letter {
            font-weight: bold;
            color: #495057;
            min-width: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            background-color: #e9ecef;
            font-size: 14px;
            margin-right: 8px;
        }
        
        .option-text {
            flex: 1;
            font-size: 15px;
            line-height: 1.5;
        }
        
        /* Clean mobile design - more minimal */
        .options-list {
            padding: 0.5rem 0;
        }
        
        /* Question status badges */
        .question-status {
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .question-correct {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
        }
        
        .question-incorrect {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        /* Icons */
        .bi-check-circle-fill,
        .bi-x-circle-fill,
        .bi-check-circle {
            font-size: 1.25rem;
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
        }
        
        /* Explanation styling */
        .explanation {
            background-color: #fff3cd;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .question-option {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            transition: all 0.2s ease;
            position: relative;
        }

        /* User marked this option but it's incorrect - red */
        .question-option.marked-incorrect {
            background-color: #ffebeb;
            color: #e04551;
            border: 2px solid #e04551;
            border-left: 5px solid #e04551;
        }
        
        /* Add the badge style for correct/incorrect */
        .question-option.correct-answer .correct-label,
        .question-option.marked-incorrect .marked-label {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }
        
        /* Style the option letter for incorrect/correct to match screenshot */
        .question-option.marked-incorrect .option-letter,
        .question-option.correct-answer .option-letter {
            background-color: white;
            color: #333;
        }

        .marked-label .badge {
            font-weight: normal;
            background: none;
            color: white;
        }

        /* This option is the correct answer - green */
        .question-option.correct-answer {
            background-color: #e8fff0;
            color: #28a745;
            border: 2px solid #28a745;
            border-left: 5px solid #28a745;
        }
        
        .question-option.marked-incorrect .option-text {
            color: #e04551;
            font-weight: 500;
        }
        
        .question-option.correct-answer .option-text {
            color: #28a745;
            font-weight: 500;
        }

        .marked-label {
            position: absolute;
            right: 10px;
        }

        .correct-label {
            position: absolute;
            right: 10px;
        }
        
        .marked-label .badge {
            font-weight: normal;
            background: none;
            color: white;
        }

        /* Markers for correct/incorrect selections */
        .option-marker-circle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            margin-right: 3px;
            border-radius: 50%;
        }
        
        .correct-marker {
            color: #28a745;
        }
        
        .incorrect-marker {
            color: #dc3545;
        }

        .option-status {
            margin-left: 10px;
            font-size: 18px;
        }

        .status-icon {
            display: inline-flex;
        }

        .option-indicator {
            display: flex;
            align-items: center;
            margin-right: 12px;
            min-width: 80px;
        }
        
        .option-markers {
            display: flex;
            align-items: center;
            margin-left: 5px;
        }
        
        .option-text {
            flex: 1;
            font-size: 15px;
            line-height: 1.5;
        }
        
        .option-status {
            margin-left: 10px;
            font-size: 18px;
        }

        /* Badge styles */
        .question-option.correct-answer .badge {
            background-color: transparent;
            color: white;
            font-weight: 500;
            border-radius: 3px;
        }
        
        .question-option.marked-incorrect .badge {
            background-color: transparent;
            color: white;
            font-weight: 500;
            border-radius: 3px;
        }
    </style>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filter questions based on correctness
            const filterButtons = document.querySelectorAll('[data-filter]');
            const questionItems = document.querySelectorAll('.question-item');
            
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Update active state
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    const filter = this.getAttribute('data-filter');
                    
                    questionItems.forEach(item => {
                        if (filter === 'all') {
                            item.style.display = 'block';
                        } else if (filter === 'correct' && item.classList.contains('correct-question')) {
                            item.style.display = 'block';
                        } else if (filter === 'incorrect' && item.classList.contains('incorrect-question')) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            });
        });
    </script>
</div>
</x-student-layout> 