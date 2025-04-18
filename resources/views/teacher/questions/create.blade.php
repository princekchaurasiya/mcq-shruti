<x-teacher-layout>
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Create New Question</h1>
            <a href="{{ route('teacher.questions.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                <i class="bi bi-arrow-left me-2"></i> Back to Questions
            </a>
        </div>

        <!-- Question Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Question Details</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('teacher.questions.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="mcq_test_id" class="form-label">Select Test</label>
                        <select name="mcq_test_id" id="mcq_test_id" class="form-control @error('mcq_test_id') is-invalid @enderror" required>
                            <option value="">Select a test...</option>
                            @foreach($tests as $test)
                                <option value="{{ $test->id }}" {{ old('mcq_test_id') == $test->id ? 'selected' : '' }}>
                                    {{ $test->title }} ({{ $test->subject->name ?? 'No Subject' }})
                                </option>
                            @endforeach
                        </select>
                        @error('mcq_test_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="question_text" class="form-label">Question Text</label>
                        <textarea name="question_text" id="question_text" rows="3" class="form-control @error('question_text') is-invalid @enderror" required>{{ old('question_text') }}</textarea>
                        @error('question_text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Question Image (Optional)</label>
                        <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Options</label>
                        <div id="options-container">
                            @for($i = 0; $i < 4; $i++)
                                <div class="input-group mb-2">
                                    <div class="input-group-text">
                                        <input type="checkbox" name="correct_option[]" value="{{ $i }}" 
                                               {{ in_array($i, old('correct_option', [])) ? 'checked' : '' }}
                                               class="form-check-input mt-0" aria-label="Correct option checkbox">
                                    </div>
                                    <input type="text" name="options[]" class="form-control @error('options.'.$i) is-invalid @enderror" 
                                           value="{{ old('options.'.$i) }}" placeholder="Option {{ $i + 1 }}" required>
                                    @if($i > 1)
                                        <button type="button" class="btn btn-danger remove-option">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                                @error('options.'.$i)
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            @endfor
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="addOption()">
                            <i class="bi bi-plus-circle"></i> Add Option
                        </button>
                    </div>

                    <div class="mb-3">
                        <label for="marks" class="form-label">Marks</label>
                        <input type="number" name="marks" id="marks" class="form-control @error('marks') is-invalid @enderror" 
                               value="{{ old('marks', 1) }}" min="1" required>
                        @error('marks')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="explanation" class="form-label">Explanation (Optional)</label>
                        <textarea name="explanation" id="explanation" rows="3" class="form-control @error('explanation') is-invalid @enderror">{{ old('explanation') }}</textarea>
                        @error('explanation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Create Question</button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function addOption() {
            const container = document.getElementById('options-container');
            const optionCount = container.children.length;
            const newOption = document.createElement('div');
            newOption.className = 'input-group mb-2';
            newOption.innerHTML = `
                <div class="input-group-text">
                    <input type="checkbox" name="correct_option[]" value="${optionCount}" 
                           class="form-check-input mt-0" aria-label="Correct option checkbox">
                </div>
                <input type="text" name="options[]" class="form-control" 
                       placeholder="Option ${optionCount + 1}" required>
                <button type="button" class="btn btn-danger remove-option">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            container.appendChild(newOption);
        }

        $(document).on('click', '.remove-option', function() {
            if ($('.input-group').length > 2) {
                $(this).closest('.input-group').remove();
                // Update the values of correct_option checkboxes
                $('.input-group').each(function(index) {
                    $(this).find('input[type="checkbox"]').val(index);
                });
            } else {
                alert('A question must have at least 2 options.');
            }
        });
    </script>
    @endpush
</x-teacher-layout> 