@extends('layouts.teacher')

@section('title', 'Test Attempt Details')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-clipboard-check"></i> Test Attempt Details
        </h1>
            <div>
            <a href="{{ route('teacher.tests.results', $testAttempt->mcq_test_id) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Results
            </a>
            <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
            </div>
        </div>

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
                                    <td><strong>{{ $testAttempt->mcqTest->title }}</strong></td>
                                </tr>
                                <tr>
                                    <th class="pl-0">Student:</th>
                                    <td>{{ $testAttempt->user->name }}</td>
                                </tr>
                                <tr>
                                    <th class="pl-0">Attempt:</th>
                                    <td>#{{ $attemptNumber }}</td>
                                </tr>
                                <tr>
                                    <th class="pl-0">Date:</th>
                                    <td>{{ $testAttempt->created_at->format('M d, Y g:i A') }}</td>
                                </tr>
                                <tr>
                                    <th class="pl-0">Score:</th>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="mr-2">
                                                <strong>{{ $correctAnswers }}/{{ $totalQuestions }}</strong>
                                                ({{ $correctPercentage }}%)
                                            </div>
                                            <div class="progress" style="height: 10px; width: 120px;">
                                                <div class="progress-bar {{ $testAttempt->passed ? 'bg-success' : 'bg-danger' }}" 
                                                     role="progressbar" 
                                                     style="width: {{ $correctPercentage }}%">
                            </div>
                        </div>
                                            <div class="ml-2">
                                                <span class="badge {{ $testAttempt->passed ? 'badge-success' : 'badge-danger' }}">
                                                    {{ $testAttempt->passed ? 'PASSED' : 'FAILED' }}
                                                </span>
                            </div>
                            </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-0">Time Taken:</th>
                                    <td>{{ $timeTaken }} minutes</td>
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
                    </div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Question Responses</h6>
                                        </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="questionsTable">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="50%">Question</th>
                                    <th width="15%">Student Answer</th>
                                    <th width="15%">Correct Answer</th>
                                    <th width="15%">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($questions as $index => $question)
                                <tr class="{{ $question['is_correct'] ? 'table-success' : ($question['is_answered'] ? 'table-danger' : 'table-warning') }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="mb-2">{{ $question['question_text'] }}</div>
                                        <button class="btn btn-sm btn-outline-info" 
                                                type="button" 
                                                data-toggle="collapse" 
                                                data-target="#explanation-{{ $index }}" 
                                                aria-expanded="false">
                                            <i class="fas fa-info-circle"></i> Show Explanation
                                        </button>
                                        <div class="collapse mt-2" id="explanation-{{ $index }}">
                                            <div class="card card-body bg-light">
                                                <strong>Explanation:</strong> 
                                                <p>{{ $question['explanation'] ?: 'No explanation provided.' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($question['is_answered'])
                                            @foreach($question['options'] as $key => $option)
                                                @if($key == $question['selected_option'])
                                                    <div class="{{ $question['is_correct'] ? 'text-success font-weight-bold' : 'text-danger font-weight-bold' }}">
                                                        {{ $option }}
                                                        </div>
                                                @endif
                                            @endforeach
                                        @else
                                            <span class="text-muted">No answer</span>
                                        @endif
                                    </td>
                                    <td>
                                        @foreach($question['options'] as $key => $option)
                                            @if($key == $question['correct_option'])
                                                <div class="text-success font-weight-bold">{{ $option }}</div>
                                            @endif
                                        @endforeach
                                    </td>
                                    <td>
                                        @if($question['is_correct'])
                                            <span class="badge badge-success"><i class="fas fa-check"></i> Correct</span>
                                        @elseif($question['is_answered'])
                                            <span class="badge badge-danger"><i class="fas fa-times"></i> Incorrect</span>
                                        @else
                                            <span class="badge badge-warning"><i class="fas fa-minus"></i> Unanswered</span>
                                        @endif
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
                data: [{{ $correctPercentage }}, {{ $incorrectPercentage }}, {{ $unansweredPercentage }}],
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
        });
    </script>
@endsection 