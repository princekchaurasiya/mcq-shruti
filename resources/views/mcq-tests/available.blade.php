<x-student-layout>
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Available Tests</h1>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if($tests->isEmpty())
            <div class="alert alert-info">
                No tests are available at the moment.
            </div>
        @else
            <div class="row">
                @foreach($tests as $test)
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card shadow h-100">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">{{ $test->title }}</h6>
                                <div class="dropdown no-arrow">
                                    <span class="badge badge-{{ $test->status === 'active' ? 'success' : 'warning' }}">
                                        {{ ucfirst($test->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <p class="mb-2">{{ Str::limit($test->description, 100) }}</p>
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i> Duration: {{ $test->formattedDuration }}
                                    </small>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted d-block d-flex justify-content-between">
                                        <span><i class="bi bi-calendar"></i> Starts: {{ $test->start_time->format('M d, Y h:i A') }}</span>
                                        <span class="badge {{ now()->gt($test->start_time) ? 'bg-success' : 'bg-warning' }}">
                                            {{ now()->gt($test->start_time) ? 'Started' : 'Upcoming' }}
                                        </span>
                                    </small>
                                    <small class="text-muted d-block d-flex justify-content-between mt-1">
                                        <span><i class="bi bi-calendar-check"></i> Ends: {{ $test->end_time->format('M d, Y h:i A') }}</span>
                                        <span class="badge bg-info">
                                            {{ $test->end_time->diffForHumans() }}
                                        </span>
                                    </small>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted d-block">
                                        <i class="bi bi-award"></i> Passing Score: {{ $test->passing_percentage }}%
                                    </small>
                                    <small class="text-muted d-block">
                                        <i class="bi bi-person"></i> Created by: {{ $test->teacher->user->name }}
                                    </small>
                                </div>
                                <div class="progress mb-3" style="height: 5px;">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: {{ min(100, max(0, (now()->diffInSeconds($test->start_time) / now()->diffInSeconds($test->end_time)) * 100)) }}%" 
                                         aria-valuenow="{{ min(100, max(0, (now()->diffInSeconds($test->start_time) / now()->diffInSeconds($test->end_time)) * 100)) }}" 
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                @if($test->isAvailable())
                                    <a href="{{ route('test.attempt', $test) }}" class="btn btn-primary btn-block">
                                        <i class="bi bi-pencil-square me-1"></i> Start Test
                                    </a>
                                @else
                                    <button disabled class="btn btn-secondary btn-block">
                                        <i class="bi bi-lock me-1"></i> Not Available
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $tests->links() }}
            </div>
        @endif
    </div>
</x-student-layout> 