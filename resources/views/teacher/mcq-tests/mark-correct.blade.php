<x-teacher-layout>
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Mark Correct Options</h1>
            <a href="{{ route('mcq-tests.show', $mcqTest) }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Test
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Mark Correct Answers for Imported Questions</h6>
            </div>
            <div class="card-body">
                <p class="mb-4">Please select the correct option(s) for each question. You can select multiple options for questions with multiple correct answers.</p>

                <form action="{{ route('mcq-tests.questions.update-correct', $mcqTest) }}" method="POST">
                    @csrf
                    
                    @foreach($questions as $index => $question)
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <strong>Question {{ $index + 1 }}</strong>
                            </div>
                            <div class="card-body">
                                <input type="hidden" name="questions[{{ $index }}][id]" value="{{ $question->id }}">
                                
                                <div class="mb-3">
                                    <h5>{{ $question->question_text }}</h5>
                                </div>
                                
                                <div class="mb-3">
                                    @php
                                        $options = is_string($question->options) ? json_decode($question->options, true) : $question->options;
                                    @endphp
                                    
                                    @if(is_array($options))
                                        @if(array_keys($options) !== range(0, count($options) - 1))
                                            {{-- Associative array like {'a': 'Option A'} --}}
                                            @foreach($options as $key => $value)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                        name="questions[{{ $index }}][correct_option][]" 
                                                        value="{{ $key }}" 
                                                        id="q{{ $question->id }}_opt{{ $key }}">
                                                    <label class="form-check-label" for="q{{ $question->id }}_opt{{ $key }}">
                                                        {{ strtoupper($key) }}) {{ $value }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        @else
                                            {{-- Sequential array --}}
                                            @foreach($options as $optIndex => $option)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                        name="questions[{{ $index }}][correct_option][]" 
                                                        value="{{ $optIndex }}" 
                                                        id="q{{ $question->id }}_opt{{ $optIndex }}">
                                                    <label class="form-check-label" for="q{{ $question->id }}_opt{{ $optIndex }}">
                                                        {{ chr(97 + $optIndex) }}) {{ $option }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Save Correct Answers
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-teacher-layout> 