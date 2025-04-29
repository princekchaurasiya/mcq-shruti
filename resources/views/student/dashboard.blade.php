<x-student-layout>
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Student Dashboard</h1>
            <div>
                <a href="{{ route('student.results.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-info shadow-sm me-2">
                    <i class="bi bi-list-check me-2"></i> View All Results
                </a>
                <a href="{{ route('tests.available') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                    <i class="bi bi-pencil-square me-2"></i> Available Tests
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row">
            <!-- Total Tests Attempted Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Tests Attempted</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    @php
                                        $student = Auth::user()->student;
                                        $testAttempts = $student ? Auth::user()->testAttempts()->count() : 0;
                                    @endphp
                                    {{ $testAttempts }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-pencil-square fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Average Score Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Average Score</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    @php
                                        $avgScore = $student ? Auth::user()->testAttempts()->avg('score') : 0;
                                    @endphp
                                    {{ number_format($avgScore, 2) }}%
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-graph-up fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Available Tests Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Available Tests</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    @php
                                        $availableCount = App\Models\McqTest::where('end_time', '>', now())
                                            ->where('is_active', true)
                                            ->has('questions')
                                            ->count();
                                    @endphp
                                    {{ $availableCount }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-clipboard2-check fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completed Tests Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Completed Tests</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    @php
                                        $completedTests = $student ? Auth::user()->testAttempts()->whereNotNull('completed_at')->count() : 0;
                                    @endphp
                                    {{ $completedTests }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-check-circle fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Available Tests Section -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Available Tests</h6>
                        <div class="d-flex">
                            <div class="dropdown me-2">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="subjectFilter" data-bs-toggle="dropdown" aria-expanded="false">
                                    Filter by Subject
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="subjectFilter">
                                    <li><a class="dropdown-item" href="#" data-subject="all">All Subjects</a></li>
                                    @php
                                        $subjects = App\Models\Subject::has('mcqTests')->get();
                                    @endphp
                                    @foreach($subjects as $subject)
                                        <li><a class="dropdown-item" href="#" data-subject="{{ $subject->id }}">{{ $subject->name }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                            <a href="{{ route('tests.available') }}" class="btn btn-sm btn-primary">View All</a>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        @php
                            // Only get tests with questions and that are active
                            $availableTests = App\Models\MCQTest::where('end_time', '>', now())
                                ->where('is_active', true)
                                ->has('questions')
                                ->with(['subject'])
                                ->latest('start_time')
                                ->take(3)
                                ->get();
                            
                            // Get subjects for filter
                            $subjects = App\Models\Subject::has('mcqTests')->get();
                        @endphp
                        
                        @if($availableTests->isEmpty())
                            <div class="text-center py-5">
                                <i class="bi bi-info-circle text-info fs-1"></i>
                                <h5 class="mt-3">No tests available right now</h5>
                                <p class="text-muted">Check back later for new tests</p>
                            </div>
                        @else
                            <div class="row test-container">
                                @foreach($availableTests as $test)
                                    @php
                                        $attemptsCount = $test->getAttemptsCountByUser(auth()->user());
                                        $maxAttempts = 5;
                                        $remainingAttempts = $maxAttempts - $attemptsCount;
                                        $canTakeTest = $test->canBeTaken() && $remainingAttempts > 0;
                                    @endphp
                                    <div class="col-md-6 col-lg-4 mb-3 test-item" data-subject="{{ $test->subject_id }}">
                                        <div class="card h-100 {{ $canTakeTest ? 'border-primary' : 'bg-light' }}">
                                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                <h6 class="m-0">{{ Str::limit($test->title, 18) }}</h6>
                                                <span class="badge {{ $canTakeTest ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $canTakeTest ? 'Available' : 'Attempted' }}
                                                </span>
                                            </div>
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="badge bg-info">{{ $test->subject->name }}</span>
                                                    <span><i class="bi bi-clock"></i> {{ $test->duration_minutes }} min</span>
                                                </div>
                                                
                                                <div class="d-flex justify-content-between mb-2">
                                                    <small><i class="bi bi-question-circle"></i> {{ $test->questions->count() }} questions</small>
                                                    <small><i class="bi bi-award"></i> {{ $test->total_marks }} marks</small>
                                                </div>
                                                
                                                @if($attemptsCount > 0)
                                                    <div class="progress mb-2" style="height: 5px;">
                                                        <div class="progress-bar bg-info" role="progressbar" 
                                                            style="width: {{ ($attemptsCount / $maxAttempts) * 100 }}%" 
                                                            aria-valuenow="{{ $attemptsCount }}" 
                                                            aria-valuemin="0" aria-valuemax="{{ $maxAttempts }}"></div>
                                                    </div>
                                                    <div class="small text-muted text-center">
                                                        {{ $attemptsCount }}/{{ $maxAttempts }} attempts used
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="card-footer bg-transparent">
                                                @if($canTakeTest)
                                                    <a href="{{ route('test.attempt', $test->id) }}" class="btn btn-primary btn-sm w-100 reattempt-btn" data-test-id="{{ $test->id }}">
                                                        {{ $attemptsCount > 0 ? 'Reattempt ('.$attemptsCount.'/'.$maxAttempts.')' : 'Start Test' }}
                                                    </a>
                                                @else
                                                    <button class="btn btn-secondary btn-sm w-100" disabled>
                                                        Max Attempts Reached
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <!-- Pagination -->
                            <div class="d-flex justify-content-center py-3">
                                @if(method_exists($availableTests, 'appends'))
                                    {{ $availableTests->appends(['results_page' => request()->results_page])->links() }}
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Test Attempts -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Recent Results</h6>
                        <a href="{{ route('student.results.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        @php
                            $recentAttempts = $student ? Auth::user()->testAttempts()
                                ->with(['mcqTest.subject', 'responses'])
                                ->whereNotNull('completed_at')
                                ->latest()
                                ->take(3)
                                ->get() : collect([]);
                            @endphp
                        
                        @if($recentAttempts->isEmpty())
                            <div class="text-center py-5">
                                <i class="bi bi-hourglass text-secondary fs-1"></i>
                                <h5 class="mt-3">No completed tests yet</h5>
                                <p class="text-muted">Your recent test results will appear here</p>
                            </div>
                        @else
                            @foreach($recentAttempts as $attempt)
                                @php
                                    // Calculate the stats
                                    $totalQuestions = $attempt->mcqTest->questions()->count();
                                    $correct = $attempt->responses()->where('is_correct', true)->count();
                                    $incorrect = $attempt->responses()->where('is_correct', false)->count();
                                    $markedForReview = 0;
                                    
                                    // Check if column exists before querying
                                    try {
                                        $markedForReview = $attempt->responses()->where('is_marked_for_review', true)->count();
                                    } catch (\Exception $e) {
                                        // Column doesn't exist yet, migrations pending
                                        $markedForReview = 0;
                                    }
                                    
                                    $unattempted = $totalQuestions - $correct - $incorrect;
                                    
                                    // Calculate percentages for progress bars
                                    $correctPercent = $totalQuestions > 0 ? ($correct / $totalQuestions) * 100 : 0;
                                    $incorrectPercent = $totalQuestions > 0 ? ($incorrect / $totalQuestions) * 100 : 0;
                                    $reviewPercent = $totalQuestions > 0 ? ($markedForReview / $totalQuestions) * 100 : 0;
                                    $unattemptedPercent = 100 - $correctPercent - $incorrectPercent;
                                    
                                    // Determine badge color based on score
                                    $scoreBadgeClass = 'bg-danger';
                                    if ($attempt->score >= 80) {
                                        $scoreBadgeClass = 'bg-success';
                                    } elseif ($attempt->score >= 60) {
                                        $scoreBadgeClass = 'bg-info';
                                    } elseif ($attempt->score >= 40) {
                                        $scoreBadgeClass = 'bg-warning';
                                    }
                                @endphp
                                
                                <div class="result-card mb-4 border rounded shadow-sm">
                                    <div class="result-header p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">{{ Str::limit($attempt->mcqTest->title, 25) }}</h6>
                                            <small class="text-muted">{{ $attempt->mcqTest->subject->name }}</small>
                                        </div>
                                        <div class="text-center">
                                            <div class="score-circle {{ $scoreBadgeClass }} text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                <span class="fw-bold">{{ round($attempt->score) }}%</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="result-body p-3">
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between mb-1">
                                                <small class="text-muted">Performance Summary</small>
                                                <small>{{ $attempt->completed_at->diffForHumans() }}</small>
                                            </div>
                                            
                                            <!-- Multi-color progress bar -->
                                            <div class="progress mb-2" style="height: 8px;">
                                                @if($correctPercent > 0)
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $correctPercent }}%" 
                                                        title="Correct: {{ $correct }}" aria-valuenow="{{ $correctPercent }}" 
                                                        aria-valuemin="0" aria-valuemax="100"></div>
                                                @endif
                                                
                                                @if($incorrectPercent > 0)
                                                    <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $incorrectPercent }}%" 
                                                        title="Incorrect: {{ $incorrect }}" aria-valuenow="{{ $incorrectPercent }}" 
                                                        aria-valuemin="0" aria-valuemax="100"></div>
                                                @endif
                                                
                                                @if($reviewPercent > 0)
                                                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $reviewPercent }}%" 
                                                        title="Reviewed: {{ $markedForReview }}" aria-valuenow="{{ $reviewPercent }}" 
                                                        aria-valuemin="0" aria-valuemax="100"></div>
                                                @endif
                                                
                                                @if($unattemptedPercent > 0)
                                                    <div class="progress-bar bg-secondary" role="progressbar" style="width: {{ $unattemptedPercent }}%" 
                                                        title="Unattempted: {{ $unattempted }}" aria-valuenow="{{ $unattemptedPercent }}" 
                                                        aria-valuemin="0" aria-valuemax="100"></div>
                                                @endif
                                            </div>
                                            
                                            <!-- Stats legend -->
                                            <div class="d-flex justify-content-between small">
                                                <span><i class="bi bi-circle-fill text-success"></i> {{ $correct }} correct</span>
                                                <span><i class="bi bi-circle-fill text-danger"></i> {{ $incorrect }} incorrect</span>
                                                @if($unattempted > 0)
                                                    <span><i class="bi bi-circle-fill text-secondary"></i> {{ $unattempted }} skipped</span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between mt-3">
                                            <small>Time: {{ round($attempt->created_at->diffInMinutes($attempt->completed_at)) }} min</small>
                                            <a href="{{ route('student.results.show', $attempt->id) }}" class="btn btn-sm btn-outline-primary">
                                                View Details <i class="bi bi-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <!-- Pagination -->
                            <div class="d-flex justify-content-center py-3">
                                @if(method_exists($recentAttempts, 'appends'))
                                    {{ $recentAttempts->appends(['tests_page' => request()->tests_page])->links() }}
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Test Attempts In Progress -->
                @php
                    $inProgressAttempts = $student ? Auth::user()->testAttempts()
                        ->whereNull('completed_at')
                        ->with('mcqTest')
                        ->latest()
                        ->take(2)
                        ->get() : collect([]);
                @endphp
                
                @if($inProgressAttempts->isNotEmpty())
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-warning">In Progress</h6>
                        </div>
                        <div class="card-body">
                            <div class="list-group">
                                @foreach($inProgressAttempts as $attempt)
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ Str::limit($attempt->mcqTest->title, 25) }}</h6>
                                            <small class="text-muted">Started {{ $attempt->created_at->diffForHumans() }}</small>
                                        </div>
                                        <a href="{{ route('test.attempt', $attempt->mcq_test_id) }}" class="btn btn-sm btn-warning">
                                            Continue <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Test Categories -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Browse by Subject</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @php
                        $subjects = App\Models\Subject::withCount(['mcqTests' => function($query) {
                            $query->where('end_time', '>', now())
                                  ->where('is_active', true)
                                  ->has('questions');
                        }])->having('mcq_tests_count', '>', 0)->get();
                    @endphp
                    
                    @forelse($subjects as $subject)
                        <div class="col-md-4 col-lg-3 mb-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="subject-icon mb-3">
                                        <i class="bi bi-journal-text fs-1 text-primary"></i>
                                    </div>
                                    <h5 class="card-title">{{ $subject->name }}</h5>
                                    <p class="card-text text-muted">{{ $subject->mcq_tests_count }} tests available</p>
                                    <a href="{{ route('tests.available') }}?subject={{ $subject->id }}" class="btn btn-sm btn-outline-primary">
                                        Browse Tests
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-4">
                            <p class="text-muted">No subjects with active tests available.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        // Subject filtering for tests
        document.addEventListener('DOMContentLoaded', function() {
            // Fix for subject filter dropdown
            const subjectLinks = document.querySelectorAll('.dropdown-item[data-subject]');
            const testItems = document.querySelectorAll('.test-item');
            
            subjectLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const subjectId = this.getAttribute('data-subject');
                    
                    // Update dropdown button text
                    document.getElementById('subjectFilter').textContent = 'Filter by Subject';
                    if (subjectId !== 'all') {
                        document.getElementById('subjectFilter').textContent = this.textContent;
                    }
                    
                    // Filter tests on dashboard
                    testItems.forEach(item => {
                        if (subjectId === 'all' || item.getAttribute('data-subject') === subjectId) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                    
                    // Update View All link to include subject filter
                    const viewAllLink = document.querySelector('.card-header .btn-primary');
                    if (viewAllLink) {
                        if (subjectId === 'all') {
                            viewAllLink.href = "{{ route('tests.available') }}";
                        } else {
                            viewAllLink.href = "{{ route('tests.available') }}?subject=" + subjectId;
                        }
                    }
                });
            });
        });
    </script>
    
    <script>
        // Separate script for test navigation to avoid conflicts
        document.addEventListener('DOMContentLoaded', function() {
            // Direct navigation for reattempt buttons
            const reattemptButtons = document.querySelectorAll('.reattempt-btn');
            reattemptButtons.forEach(button => {
                button.onclick = function(e) {
                    // Get the test URL from the href attribute
                    const testUrl = this.getAttribute('href');
                    
                    // Navigate directly to the test page
                    window.location.href = testUrl;
                    
                    // Prevent default action and stop propagation
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Force the navigation for extra measure
                    setTimeout(() => {
                        if (window.location.href !== testUrl) {
                            window.location.replace(testUrl);
                        }
                    }, 100);
                    
                    return false;
                };
            });
        });
    </script>
    @endpush
</x-student-layout> 