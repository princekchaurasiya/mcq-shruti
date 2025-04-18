<x-admin-layout>
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Admin Dashboard</h1>
        </div>

        <!-- Stats Cards -->
        <div class="row">
            <!-- Total Teachers Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Teachers</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ App\Models\Teacher::count() }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-person-video3 fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Students Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Students</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ App\Models\Student::count() }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-mortarboard fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Subjects Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Subjects</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ App\Models\Subject::count() }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-book fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Tests Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Total Tests</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ App\Models\McqTest::count() }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-file-text fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row">
            <!-- Recent Teachers -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Recent Teachers</h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            @foreach(App\Models\Teacher::with('user', 'subject')->latest()->take(5)->get() as $teacher)
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $teacher->user->name ?? 'N/A' }}</h6>
                                        <small>{{ $teacher->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-1">Subject: {{ $teacher->subject->name ?? 'Not Assigned' }}</p>
                                    <small class="text-muted">Experience: {{ $teacher->experience_years }} years</small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Students -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Recent Students</h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            @foreach(App\Models\Student::with('user')->latest()->take(5)->get() as $student)
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $student->user->name ?? 'N/A' }}</h6>
                                        <small>{{ $student->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-1">Roll Number: {{ $student->roll_number }}</p>
                                    <small class="text-muted">Batch: {{ $student->batch }}</small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Tests -->
        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Recent Tests</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $recentTests = App\Models\MCQTest::with(['teacher.user', 'subject'])
                                ->latest()
                                ->take(10)
                                ->get();
                        @endphp
                        
                        @if($recentTests->isEmpty())
                            <div class="text-center py-4">
                                <p class="text-muted">No tests found in the system.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Test Title</th>
                                            <th>Subject</th>
                                            <th>Created By</th>
                                            <th>Duration</th>
                                            <th>Questions</th>
                                            <th>Attempts</th>
                                            <th>Timeline</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentTests as $test)
                                            @php
                                                $statusClass = '';
                                                if($test->end_time < now()) {
                                                    $statusClass = 'test-ended';
                                                } elseif($test->start_time > now()) {
                                                    $statusClass = 'test-scheduled';
                                                } elseif(!$test->is_active) {
                                                    $statusClass = 'test-inactive';
                                                } else {
                                                    $statusClass = 'test-active';
                                                }
                                            @endphp
                                            <tr class="{{ $statusClass }}">
                                                <td>{{ $test->title }}</td>
                                                <td>{{ $test->subject->name }}</td>
                                                <td>{{ $test->teacher->user->name }}</td>
                                                <td>{{ $test->duration_minutes }} mins</td>
                                                <td>{{ $test->questions->count() }} questions</td>
                                                <td>{{ $test->attempts->count() }} attempts</td>
                                                <td>
                                                    <div class="test-timeline">
                                                        <div class="progress" style="height: 4px;">
                                                            @php
                                                                $now = now();
                                                                $totalDuration = $test->end_time->diffInSeconds($test->start_time);
                                                                $elapsed = $now->diffInSeconds($test->start_time);
                                                                $progress = $totalDuration > 0 ? min(100, max(0, ($elapsed / $totalDuration) * 100)) : 0;
                                                                
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
                                                        <div class="d-flex justify-content-between">
                                                            <small>{{ $test->start_time->format('M d, h:i A') }}</small>
                                                            <small>{{ $test->end_time->format('M d, h:i A') }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($test->end_time < now())
                                                        <span class="badge bg-danger">Ended</span>
                                                    @elseif($test->start_time > now())
                                                        <span class="badge bg-info">Scheduled</span>
                                                    @elseif($test->is_active)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @push('styles')
    <style>
        /* Test status styling */
        tr.test-ended td {
            opacity: 0.7;
            background-color: #f8f9fc !important;
        }
        
        tr.test-inactive td {
            opacity: 0.75;
            font-style: italic;
        }
        
        tr.test-scheduled td {
            background-color: #eaf6ff !important;
        }
        
        tr.test-active td {
            background-color: #f0fff4 !important;
        }
        
        .test-timeline {
            width: 180px;
        }
        
        .test-timeline small {
            font-size: 0.7rem;
        }
    </style>
    @endpush
</x-admin-layout> 