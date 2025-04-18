<x-teacher-layout>
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">{{ $mcqTest->title }}</h1>
            <div>
                <a href="{{ route('mcq-tests.edit', $mcqTest) }}" class="btn btn-sm btn-primary mr-2">
                    <i class="bi bi-pencil"></i> Edit Test
                </a>
                <a href="{{ route('teacher.dashboard') }}" class="btn btn-sm btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <!-- Test Details Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Test Details</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Subject:</strong> {{ $mcqTest->subject->name }}</p>
                        <p><strong>Duration:</strong> {{ $mcqTest->duration_minutes }} minutes</p>
                        <p><strong>Passing Percentage:</strong> {{ $mcqTest->passing_percentage }}%</p>
                        <p><strong>Total Marks:</strong> {{ $mcqTest->total_marks }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Start Time:</strong> {{ $mcqTest->start_time->format('Y-m-d H:i') }}</p>
                        <p><strong>End Time:</strong> {{ $mcqTest->end_time->format('Y-m-d H:i') }}</p>
                        <p><strong>Status:</strong> 
                            @if($mcqTest->end_time < now())
                                <span class="badge bg-danger">Ended</span>
                            @elseif($mcqTest->start_time > now())
                                <span class="badge bg-info">Scheduled</span>
                            @else
                                <span class="badge bg-success">Active</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="mt-3">
                    <p><strong>Description:</strong></p>
                    <p>{{ $mcqTest->description }}</p>
                </div>
            </div>
        </div>

        <!-- Questions Section -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Questions</h6>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#importQuestionsModal">
                    <i class="bi bi-upload"></i> Import Questions
                </button>
            </div>
            <div class="card-body">
                @if($mcqTest->questions->isEmpty())
                    <div class="text-center py-5">
                        <p class="text-muted">No questions added yet.</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                            <i class="bi bi-plus-circle"></i> Add Question
                        </button>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Question</th>
                                    <th>Options</th>
                                    <th>Correct Option</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($mcqTest->questions as $index => $question)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $question->question_text }}</td>
                                        <td>
                                            @php
                                                $options = is_string($question->options) 
                                                    ? json_decode($question->options) 
                                                    : $question->options;
                                            @endphp
                                            @foreach($options as $key => $option)
                                                {{ strtoupper($key) }}) {{ $option }}<br>
                                            @endforeach
                                        </td>
                                        <td>
                                            @php
                                                $correctOptions = is_string($question->correct_option) 
                                                    ? json_decode($question->correct_option) 
                                                    : $question->correct_option;
                                                    
                                                if (!empty($correctOptions)) {
                                                    if (is_array($correctOptions)) {
                                                        echo implode(', ', array_map('strtoupper', $correctOptions));
                                                    } else {
                                                        echo strtoupper($correctOptions);
                                                    }
                                                } else {
                                                    echo '<span class="text-danger">Not set</span>';
                                                }
                                            @endphp
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="editQuestion({{ $question->id }})">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteQuestion({{ $question->id }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                            <i class="bi bi-plus-circle"></i> Add Question
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Import Questions Modal -->
    <div class="modal fade" id="importQuestionsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Questions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('mcq-tests.questions.import', $mcqTest) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="questions_text" class="form-label">Paste Questions</label>
                            <div class="alert alert-info">
                                <strong>Format Instructions:</strong>
                                <ul>
                                    <li>Each question should be on its own line</li>
                                    <li>Options should be formatted as a), b), c), d) followed by the option text</li>
                                    <li>Leave a blank line between questions</li>
                                    <li>After importing, you'll need to edit each question to mark the correct option</li>
                                </ul>
                            </div>
                            <textarea class="form-control" id="questions_text" name="questions_text" rows="10" 
                                placeholder="Example:
Which of the following is NOT a characteristic of a chemical reaction?
a) Change in color
b) Evolution of gas
c) Change in shape without new substance formation
d) Change in temperature

A combination reaction is a reaction in which:
a) One reactant breaks down into two or more products
b) Two or more reactants combine to form a single product
c) A compound reacts with oxygen to release heat
d) An insoluble substance is formed in solution"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Import Questions</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Question Modal -->
    <div class="modal fade" id="addQuestionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('mcq-tests.questions.store', $mcqTest) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="question_text" class="form-label">Question</label>
                            <textarea class="form-control" id="question_text" name="questions[0][question_text]" rows="3" required></textarea>
                        </div>
                        @foreach(['a', 'b', 'c', 'd'] as $option)
                            <div class="mb-3">
                                <label for="option_{{ $option }}" class="form-label">Option {{ strtoupper($option) }}</label>
                                <input type="text" class="form-control" id="option_{{ $option }}" 
                                    name="questions[0][options][{{ $option }}]" required>
                            </div>
                        @endforeach
                        <div class="mb-3">
                            <label for="correct_option" class="form-label">Correct Option</label>
                            <select class="form-control" id="correct_option" name="questions[0][correct_option]" required>
                                <option value="">Select correct option</option>
                                @foreach(['a', 'b', 'c', 'd'] as $option)
                                    <option value="{{ $option }}">Option {{ strtoupper($option) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="explanation" class="form-label">Explanation (Optional)</label>
                            <textarea class="form-control" id="explanation" name="questions[0][explanation]" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Question</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function editQuestion(questionId) {
            // Redirect to the question edit route using the named route
            window.location.href = "{{ url('teacher/questions') }}/" + questionId + "/edit";
        }

        function deleteQuestion(questionId) {
            if (confirm('Are you sure you want to delete this question?')) {
                // Create a form and submit it for DELETE method
                let form = document.createElement('form');
                form.method = 'POST';
                form.action = '/teacher/questions/' + questionId;
                
                let methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                
                let csrfField = document.createElement('input');
                csrfField.type = 'hidden';
                csrfField.name = '_token';
                csrfField.value = '{{ csrf_token() }}';
                
                form.appendChild(methodField);
                form.appendChild(csrfField);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    @endpush
</x-teacher-layout> 