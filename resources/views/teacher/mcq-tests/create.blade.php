<x-teacher-layout>
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Create New Test</h1>
            <a href="{{ route('teacher.dashboard') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Test Details</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('mcq-tests.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="title" class="form-label">Test Title</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                    id="title" name="title" value="{{ old('title') }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="subject_id" class="form-label">Subject</label>
                                <select class="form-control @error('subject_id') is-invalid @enderror" 
                                    id="subject_id" name="subject_id" required>
                                    <option value="">Select Subject</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" 
                                            {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('subject_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="duration_minutes" class="form-label">Duration (minutes)</label>
                                <input type="number" class="form-control @error('duration_minutes') is-invalid @enderror" 
                                    id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes') }}" 
                                    min="1" required>
                                @error('duration_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="passing_percentage" class="form-label">Passing Percentage</label>
                                <input type="number" class="form-control @error('passing_percentage') is-invalid @enderror" 
                                    id="passing_percentage" name="passing_percentage" 
                                    value="{{ old('passing_percentage') }}" min="1" max="100" required>
                                @error('passing_percentage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="start_time" class="form-label">Start Time</label>
                                <input type="datetime-local" class="form-control @error('start_time') is-invalid @enderror" 
                                    id="start_time" name="start_time" min="{{ now()->format('Y-m-d\TH:i') }}"
                                    value="{{ old('start_time', now()->format('Y-m-d\TH:i')) }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Start time must be current or future time</small>
                            </div>

                            <div class="mb-3">
                                <label for="end_time" class="form-label">End Time</label>
                                <input type="datetime-local" class="form-control @error('end_time') is-invalid @enderror" 
                                    id="end_time" name="end_time" min="{{ now()->addHour()->format('Y-m-d\TH:i') }}"
                                    value="{{ old('end_time', now()->addDay()->format('Y-m-d\TH:i')) }}" required>
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">End time must be after start time</small>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                    id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-block">Create Test</button>
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
            
            // Set minimum value for start time (current time)
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const currentDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
            
            startTimeInput.min = currentDateTime;
            
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