<x-teacher-layout>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Question Details</h2>
            <div>
                <a href="{{ route('teacher.questions.edit', $question) }}" class="btn btn-primary me-2">
                    <i class="bi bi-pencil"></i> Edit Question
                </a>
                <a href="{{ route('teacher.questions.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Questions
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Test Information</h5>
                        <p class="mb-1"><strong>Test Title:</strong> {{ $question->test->title }}</p>
                        <p class="mb-1"><strong>Subject:</strong> {{ $question->test->subject }}</p>
                        <p class="mb-0"><strong>Created:</strong> {{ $question->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    <div class="col-md-6">
                        <h5>Question Details</h5>
                        <p class="mb-1"><strong>Marks:</strong> {{ $question->marks }}</p>
                        <p class="mb-0"><strong>Last Updated:</strong> {{ $question->updated_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>

                <div class="mb-4">
                    <h5>Question Text</h5>
                    <div class="card bg-light">
                        <div class="card-body">
                            {{ $question->question_text }}
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h5>Options</h5>
                    <div class="list-group">
                        @foreach($question->options as $option)
                            <div class="list-group-item {{ $option === $question->correct_option ? 'list-group-item-success' : '' }}">
                                <div class="d-flex align-items-center">
                                    @if($option === $question->correct_option)
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    @else
                                        <i class="bi bi-circle me-2"></i>
                                    @endif
                                    {{ $option }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if($question->explanation)
                    <div>
                        <h5>Explanation</h5>
                        <div class="card bg-light">
                            <div class="card-body">
                                {{ $question->explanation }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-teacher-layout> 