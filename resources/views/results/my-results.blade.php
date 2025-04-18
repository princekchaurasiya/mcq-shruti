<x-student-layout>
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">My Test Results</h1>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($results->isEmpty())
            <div class="alert alert-info">
                You haven't attempted any tests yet.
            </div>
        @else
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Test Attempts</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Test Title</th>
                                    <th>Teacher</th>
                                    <th>Attempted On</th>
                                    <th>Score</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($results as $result)
                                    <tr>
                                        <td>{{ $result->mcqTest->title }}</td>
                                        <td>{{ $result->mcqTest->teacher->user->name }}</td>
                                        <td>{{ $result->created_at->format('M d, Y h:i A') }}</td>
                                        <td>{{ number_format($result->score, 1) }}%</td>
                                        <td>
                                            @if($result->score >= $result->mcqTest->passing_percentage)
                                                <span class="badge badge-success">Passed</span>
                                            @else
                                                <span class="badge badge-danger">Failed</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('results.show', $result) }}" class="btn btn-sm btn-primary">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $results->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-student-layout> 