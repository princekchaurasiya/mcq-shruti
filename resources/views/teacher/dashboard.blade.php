<x-teacher-layout>
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Teacher Dashboard</h1>
            <a href="{{ route('mcq-tests.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="bi bi-plus-circle me-2"></i> Create New Test
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="row">
            <!-- Total Tests Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    My Tests</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    @php
                                        $teacher = Auth::user()->teacher;
                                        $totalTests = $teacher ? $teacher->mcqTests()->count() : 0;
                                    @endphp
                                    {{ $totalTests }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-file-text fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Tests Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Active Tests</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    @php
                                        $activeTests = $teacher ? $teacher->mcqTests()->where('end_time', '>', now())->where('is_active', true)->count() : 0;
                                    @endphp
                                    {{ $activeTests }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-clock-history fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Questions Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Questions</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    @php
                                        $totalQuestions = 0;
                                        if ($teacher) {
                                            $totalQuestions = DB::table('questions')
                                                ->join('mcq_tests', 'questions.mcq_test_id', '=', 'mcq_tests.id')
                                                ->where('mcq_tests.teacher_id', $teacher->id)
                                                ->count();
                                        }
                                    @endphp
                                    {{ $totalQuestions }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-question-circle fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Attempts Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Total Attempts</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    @php
                                        $totalAttempts = $teacher ? DB::table('test_attempts')
                                            ->join('mcq_tests', 'test_attempts.mcq_test_id', '=', 'mcq_tests.id')
                                            ->where('mcq_tests.teacher_id', $teacher->id)
                                            ->count() : 0;
                                    @endphp
                                    {{ $totalAttempts }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-people fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row">
            <!-- Recent Tests -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Recent Tests</h6>
                        <a href="{{ route('mcq-tests.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="test-list" style="max-height: 650px; overflow-y: auto;">
                            @foreach($tests as $test)
                                <div class="card mb-3 mx-3 mt-3 test-card 
                                    @if($test->end_time < now()) test-ended @elseif(!$test->is_active) test-inactive @endif">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="card-title">
                                                <a href="{{ route('mcq-tests.show', $test) }}" class="text-decoration-none">
                                                    {{ $test->title }}
                                                </a>
                                            </h5>
                                            <span class="badge 
                                                @if($test->end_time < now()) 
                                                    bg-danger
                                                @elseif($test->start_time > now())
                                                    bg-info
                                                @elseif($test->is_active)
                                                    bg-success
                                                @else
                                                    bg-secondary
                                                @endif
                                                ">
                                                @if($test->end_time < now())
                                                    Ended
                                                @elseif($test->start_time > now())
                                                    Scheduled
                                                @elseif($test->is_active)
                                                    Active
                                                @else
                                                    Inactive
                                                @endif
                                            </span>
                                        </div>
                                        <p class="card-text text-muted small">Created {{ $test->created_at->diffForHumans() }}</p>
                                        
                                        <div class="row mt-2">
                                            <div class="col-md-6">
                                                <p class="mb-1"><i class="bi bi-stopwatch"></i> <strong>Duration:</strong> {{ $test->duration_minutes }} min</p>
                                                <p class="mb-1"><i class="bi bi-book"></i> <strong>Subject:</strong> {{ $test->subject->name ?? 'N/A' }}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1"><i class="bi bi-question-circle"></i> <strong>Questions:</strong> {{ $test->questions->count() }}</p>
                                                <p class="mb-1"><i class="bi bi-people"></i> <strong>Attempts:</strong> {{ $test->attempts->count() }}</p>
                                            </div>
                                        </div>
                                        
                                        <div class="test-timeline mt-3 mb-3">
                                            <div class="progress" style="height: 4px;">
                                                @php
                                                    $now = now();
                                                    $totalDuration = $test->end_time->diffInSeconds($test->start_time);
                                                    $elapsed = $now->diffInSeconds($test->start_time);
                                                    $progress = $totalDuration > 0 ? min(100, max(0, ($elapsed / $totalDuration) * 100)) : 0;
                                                    
                                                    // Different progress bar colors based on status
                                                    $progressClass = 'bg-primary';
                                                    if ($test->end_time < $now) {
                                                        $progressClass = 'bg-danger';
                                                        $progress = 100;
                                                    } elseif ($test->start_time > $now) {
                                                        $progressClass = 'bg-info';
                                                        $progress = 0;
                                                    } elseif (!$test->is_active) {
                                                        $progressClass = 'bg-secondary';
                                                    }
                                                @endphp
                                                <div class="progress-bar {{ $progressClass }}" role="progressbar" 
                                                    style="width: {{ $progress }}%" 
                                                    aria-valuenow="{{ $progress }}" 
                                                    aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="d-flex justify-content-between mt-1">
                                                <small class="text-{{ $now->gte($test->start_time) ? 'success' : 'muted' }}">
                                                    {{ $test->start_time->format('M d, Y h:i A') }}
                                                </small>
                                                <small class="text-{{ $now->gte($test->end_time) ? 'danger' : 'muted' }}">
                                                    {{ $test->end_time->format('M d, Y h:i A') }}
                                                </small>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-3 d-flex justify-content-between">
                                            <div>
                                                @if($test->attempts->count() > 0)
                                                    <small class="d-flex align-items-center">
                                                        <span class="me-2">{{ $test->attempts->count() }} students attempted</span>
                                                        <div class="student-avatars d-flex">
                                                            @foreach($test->attempts->take(3) as $attempt)
                                                                <div class="avatar-circle me-1" data-bs-toggle="tooltip" title="{{ $attempt->user->name ?? 'Student' }}">
                                                                    {{ substr($attempt->user->name ?? 'S', 0, 1) }}
                                                                </div>
                                                            @endforeach
                                                            @if($test->attempts->count() > 3)
                                                                <div class="avatar-circle bg-secondary me-1" data-bs-toggle="tooltip" title="{{ $test->attempts->count() - 3 }} more">+{{ $test->attempts->count() - 3 }}</div>
                                                            @endif
                                                        </div>
                                                    </small>
                                                @else
                                                    <small class="text-muted">No attempts yet</small>
                                                @endif
                                            </div>
                                            <div>
                                                <a href="{{ route('mcq-tests.results', $test) }}" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-bar-chart"></i> Results
                                                </a>
                                                <a href="{{ route('mcq-tests.edit', $test) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            @if($tests->isEmpty())
                                <div class="text-center py-4">
                                    <p class="text-muted">No tests created yet.</p>
                                    <a href="{{ route('mcq-tests.create') }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-plus-circle"></i> Create New Test
                                    </a>
                                </div>
                            @endif
                        </div>
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center py-3">
                            {{ $tests->appends(['results_page' => request()->results_page])->links() }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Results -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Recent Results</h6>
                        <a href="{{ route('teacher.results.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="test-list" style="max-height: 650px; overflow-y: auto;">
                            @forelse($recentResults as $result)
                                <div class="card mb-3 mx-3 mt-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="card-title">
                                                <a href="{{ route('mcq-tests.show', $result->mcqTest) }}" class="text-decoration-none">
                                                    {{ $result->mcqTest->title ?? 'Unknown Test' }}
                                                </a>
                                            </h5>
                                            <span class="badge bg-{{ $result->score >= ($result->mcqTest->passing_percentage ?? 0) ? 'success' : 'danger' }}">
                                                {{ $result->score }}%
                                            </span>
                                        </div>
                                        <p class="card-text text-muted small">
                                            {{ $result->user->name ?? 'Unknown Student' }} - {{ $result->completed_at ? $result->completed_at->diffForHumans() : 'Not completed' }}
                                        </p>
                                        
                                        <div class="progress mt-2" style="height: 10px;">
                                            <div class="progress-bar {{ $result->score >= ($result->mcqTest->passing_percentage ?? 0) ? 'bg-success' : 'bg-danger' }}" 
                                                role="progressbar" 
                                                style="width: {{ $result->score }}%;" 
                                                aria-valuenow="{{ $result->score }}" 
                                                aria-valuemin="0" 
                                                aria-valuemax="100">
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <div>
                                                <small class="text-muted">Duration: {{ $result->duration_minutes ?? 'N/A' }} min</small>
                                            </div>
                                            <a href="{{ route('tests.student.attempt.show', $result->id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <p class="text-muted">No test results yet.</p>
                                </div>
                            @endforelse
                        </div>
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center py-3">
                            {{ $recentResults->appends(['tests_page' => request()->tests_page])->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        /* Test card status styling */
        .test-card.test-ended {
            opacity: 0.75;
            border-left: 4px solid #e74a3b;
        }
        
        .test-card.test-inactive {
            opacity: 0.75;
            border-left: 4px solid #858796;
        }
        
        .test-card:not(.test-ended):not(.test-inactive) {
            border-left: 4px solid #4e73df;
        }
        
        .avatar-circle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background-color: #4e73df;
            color: white;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        .test-timeline {
            position: relative;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        });
    </script>
    @endpush
</x-teacher-layout> 