<x-teacher-layout>
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Edit Test</h1>
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
                <h6 class="m-0 font-weight-bold text-primary">Test Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('mcq-tests.update', $mcqTest) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Title</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                    id="title" name="title" value="{{ old('title', $mcqTest->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="subject_id">Subject</label>
                                <input type="text" class="form-control" 
                                    value="{{ $mcqTest->subject->name }}" disabled>
                                <small class="form-text text-muted">Subject cannot be changed after creation</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="description">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                            id="description" name="description" rows="3" required>{{ old('description', $mcqTest->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="duration_minutes">Duration (minutes)</label>
                                <input type="number" class="form-control @error('duration_minutes') is-invalid @enderror" 
                                    id="duration_minutes" name="duration_minutes" 
                                    value="{{ old('duration_minutes', $mcqTest->duration_minutes) }}" min="1" required>
                                @error('duration_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="passing_percentage">Passing Percentage (%)</label>
                                <input type="number" class="form-control @error('passing_percentage') is-invalid @enderror" 
                                    id="passing_percentage" name="passing_percentage" 
                                    value="{{ old('passing_percentage', $mcqTest->passing_percentage) }}" min="1" max="100" required>
                                @error('passing_percentage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_time">Start Time</label>
                                <input type="datetime-local" class="form-control @error('start_time') is-invalid @enderror" 
                                    id="start_time" name="start_time" 
                                    value="{{ old('start_time', $mcqTest->start_time->format('Y-m-d\TH:i')) }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    {{ $mcqTest->start_time->isPast() ? 'This test has already started.' : 'Test will start at this time.' }}
                                </small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_time">End Time</label>
                                <input type="datetime-local" class="form-control @error('end_time') is-invalid @enderror" 
                                    id="end_time" name="end_time" 
                                    value="{{ old('end_time', $mcqTest->end_time->format('Y-m-d\TH:i')) }}" required>
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    {{ $mcqTest->end_time->isPast() ? 'This test has ended.' : 'Test will end at this time.' }}
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Extra information about test availability -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="alert alert-info small">
                                <i class="bi bi-info-circle"></i> <strong>Test Availability Status:</strong>
                                @if($mcqTest->isAvailable())
                                    <span class="text-success">This test is currently available to students.</span>
                                @else
                                    <span class="text-warning">This test is not currently available to students.</span>
                                    <ul class="mt-2 mb-0">
                                        @if(!$mcqTest->is_active)
                                            <li>The test is marked as inactive.</li>
                                        @endif
                                        @if(now()->lt($mcqTest->start_time))
                                            <li>The test has not started yet (starts {{ $mcqTest->start_time->diffForHumans() }}).</li>
                                        @endif
                                        @if(now()->gt($mcqTest->end_time))
                                            <li>The test has already ended (ended {{ $mcqTest->end_time->diffForHumans() }}).</li>
                                        @endif
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                        {{ old('is_active', $mcqTest->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active (students can see and take the test)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-block">Update Test</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get the input elements
            const startTimeInput = document.getElementById('start_time');
            const endTimeInput = document.getElementById('end_time');
            
            // Get the current test times for reference
            let currentStartTime = "{{ $mcqTest->start_time->format('Y-m-d\TH:i') }}";
            let currentEndTime = "{{ $mcqTest->end_time->format('Y-m-d\TH:i') }}";
            const now = new Date();
            
            // Format current time for comparison
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const currentDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
            
            // Only enforce minimum time for future tests that haven't started yet
            if (new Date(currentStartTime) > now) {
                startTimeInput.min = currentDateTime;
            }
            
            // Update end time minimum value when start time changes
            startTimeInput.addEventListener('change', function() {
                if (startTimeInput.value) {
                    endTimeInput.min = startTimeInput.value;
                    
                    // If end time is before start time, update it
                    if (endTimeInput.value <= startTimeInput.value) {
                        // Set end time to start time + 1 hour
                        const startDate = new Date(startTimeInput.value);
                        startDate.setHours(startDate.getHours() + 1);
                        
                        const endYear = startDate.getFullYear();
                        const endMonth = String(startDate.getMonth() + 1).padStart(2, '0');
                        const endDay = String(startDate.getDate()).padStart(2, '0');
                        const endHours = String(startDate.getHours()).padStart(2, '0');
                        const endMinutes = String(startDate.getMinutes()).padStart(2, '0');
                        
                        endTimeInput.value = `${endYear}-${endMonth}-${endDay}T${endHours}:${endMinutes}`;
                    }
                }
            });
        });
    </script>
    @endpush
</x-teacher-layout> 