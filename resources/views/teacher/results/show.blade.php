@extends('layouts.teacher')

@section('title', 'Student Test Attempt Details')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-clipboard-check"></i> Student Test Attempt Details
        </h1>
            <div>
            <a href="{{ route('mcq-tests.results', $testAttempt->mcq_test_id ?? 0) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Results
            </a>
            <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
            </div>
        </div>

        @if(!isset($testAttempt) || !$testAttempt)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Test attempt data is not available. Please go back and try again.
            </div>
        @else
            <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Test Information</h6>
                        </div>
                        <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th class="pl-0" width="30%">Test:</th>
                                        <td><strong>{{ $testAttempt->mcqTest->title ?? 'No Title Available' }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th class="pl-0">Student:</th>
                                        <td>{{ $testAttempt->user->name ?? 'Unknown Student' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="pl-0">Attempt:</th>
                                        <td>#{{ $attemptNumber ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="pl-0">Date:</th>
                                        <td>{{ isset($testAttempt->created_at) ? $testAttempt->created_at->format('M d, Y g:i A') : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="pl-0">Score:</th>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="mr-2">
                                                    <strong>{{ $correctAnswers ?? 0 }}/{{ $totalQuestions ?? 0 }}</strong>
                                                    ({{ $correctPercentage ?? 0 }}%)
                                                </div>
                                                <div class="progress" style="height: 10px; width: 120px;">
                                                    <div class="progress-bar {{ isset($testAttempt) && ($testAttempt->passed ?? false) ? 'bg-success' : 'bg-danger' }}" 
                                                         role="progressbar" 
                                                         style="width: {{ $correctPercentage ?? 0 }}%">
                            </div>
                        </div>
                                            <div class="ml-2">
                                                <span class="badge {{ isset($testAttempt) && ($testAttempt->passed ?? false) ? 'badge-success' : 'badge-danger' }}">
                                                    {{ isset($testAttempt) && ($testAttempt->passed ?? false) ? 'PASSED' : 'FAILED' }}
                                                </span>
                            </div>
                            </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-0">Time Taken:</th>
                                    <td>{{ $timeTaken ?? 'N/A' }} minutes</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div class="chart-container" style="position: relative; height:200px;">
                                <canvas id="performanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                            </div>
                        </div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Question Responses</h6>
                                        </div>
                <div class="card-body">
                    @if(!isset($responses) || $responses->isEmpty())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No responses available for this test attempt.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered" id="questionsTable">
                                <thead>
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>Question</th>
                                        <th>Student Answer</th>
                                        <th>Correct Answer</th>
                                        <th>Status</th>
                                        <th width="80">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($responses as $index => $response)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $response->question->question_text ?? 'No question text available' }}</td>
                                        <td>
                                            @if(isset($response->selected_option) && !empty($response->selected_option))
                                                <span class="badge bg-info text-white">
                                                    {{ is_array($response->selected_option) ? implode(', ', $response->selected_option) : $response->selected_option }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">Not Answered</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($response->question) && isset($response->question->correct_option))
                                                <span class="badge bg-success text-white">
                                                    {{ is_array($response->question->correct_option) ? implode(', ', $response->question->correct_option) : $response->question->correct_option }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">No data</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($response->is_correct) && $response->is_correct)
                                                <span class="badge badge-success"><i class="fas fa-check"></i> Correct</span>
                                            @elseif(isset($response->selected_option) && !empty($response->selected_option))
                                                <span class="badge badge-danger"><i class="fas fa-times"></i> Incorrect</span>
                                            @else
                                                <span class="badge badge-warning"><i class="fas fa-minus"></i> Unanswered</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($response->question) && isset($response->question->id))
                                                <button class="btn btn-sm btn-outline-info" 
                                                        type="button" 
                                                        data-toggle="collapse" 
                                                        data-target="#explanation-{{ $response->question->id }}" 
                                                        aria-expanded="false">
                                                    <i class="fas fa-info-circle"></i> Explanation
                                                </button>
                                                <div class="collapse mt-2" id="explanation-{{ $response->question->id }}">
                                                    <div class="card card-body bg-light">
                                                        <strong>Explanation:</strong> 
                                                        <p>{{ $response->question->explanation ?? 'No explanation provided.' }}</p>
                                                    </div>
                                                </div>
                                            @else
                                                <button class="btn btn-sm btn-outline-secondary" disabled>
                                                    <i class="fas fa-info-circle"></i> Not Available
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
    // Initialize performance chart
    const ctx = document.getElementById('performanceChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Correct', 'Incorrect', 'Unanswered'],
            datasets: [{
                data: [{{ $correctPercentage ?? 0 }}, {{ $incorrectPercentage ?? 0 }}, {{ $unansweredPercentage ?? 0 }}],
                backgroundColor: ['#1cc88a', '#e74a3b', '#f6c23e'],
                hoverBackgroundColor: ['#17a673', '#cc3c31', '#daa520'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw;
                            return `${label}: ${value}%`;
                        }
                    }
                }
            },
            cutout: '70%',
        }
    });
    
    // Initialize the questions table
    if (typeof $ !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
        $('#questionsTable').DataTable({
            pageLength: 10,
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search questions...",
            },
            columnDefs: [
                { orderable: false, targets: [1, 2, 3, 4] }
            ]
        });
    }
        });
    </script>
@endsection 