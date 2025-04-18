<x-student-layout>
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Available Tests</h1>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($tests->isEmpty())
            <div class="alert alert-info">
                <h5><i class="bi bi-info-circle"></i> No available tests found.</h5>
                <p>Tests become available when:</p>
                <ul>
                    <li>A test's start time has passed (it has started)</li>
                    <li>The test's end time is in the future (it hasn't ended yet)</li>
                    <li>The test is marked as active by the teacher</li>
                    <li>You haven't attempted the test already</li>
                </ul>
                <p>Check back later or contact your teacher if you believe a test should be available.</p>
            </div>
        @else
            <div class="row">
                @foreach($tests as $test)
                    @php
                        $hasAttempted = $test->hasBeenAttemptedBy(auth()->user());
                        $canTakeTest = $test->canBeTaken();
                        $isScheduled = $test->start_time > now();
                        $hasQuestions = $test->questions->count() > 0;
                        $attemptsCount = $test->getAttemptsCountByUser(auth()->user());
                        $maxAttempts = 5;
                        $remainingAttempts = $maxAttempts - $attemptsCount;

                        // Determine card border color
                        $borderClass = 'border-left-secondary';
                        if ($attemptsCount >= $maxAttempts) {
                            $borderClass = 'border-left-danger';
                        } elseif ($hasAttempted) {
                            $borderClass = 'border-left-info';
                        } elseif ($canTakeTest && $hasQuestions) {
                            $borderClass = 'border-left-primary';
                        }
                    @endphp
                    <div class="col-md-4 mb-4">
                        <div class="card shadow h-100 {{ $borderClass }} {{ ($attemptsCount >= $maxAttempts || !$canTakeTest) ? 'bg-light' : '' }}" 
                             style="{{ ($attemptsCount >= $maxAttempts || !$canTakeTest) ? 'opacity: 0.8;' : '' }}">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">{{ $test->title }}</h6>
                                <span class="badge bg-{{ $attemptsCount >= $maxAttempts ? 'danger' : ($hasAttempted ? 'info' : ($canTakeTest ? 'success' : ($isScheduled ? 'warning' : 'secondary'))) }}">
                                    @if($attemptsCount >= $maxAttempts)
                                        Max Attempts Reached
                                    @elseif($hasAttempted)
                                        Attempted {{ $attemptsCount }}/{{ $maxAttempts }}
                                    @elseif($canTakeTest)
                                        Available Now
                                    @elseif($isScheduled)
                                        Starts Soon
                                    @else
                                        Unavailable
                                    @endif
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <p><i class="bi bi-book"></i> <strong>Subject:</strong> {{ $test->subject->name }}</p>
                                    <p><i class="bi bi-stopwatch"></i> <strong>Duration:</strong> {{ $test->duration_minutes }} minutes</p>
                                    <p><i class="bi bi-question-circle"></i> <strong>Questions:</strong> {{ $test->questions->count() }}</p>
                                    <p><i class="bi bi-percent"></i> <strong>Passing Score:</strong> {{ $test->passing_percentage }}%</p>
                                </div>

                                @if($hasAttempted)
                                <div class="alert alert-info py-1 mt-2">
                                    <small><strong>Your attempts:</strong> {{ $attemptsCount }}/{{ $maxAttempts }}</small>
                                    @if($attemptsCount < $maxAttempts)
                                        <small class="d-block">{{ $remainingAttempts }} attempt(s) remaining</small>
                                    @endif
                                </div>
                                @endif

                                <div class="time-info mb-3">
                                    <div class="d-flex justify-content-between">
                                        <small><i class="bi bi-calendar-event"></i> Starts: {{ $test->start_time->format('M d, Y h:i A') }}</small>
                                        <small class="text-{{ now()->gt($test->start_time) ? 'success' : 'warning' }}">
                                            {{ now()->gt($test->start_time) ? 'Started' : 'Starts ' . $test->start_time->diffForHumans() }}
                                        </small>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <small><i class="bi bi-calendar-check"></i> Ends: {{ $test->end_time->format('M d, Y h:i A') }}</small>
                                        <small class="text-{{ now()->gt($test->end_time) ? 'danger' : 'info' }}">
                                            {{ now()->gt($test->end_time) ? 'Ended' : 'Ends ' . $test->end_time->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>

                                <div class="progress mb-3" style="height: 5px;">
                                    @php
                                        $now = now();
                                        $totalDuration = $test->end_time->diffInSeconds($test->start_time);
                                        $elapsed = $now->diffInSeconds($test->start_time);
                                        $progress = $totalDuration > 0 ? min(100, max(0, ($elapsed / $totalDuration) * 100)) : 0;
                                    @endphp
                                    <div class="progress-bar {{ $attemptsCount >= $maxAttempts ? 'bg-danger' : ($hasAttempted ? 'bg-info' : ($canTakeTest ? 'bg-success' : 'bg-secondary')) }}" role="progressbar" 
                                         style="width: {{ $progress }}%" 
                                         aria-valuenow="{{ $progress }}" 
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>

                                <div class="d-grid gap-2">
                                    @if($attemptsCount >= $maxAttempts)
                                        <button disabled class="btn btn-danger">
                                            <i class="bi bi-x-circle"></i> Maximum Attempts Reached
                                        </button>
                                    @elseif($hasAttempted && $canTakeTest && $hasQuestions)
                                        <a href="{{ route('test.attempt', $test->id) }}" class="btn btn-warning">
                                            <i class="bi bi-arrow-repeat"></i> Reattempt ({{ $attemptsCount }}/{{ $maxAttempts }})
                                        </a>
                                    @elseif($canTakeTest && $hasQuestions)
                                        <a href="{{ route('test.attempt', $test->id) }}" class="btn btn-primary">
                                            <i class="bi bi-pencil-square"></i> Attempt Test
                                        </a>
                                    @elseif($isScheduled && $hasQuestions)
                                        <button disabled class="btn btn-secondary">
                                            <i class="bi bi-clock"></i> Available from {{ $test->start_time->format('M d, h:i A') }}
                                        </button>
                                    @elseif(!$hasQuestions)
                                        <button disabled class="btn btn-danger">
                                            <i class="bi bi-exclamation-triangle"></i> No Questions Available
                                        </button>
                                    @else
                                        <button disabled class="btn btn-secondary">
                                            <i class="bi bi-lock"></i> Not Available
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <div class="card-footer bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Expires: {{ $test->end_time->format('M d, Y h:i A') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $tests->links() }}
            </div>
        @endif
    </div>

@push('styles')
<style>
    .border-left-primary {
        border-left: 4px solid #4e73df;
    }
    .border-left-secondary {
        border-left: 4px solid #858796;
    }
    .card-body {
        position: relative;
    }
    .time-info {
        font-size: 0.85rem;
    }
</style>
@endpush
</x-student-layout> 