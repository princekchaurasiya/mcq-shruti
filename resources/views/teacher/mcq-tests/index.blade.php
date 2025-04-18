<x-teacher-layout>
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">MCQ Tests</h1>
            <a href="{{ route('mcq-tests.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Create New Test
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <!-- Tests List -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">All Tests</h6>
            </div>
            <div class="card-body">
                @if($tests->isEmpty())
                    <div class="text-center py-5">
                        <p class="text-muted">No tests created yet.</p>
                        <a href="{{ route('mcq-tests.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Create Your First Test
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Subject</th>
                                    <th>Duration</th>
                                    <th>Questions</th>
                                    <th>Attempts</th>
                                    <th>Average Score</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tests as $test)
                                    <tr class="@if($test->end_time < now()) test-ended @elseif($test->start_time > now()) test-scheduled @elseif($test->is_active) test-active @else test-inactive @endif">
                                        <td>{{ $test->title }}</td>
                                        <td>{{ $test->subject->name }}</td>
                                        <td>{{ $test->duration_minutes }} mins</td>
                                        <td>{{ $test->questions_count }} questions</td>
                                        <td>{{ $test->attempts_count }} attempts</td>
                                        <td>
                                            @if($test->attempts_count > 0)
                                                {{ number_format($test->attempts->avg('score'), 1) }}%
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            @if($test->end_time < now())
                                                <span class="badge bg-danger">Ended</span>
                                            @elseif($test->start_time > now())
                                                <span class="badge bg-info">Scheduled</span>
                                            @elseif($test->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('mcq-tests.show', $test) }}" 
                                                    class="btn btn-sm btn-info" title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('mcq-tests.edit', $test) }}" 
                                                    class="btn btn-sm btn-primary" title="Edit Test">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="{{ route('mcq-tests.results', $test) }}" 
                                                    class="btn btn-sm btn-warning" title="View Results">
                                                    <i class="bi bi-bar-chart"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="deleteTest({{ $test->id }})" title="Delete Test">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $tests->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Results Modal -->
        <div id="resultsModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                                    Test Results
                                </h3>
                                <div class="mt-4">
                                    <div id="resultsContent" class="max-h-96 overflow-y-auto">
                                        <!-- Results will be loaded here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" onclick="closeResultsModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function viewResults(testId) {
            const modal = document.getElementById('resultsModal');
            const content = document.getElementById('resultsContent');
            
            // Show loading state
            content.innerHTML = '<div class="text-center"><div class="spinner"></div></div>';
            modal.classList.remove('hidden');
            
            // Fetch results
            fetch(`/teacher/mcq-tests/${testId}/results`)
                .then(response => response.json())
                .then(data => {
                    let html = '<div class="space-y-4">';
                    if (data.attempts.length === 0) {
                        html += '<p class="text-gray-500 dark:text-gray-400">No attempts yet.</p>';
                    } else {
                        html += data.attempts.map(attempt => `
                            <div class="border dark:border-gray-700 rounded p-4">
                                <div class="flex justify-between items-center">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">${attempt.student_name}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">${attempt.attempt_date}</div>
                                </div>
                                <div class="mt-2 grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Score:</span>
                                        <span class="ml-2 font-medium ${attempt.score >= attempt.passing_score ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'}">${attempt.score}%</span>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Time Taken:</span>
                                        <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">${attempt.time_taken} mins</span>
                                    </div>
                                </div>
                            </div>
                        `).join('');
                    }
                    html += '</div>';
                    content.innerHTML = html;
                })
                .catch(error => {
                    content.innerHTML = '<p class="text-red-600 dark:text-red-400">Error loading results. Please try again.</p>';
                    console.error('Error:', error);
                });
        }

        function closeResultsModal() {
            document.getElementById('resultsModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('resultsModal');
            if (event.target == modal) {
                closeResultsModal();
            }
        }

        function deleteTest(testId) {
            if (confirm('Are you sure you want to delete this test? This action cannot be undone.')) {
                fetch(`/teacher/mcq-tests/${testId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                }).then(() => window.location.reload());
            }
        }
    </script>

    <style>
    .spinner {
        border: 3px solid rgba(0, 0, 0, 0.1);
        border-radius: 50%;
        border-top: 3px solid #3498db;
        width: 24px;
        height: 24px;
        animation: spin 1s linear infinite;
        margin: 20px auto;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @media (prefers-color-scheme: dark) {
        .spinner {
            border-color: rgba(255, 255, 255, 0.1);
            border-top-color: #3498db;
        }
    }

    /* Test status styling */
    tr.test-ended td {
        opacity: 0.7;
        background-color: #f8f9fc !important;
    }
    
    tr.test-inactive td {
        opacity: 0.75;
        font-style: italic;
    }
    
    tr.test-scheduled td {
        background-color: #eaf6ff !important;
    }
    
    tr.test-active td {
        background-color: #f0fff4 !important;
    }
    </style>
    @endpush
</x-teacher-layout> 