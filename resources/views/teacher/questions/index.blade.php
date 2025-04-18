<x-teacher-layout>
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Questions Bank</h1>
            <a href="{{ route('questions.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="bi bi-plus-circle me-2"></i> Create New Question
            </a>
        </div>

        <!-- Questions Table -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">All Questions</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="questionsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Question</th>
                                <th>Test</th>
                                <th>Subject</th>
                                <th>Marks</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($questions as $question)
                                <tr>
                                    <td>{{ Str::limit($question->question_text, 50) }}</td>
                                    <td>{{ $question->mcqTest->title }}</td>
                                    <td>{{ $question->mcqTest->subject->name ?? 'N/A' }}</td>
                                    <td>{{ $question->marks }}</td>
                                    <td>
                                        <a href="{{ route('questions.show', $question) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('questions.edit', $question) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('questions.destroy', $question) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this question?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($questions->isEmpty())
                    <div class="text-center py-4">
                        <p class="text-muted">No questions found.</p>
                    </div>
                @else
                    <div class="mt-4">
                        {{ $questions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#questionsTable').DataTable({
                order: [[1, 'asc']],
                pageLength: 25
            });
        });
    </script>
    @endpush
</x-teacher-layout> 