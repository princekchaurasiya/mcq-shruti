<x-teacher-layout>
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Test Results: {{ $mcqTest->title }}</h1>
            <div>
                <a href="{{ route('mcq-tests.show', $mcqTest) }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Test
                </a>
            </div>
        </div>

        <!-- Test Overview -->
        <div class="row">
            <!-- Test Details Card -->
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Test Subject</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $mcqTest->subject->name ?? 'N/A' }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-book fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Students Card -->
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Students</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ count($userData) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-people fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Average Score Card -->
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Average Best Score
                                </div>
                                <div class="row no-gutters align-items-center">
                                    <div class="col-auto">
                                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ number_format($averageBestScore, 1) }}%</div>
                                    </div>
                                    <div class="col">
                                        <div class="progress progress-sm mr-2">
                                            <div class="progress-bar bg-info" role="progressbar"
                                                style="width: {{ $averageBestScore }}%" aria-valuenow="{{ $averageBestScore }}" aria-valuemin="0"
                                                aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-percent fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row">
            <!-- Scores Chart -->
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Best Student Scores</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-bar">
                            <canvas id="scoresChart"></canvas>
                        </div>
                        <hr>
                        <div class="text-center small">
                            <span class="me-2">
                                <i class="fas fa-circle text-success"></i> Passing Score: {{ $mcqTest->passing_percentage }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attempts Distribution -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Attempts Distribution</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-pie pt-4 pb-2">
                            <canvas id="attemptsChart"></canvas>
                        </div>
                        <div class="mt-4 text-center small">
                            <span class="me-2">
                                <i class="fas fa-circle text-primary"></i> Average Attempts: 
                                {{ number_format(collect($userData)->pluck('attempts_count')->average(), 1) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Performance Overview -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Student Performance Overview</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="studentTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Best Score</th>
                                        <th>Status</th>
                                        <th>Attempts</th>
                                        <th>Last Attempt</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($userData as $user)
                                        <tr>
                                            <td>{{ $user['student_name'] }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress progress-sm me-2" style="width: 80px; height: 8px;">
                                                        <div class="progress-bar {{ $user['has_passed'] ? 'bg-success' : 'bg-danger' }}" 
                                                            role="progressbar" style="width: {{ $user['best_score'] }}%"></div>
                                                    </div>
                                                    <span>{{ $user['best_score'] }}%</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge {{ $user['has_passed'] ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $user['has_passed'] ? 'Passed' : 'Failed' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $user['attempts_count'] }}/5</span>
                                            </td>
                                            <td>{{ $user['attempts'][0]['attempt_date'] }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-info view-attempts" data-user-id="{{ $user['user_id'] }}">
                                                    <i class="bi bi-list"></i> View Attempts
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attempt Details (Initially Hidden) -->
        <div class="row" id="attemptDetailsContainer" style="display: none;">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            Attempt History: <span id="selectedStudentName"></span>
                        </h6>
                        <button class="btn btn-sm btn-secondary" id="hideAttemptDetails">
                            <i class="bi bi-x"></i> Close
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="attemptDetailsList">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="attemptsTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Score</th>
                                            <th>Correct</th>
                                            <th>Incorrect</th>
                                            <th>Unanswered</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="attemptsList">
                                        <!-- Attempts will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <button class="btn btn-secondary" id="hideAttemptDetails">
                                    <i class="fas fa-times"></i> Hide Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Performance Comparison -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Student Performance Comparison</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-bar">
                            <canvas id="performanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Options -->
        <div class="row mb-4">
            <div class="col-12 text-center">
                <button class="btn btn-primary" id="exportPdf">
                    <i class="bi bi-file-pdf"></i> Export as PDF
                </button>
                <button class="btn btn-success" id="exportExcel">
                    <i class="bi bi-file-excel"></i> Export as Excel
                </button>
                <button class="btn btn-info" id="printResults">
                    <i class="bi bi-printer"></i> Print Results
                </button>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .progress {
            background-color: #eaecf4;
        }
    </style>
    @endpush

    @push('scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTables
            const studentTable = $('#studentTable').DataTable({
                order: [[1, 'desc']], // Sort by best score by default
                language: {
                    search: "Search students:",
                    lengthMenu: "Show _MENU_ students per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ students"
                }
            });
            
            // Get data from backend
            const students = @json($studentsArray);
            const bestScores = @json($bestScoresArray);
            const passingScore = {{ $mcqTest->passing_percentage }};
            const userData = @json($userData);
            
            // Calculate attempt distribution
            const attemptCounts = [0, 0, 0, 0, 0]; // 1, 2, 3, 4, 5 attempts
            userData.forEach(user => {
                const count = Math.min(user.attempts_count, 5);
                attemptCounts[count - 1]++;
            });
            
            // Initialize the scores chart
            const scoresCtx = document.getElementById('scoresChart').getContext('2d');
            new Chart(scoresCtx, {
                type: 'bar',
                data: {
                    labels: students,
                    datasets: [{
                        label: 'Best Score',
                        data: bestScores,
                        backgroundColor: bestScores.map(score => score >= passingScore ? 'rgba(78, 115, 223, 0.8)' : 'rgba(231, 74, 59, 0.8)'),
                        borderColor: bestScores.map(score => score >= passingScore ? 'rgb(78, 115, 223)' : 'rgb(231, 74, 59)'),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            title: {
                                display: true,
                                text: 'Score (%)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Students'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Score: ${context.parsed.y}%`;
                                }
                            }
                        }
                    }
                }
            });
            
            // Initialize the attempts distribution chart
            const attemptsCtx = document.getElementById('attemptsChart').getContext('2d');
            new Chart(attemptsCtx, {
                type: 'doughnut',
                data: {
                    labels: ['1 Attempt', '2 Attempts', '3 Attempts', '4 Attempts', '5 Attempts'],
                    datasets: [{
                        data: attemptCounts,
                        backgroundColor: [
                            'rgba(78, 115, 223, 0.8)',
                            'rgba(28, 200, 138, 0.8)',
                            'rgba(246, 194, 62, 0.8)',
                            'rgba(54, 185, 204, 0.8)',
                            'rgba(231, 74, 59, 0.8)'
                        ],
                        borderColor: [
                            'rgb(78, 115, 223)',
                            'rgb(28, 200, 138)',
                            'rgb(246, 194, 62)',
                            'rgb(54, 185, 204)',
                            'rgb(231, 74, 59)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Print functionality
            document.getElementById('printResults').addEventListener('click', function() {
                window.print();
            });

            // PDF Export (mock)
            document.getElementById('exportPdf').addEventListener('click', function() {
                alert('PDF export functionality would be implemented here');
            });

            // Excel Export (mock)
            document.getElementById('exportExcel').addEventListener('click', function() {
                alert('Excel export functionality would be implemented here');
            });

            // Populate the attempts table when clicking "View Attempts"
            $('.view-attempts').click(function() {
                const userId = $(this).data('user-id');
                const user = userData.find(u => u.user_id == userId);
                
                if (user) {
                    $('#selectedStudentName').text(user.student_name);
                    
                    // Clear previous data
                    $('#attemptDetailsBody').empty();
                    
                    user.attempts.forEach((attempt, index) => {
                        const row = `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${new Date(attempt.created_at).toLocaleString()}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress" style="height: 10px; width: 100px;">
                                            <div class="progress-bar ${attempt.passed ? 'bg-success' : 'bg-danger'}" role="progressbar" style="width: ${attempt.score}%"></div>
                                        </div>
                                        <span class="ml-2">${attempt.score}%</span>
                                    </div>
                                </td>
                                <td>${attempt.correct_answers}</td>
                                <td>${attempt.incorrect_answers}</td>
                                <td>${attempt.unanswered}</td>
                                <td>${attempt.time_taken} min</td>
                                <td><span class="badge ${attempt.passed ? 'badge-success' : 'badge-danger'}">${attempt.passed ? 'Passed' : 'Failed'}</span></td>
                                <td>
                                    <a href="{{ route('teacher.tests.student.attempt.show', '') }}/${attempt.id}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        `;
                        $('#attemptDetailsBody').append(row);
                    });
                    
                    // Initialize DataTable for attempts
                    if ($.fn.DataTable.isDataTable('#attemptsTable')) {
                        $('#attemptsTable').DataTable().destroy();
                    }
                    
                    $('#attemptsTable').DataTable({
                        paging: false,
                        searching: false,
                        order: [[0, 'asc']]
                    });
                    
                    $('#attemptDetailsList').show();
                    
                    // Scroll to the attempt details
                    $('html, body').animate({
                        scrollTop: $("#attemptDetailsList").offset().top - 100
                    }, 500);
                }
            });
            
            // Hide attempt details
            $('#hideAttemptDetails').click(function() {
                $('#attemptDetailsList').hide();
            });
        });
    </script>
    @endpush
</x-teacher-layout> 