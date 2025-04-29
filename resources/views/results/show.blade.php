<x-student-layout>
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Test Result Details</h1>
            <a href="{{ route('results.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Results
            </a>
        </div>

        <!-- Test Information Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">{{ $result->mcqTest->title }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Teacher:</strong> {{ $result->mcqTest->teacher->user->name }}</p>
                        <p><strong>Attempted On:</strong> {{ $result->created_at->format('M d, Y h:i A') }}</p>
                        <p><strong>Completed On:</strong> {{ $result->completed_at->format('M d, Y h:i A') }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Score:</strong> {{ number_format($result->score, 1) }}%</p>
                        <p><strong>Passing Score:</strong> {{ $result->mcqTest->passing_percentage }}%</p>
                        <p>
                            <strong>Status:</strong>
                            @if($result->score >= $result->mcqTest->passing_percentage)
                                <span class="badge badge-success">Passed</span>
                            @else
                                <span class="badge badge-danger">Failed</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Answers Review -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Question Review</h6>
            </div>
            <div class="card-body">
                @foreach($result->responses as $response)
                    <div class="mb-4 p-3 {{ $response->is_correct ? 'border-left-success' : 'border-left-danger' }} bg-white shadow-sm">
                        <h6 class="mb-3">{{ $loop->iteration }}. {{ $response->question->question_text }}</h6>
                        
                        <p><strong>Your Answer:</strong> {{ $response->selectedOption->option_text }}</p>
                        
                        @if(!$response->is_correct)
                            <p><strong>Correct Answer:</strong> 
                                {{ $response->question->options->where('is_correct', true)->first()->option_text }}
                            </p>
                        @endif

                        @if($response->question->explanation)
                            <div class="mt-2">
                                <strong>Explanation:</strong>
                                <p class="text-muted">{{ $response->question->explanation }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-student-layout> 