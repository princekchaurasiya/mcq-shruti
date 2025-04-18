<x-teacher-layout>
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Test Result Details</h1>
            <div>
                <a href="{{ route('mcq-tests.results', $testAttempt->mcq_test_id) }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Results
                </a>
            </div>
        </div>

        <!-- Student & Test Info -->
        <div class="row">
            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Student Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tr>
                                    <th style="width: 150px;">Name:</th>
                                    <td><strong>{{ $testAttempt->user->name }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $testAttempt->user->email }}</td>
                                </tr>
                                <tr>
                                    <th>Student ID:</th>
                                    <td>{{ $testAttempt->user->id }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Test Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tr>
                                    <th style="width: 150px;">Test:</th>
                                    <td><strong>{{ $testAttempt->mcqTest->title }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Subject:</th>
                                    <td>{{ $testAttempt->mcqTest->subject->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Date Attempted:</th>
                                    <td>{{ $testAttempt->created_at->format('F j, Y, g:i a') }}</td>
                                </tr>
                                <tr>
                                    <th>Time Taken:</th>
                                    <td>{{ $timeTaken }} minutes</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Test Score Summary -->
        <div class="row">
            <div class="col-xl-12 col-lg-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Test Results Summary</h6>
                        <div>
                            <button class="btn btn-sm btn-info" id="printResults">
                                <i class="bi bi-printer"></i> Print
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Score -->
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card {{ $testAttempt->is_passed ? 'border-left-success' : 'border-left-danger' }} shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold {{ $testAttempt->is_passed ? 'text-success' : 'text-danger' }} text-uppercase mb-1">
                                                    Score</div>
                                                <div class="h3 mb-0 font-weight-bold text-gray-800">{{ number_format($testAttempt->score, 1) }}%</div>
                                                <div class="text-xs text-muted">
                                                    {{ $testAttempt->is_passed ? 'Passed' : 'Failed' }} 
                                                    (Passing: {{ $testAttempt->mcqTest->passing_percentage }}%)
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="{{ $testAttempt->is_passed ? 'bi bi-check-circle' : 'bi bi-x-circle' }} fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Questions -->
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-left-primary shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Total Questions</div>
                                                <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $totalQuestions }}</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="bi bi-list-check fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Correct Answers -->
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-left-success shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                    Correct</div>
                                                <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $correctAnswers }}</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="bi bi-check-circle fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Incorrect Answers -->
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-left-danger shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                    Incorrect/Unanswered</div>
                                                <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $incorrectAnswers }} / {{ $unansweredQuestions }}</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="bi bi-x-circle fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $correctPercentage }}%" 
                                        aria-valuenow="{{ $correctPercentage }}" aria-valuemin="0" aria-valuemax="100">
                                        {{ $correctPercentage }}% Correct
                                    </div>
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $unansweredPercentage }}%" 
                                        aria-valuenow="{{ $unansweredPercentage }}" aria-valuemin="0" aria-valuemax="100">
                                        {{ $unansweredPercentage }}% Unanswered
                                    </div>
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $incorrectPercentage }}%" 
                                        aria-valuenow="{{ $incorrectPercentage }}" aria-valuemin="0" aria-valuemax="100">
                                        {{ $incorrectPercentage }}% Incorrect
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Question Details -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Question-by-Question Analysis</h6>
                    </div>
                    <div class="card-body">
                        <!-- Filter controls -->
                        <div class="mb-4">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary active filter-btn" data-filter="all">All Questions</button>
                                <button type="button" class="btn btn-outline-success filter-btn" data-filter="correct">Correct Only</button>
                                <button type="button" class="btn btn-outline-danger filter-btn" data-filter="incorrect">Incorrect Only</button>
                                <button type="button" class="btn btn-outline-warning filter-btn" data-filter="unanswered">Unanswered Only</button>
                            </div>
                        </div>

                        <!-- Questions accordion -->
                        <div class="accordion" id="questionsAccordion">
                            @foreach($responses as $index => $response)
                                <div class="card mb-2 question-card {{ $response['status'] }}-question">
                                    <div class="card-header py-3 d-flex justify-content-between align-items-center" id="heading{{ $index }}">
                                        <h6 class="mb-0">
                                            <button class="btn btn-link text-decoration-none" type="button" data-toggle="collapse" data-target="#collapse{{ $index }}">
                                                <span class="text-gray-900">Question {{ $index + 1 }}:</span>
                                                <span class="text-muted">{{ Str::limit($response['question_text'], 70) }}</span>
                                            </button>
                                        </h6>
                                        <span class="badge badge-pill 
                                            {{ $response['status'] === 'correct' ? 'bg-success text-white' : 
                                               ($response['status'] === 'incorrect' ? 'bg-danger text-white' : 'bg-warning text-dark') }}">
                                            {{ ucfirst($response['status']) }}
                                        </span>
                                    </div>
                                
                                    <div id="collapse{{ $index }}" class="collapse" aria-labelledby="heading{{ $index }}" data-parent="#questionsAccordion">
                                        <div class="card-body">
                                            <div class="question-content mb-3">
                                                <h5 class="card-title">{{ $response['question_text'] }}</h5>
                                                @if($response['question_image'])
                                                    <div class="text-center mb-3">
                                                        <img src="{{ asset('storage/' . $response['question_image']) }}" 
                                                            alt="Question Image" class="img-fluid" style="max-height: 200px;">
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="options-list">
                                                @foreach($response['options'] as $option)
                                                    <div class="form-check mb-2 p-2 
                                                        {{ $option['is_correct'] ? 'bg-success text-white rounded' : 
                                                           ($option['id'] == $response['selected_option_id'] && !$option['is_correct'] ? 'bg-danger text-white rounded' : '') }}">
                                                        <input class="form-check-input" type="radio" disabled
                                                            {{ $option['id'] == $response['selected_option_id'] ? 'checked' : '' }}>
                                                        <label class="form-check-label d-block">
                                                            {{ $option['option_text'] }}
                                                            @if($option['is_correct'])
                                                                <i class="bi bi-check-circle-fill ms-2"></i>
                                                            @endif
                                                            @if($option['id'] == $response['selected_option_id'] && !$option['is_correct'])
                                                                <i class="bi bi-x-circle-fill ms-2"></i>
                                                            @endif
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>

                                            @if($response['explanation'])
                                                <div class="explanation mt-3 p-3 bg-light rounded">
                                                    <h6 class="font-weight-bold">Explanation:</h6>
                                                    <p>{{ $response['explanation'] }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feedback Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Teacher Feedback</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('teacher.feedback.store', $testAttempt->id) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="feedback">Add Feedback for Student</label>
                                <textarea class="form-control" id="feedback" name="feedback" rows="3">{{ $testAttempt->feedback ?? '' }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Feedback</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .question-card {
            border-left: 4px solid #f8f9fc;
        }
        .correct-question {
            border-left: 4px solid #1cc88a;
        }
        .incorrect-question {
            border-left: 4px solid #e74a3b;
        }
        .unanswered-question {
            border-left: 4px solid #f6c23e;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filters for questions
            const filterButtons = document.querySelectorAll('.filter-btn');
            
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    const filter = this.getAttribute('data-filter');
                    const questionCards = document.querySelectorAll('.question-card');
                    
                    questionCards.forEach(card => {
                        if (filter === 'all') {
                            card.style.display = 'block';
                        } else {
                            if (card.classList.contains(`${filter}-question`)) {
                                card.style.display = 'block';
                            } else {
                                card.style.display = 'none';
                            }
                        }
                    });
                });
            });

            // Print functionality
            document.getElementById('printResults').addEventListener('click', function() {
                window.print();
            });
        });
    </script>
    @endpush
</x-teacher-layout> 