<x-student-layout>
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">My Test Results</h1>
            <a href="{{ route('student.dashboard') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                <i class="bi bi-arrow-left me-2"></i> Back to Dashboard
            </a>
        </div>

        <!-- Results Table -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">All Test Results</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="resultsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Test Title</th>
                                <th>Subject</th>
                                <th>Score</th>
                                <th>Completed At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results as $result)
                                <tr>
                                    <td>{{ $result->mcqTest->title ?? 'N/A' }}</td>
                                    <td>{{ $result->mcqTest->subject->name ?? 'N/A' }}</td>
                                    <td>{{ $result->score }}%</td>
                                    <td>{{ $result->completed_at ? $result->completed_at->format('M d, Y H:i') : 'In Progress' }}</td>
                                    <td>
                                        @if($result->completed_at)
                                            <a href="{{ route('results.show', $result->id) }}" class="btn btn-sm btn-primary">
                                                View Details
                                            </a>
                                        @else
                                            <span class="badge bg-warning text-dark">In Progress</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($results->isEmpty())
                    <div class="text-center py-4">
                        <p class="text-muted">No test results found.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#resultsTable').DataTable({
                order: [[3, 'desc']]
            });
        });
    </script>
    @endpush
</x-student-layout> 