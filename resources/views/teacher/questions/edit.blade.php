<x-teacher-layout>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Edit Question</h2>
            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('questions.update', $question) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="mcq_test_id" class="form-label">Test</label>
                        <select name="mcq_test_id" id="mcq_test_id" class="form-select @error('mcq_test_id') is-invalid @enderror" required>
                            <option value="">Select Test</option>
                            @foreach($tests as $test)
                                <option value="{{ $test->id }}" {{ old('mcq_test_id', $question->mcq_test_id) == $test->id ? 'selected' : '' }}>
                                    {{ $test->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('mcq_test_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="question_text" class="form-label">Question Text</label>
                        <textarea name="question_text" id="question_text" rows="3" class="form-control @error('question_text') is-invalid @enderror" required>{{ old('question_text', $question->question_text) }}</textarea>
                        @error('question_text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="options-container">
                        <label class="form-label">Options</label>
                        @foreach($question->options as $index => $option)
                            <div class="option-group mb-3">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <input type="checkbox" name="correct_option[]" value="{{ $index }}"
                                                @if(is_array($question->correct_option) && in_array($option, $question->correct_option)) checked @endif>
                                        </div>
                                    </div>
                                    <input type="text" name="options[]" class="form-control @error('options.'.$index) is-invalid @enderror" 
                                           value="{{ old('options.'.$index, $option) }}" required>
                                    <button type="button" class="btn btn-danger remove-option">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                                @error('options.'.$index)
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach
                    </div>

                    <button type="button" class="btn btn-secondary mb-3" id="add-option">
                        <i class="bi bi-plus"></i> Add Option
                    </button>

                    <div class="mb-3">
                        <label for="marks" class="form-label">Marks</label>
                        <input type="number" name="marks" id="marks" class="form-control @error('marks') is-invalid @enderror" 
                               value="{{ old('marks', $question->marks) }}" required min="1">
                        @error('marks')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="explanation" class="form-label">Explanation (Optional)</label>
                        <textarea name="explanation" id="explanation" rows="3" class="form-control @error('explanation') is-invalid @enderror">{{ old('explanation', $question->explanation) }}</textarea>
                        @error('explanation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Question
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            // Add new option field
            $('#add-option').click(function() {
                const optionCount = $('.option-group').length;
                const optionGroup = `
                    <div class="option-group mb-3">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <input type="checkbox" name="correct_option[]" value="${optionCount}">
                                </div>
                            </div>
                            <input type="text" name="options[]" class="form-control" required>
                            <button type="button" class="btn btn-danger remove-option">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                $('#options-container').append(optionGroup);
            });

            // Remove option field
            $(document).on('click', '.remove-option', function() {
                if ($('.option-group').length > 2) {
                    $(this).closest('.option-group').remove();
                    // Update checkbox values to match their position
                    updateCheckboxValues();
                } else {
                    alert('A question must have at least 2 options.');
                }
            });

            // Update checkbox values when options are removed
            function updateCheckboxValues() {
                $('.option-group').each(function(index) {
                    $(this).find('input[type="checkbox"]').val(index);
                });
            }
        });
    </script>
    @endpush
</x-teacher-layout> 