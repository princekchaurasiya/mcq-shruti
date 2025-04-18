<x-student-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">{{ $mcq_test->title }}</h1>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <!-- Test Info Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Test Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Subject:</strong> {{ $mcq_test->subject->name }}</p>
                        <p><strong>Duration:</strong> {{ $mcq_test->duration_minutes }} minutes</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Total Questions:</strong> {{ $mcq_test->questions->count() }}</p>
                        <p><strong>Passing Score:</strong> {{ $mcq_test->passing_percentage }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add SweetAlert2 for notifications -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <form id="test-form" action="{{ route('test.submit', $mcq_test) }}" method="POST">
            @csrf
            <input type="hidden" name="attempt_id" value="{{ $attempt->id }}">
            
            <!-- Question Navigation with Timer - Sticky -->
            <div class="card shadow mb-4 sticky-top" style="top: 10px; z-index: 1000;">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Question Navigation</h6>
                    <!-- Timer in header -->
                    <div id="timer" class="bg-info text-white rounded py-1 px-3 text-nowrap">
                        <i class="bi bi-clock me-1"></i>
                        <strong>Time: <span id="time-remaining-minutes">{{ str_pad($mcq_test->duration_minutes, 2, '0', STR_PAD_LEFT) }}</span> min <span id="time-remaining-seconds">00</span> sec</strong>
                    </div>
                </div>
                <div class="card-body py-2">
                    <!-- Question buttons -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="btn-toolbar flex-grow-1" role="toolbar">
                            <div class="btn-group flex-wrap" role="group" id="question-nav-container">
                            @foreach($mcq_test->questions as $index => $question)
                                <button type="button" class="btn btn-outline-primary question-nav-btn" 
                                        data-question="{{ $index + 1 }}" id="nav-btn-{{ $index + 1 }}"
                                        data-page="{{ ceil(($index + 1) / 5) }}">
                                    {{ $index + 1 }}
                                </button>
                            @endforeach
                            </div>
                        </div>
                    </div>
                    
                    <!-- Mobile pagination controls -->
                    <div class="d-flex justify-content-between mb-2 d-md-none">
                        <button type="button" class="btn btn-sm btn-secondary" id="prev-page">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <span id="pagination-info">Questions 1-5</span>
                        <button type="button" class="btn btn-sm btn-secondary" id="next-page">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                    
                    <div class="d-flex flex-wrap mb-2">
                        <span class="badge bg-success me-2 mb-1">✓ Answered</span>
                        <span class="badge bg-warning me-2 mb-1">? Marked for Review</span>
                        <span class="badge bg-light me-2 mb-1">Not Visited</span>
                    </div>
                </div>
            </div>

            <!-- Questions -->
            @foreach($mcq_test->questions as $index => $question)
                <div class="card shadow mb-4 question-card" id="question-{{ $index + 1 }}" style="{{ $index > 0 ? 'display: none;' : '' }}">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Question {{ $index + 1 }} of {{ $mcq_test->questions->count() }}</h6>
                        <div>
                            <button type="button" class="btn btn-warning btn-sm mark-review" 
                                data-question="{{ $index + 1 }}" onclick="toggleReviewQuestion({{ $index + 1 }})">
                                <i class="bi bi-flag"></i> Mark for Review
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title mb-4">{{ $question->question_text }}</h5>
                        
                        @php
                            $options = is_string($question->options) 
                                ? json_decode($question->options, true) 
                                : $question->options;
                            $isAssoc = array_keys($options) !== range(0, count($options) - 1);
                        @endphp

                        <div class="options-list mb-4">
                            @foreach($options as $key => $option)
                                <div class="form-check mb-3">
                                    <input class="form-check-input option-input" type="radio" 
                                        name="answers[{{ $question->id }}][selected_option]" 
                                        id="option-{{ $question->id }}-{{ $key }}" 
                                        value="{{ $isAssoc ? $key : $option }}"
                                        data-question="{{ $index + 1 }}">
                                    <label class="form-check-label" for="option-{{ $question->id }}-{{ $key }}">
                                        {{ strtoupper($isAssoc ? $key : chr(97 + $loop->index)) }}) {{ $option }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="question-actions mt-4">
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-warning" id="mark-review-btn-{{ $question->id }}" onclick="toggleReviewQuestion({{ $index + 1 }})">
                                    <i class="bi bi-flag"></i> Mark for Review
                                </button>
                                
                                <button type="button" class="btn btn-primary next-question-btn" data-target="{{ $loop->index + 1 }}">
                                    {{ $loop->last ? 'Review Answers' : 'Next Question' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Submit Confirmation Modal -->
            <div class="modal fade" id="submitTestModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Submit Test</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to submit your test?</p>
                            <div class="alert alert-warning">
                                <div id="summary-answered">Answered: 0/{{ $mcq_test->questions->count() }}</div>
                                <div id="summary-review">Marked for Review: 0</div>
                                <div id="summary-unanswered">Unanswered: {{ $mcq_test->questions->count() }}</div>
                            </div>
                            
                            <!-- Review marked questions -->
                            <div id="review-marked-container" class="mt-3" style="display: none;">
                                <h6>Questions marked for review:</h6>
                                <div id="review-marked-list" class="d-flex flex-wrap gap-1 mt-2"></div>
                            </div>
                            
                            <!-- Unattempted questions -->
                            <div id="unattempted-container" class="mt-3">
                                <h6>Unattempted questions:</h6>
                                <div id="unattempted-list" class="d-flex flex-wrap gap-1 mt-2"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Continue Test</button>
                            <button type="submit" class="btn btn-primary submit-test-btn">Submit Test</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        // Initialize global variables to track question status
        var answeredQuestions = {};
        var reviewedQuestions = {};
        var visitedQuestions = { 1: true }; // First question is already visited
        var currentPage = 1;
        var questionsPerPage = 5;
        var totalPages = Math.ceil({{ $mcq_test->questions->count() }} / questionsPerPage);
        var currentQuestion = 1; // Track the current active question
        
        $(document).ready(function() {
            // Prevent accidental form submissions
            $('#test-form').on('submit', function(event) {
                // Only allow submission through the submit button in the modal
                const submitter = event.originalEvent ? event.originalEvent.submitter : null;
                if (!submitter || !submitter.classList.contains('submit-test-btn')) {
                    event.preventDefault();
                    console.log('Form submission prevented - use the Submit Test button in the modal.');
                    return false;
                }
            });
            
            // Timer functionality
            let testDuration = {{ $mcq_test->duration_minutes }}; // in minutes
            let totalSeconds = testDuration * 60;
            let warningThreshold = 5 * 60; // 5 minutes in seconds
            let timerInterval;
            
            // Function to update the timer display
            function updateTimer() {
                totalSeconds--;
                
                if (totalSeconds <= 0) {
                    // Time's up - auto-submit the test
                    clearInterval(timerInterval);
                    
                    // Check if SweetAlert2 is available
                    if (typeof Swal !== 'undefined') {
                        showTimeUpMessage();
                    } else {
                        alert('Time\'s up! Your test will be submitted automatically.');
                    }
                    
                    setTimeout(function() {
                    document.getElementById('test-form').submit();
                    }, 1000);
                    return;
                }
                
                // Calculate minutes and seconds
                let minutes = Math.floor(totalSeconds / 60);
                let seconds = totalSeconds % 60;
                
                // Update the timer display with minutes and seconds
                $('#time-remaining-minutes').text(minutes < 10 ? "0" + minutes : minutes);
                $('#time-remaining-seconds').text(seconds < 10 ? "0" + seconds : seconds);
                
                // Change color to danger if less than 5 minutes remaining
                if (totalSeconds <= warningThreshold) {
                    $('#timer').removeClass('bg-info').addClass('bg-danger');
                    
                    // Flash the timer for emphasis
                    if (seconds % 2 === 0) {
                        $('#timer').css('opacity', '0.7');
                    } else {
                        $('#timer').css('opacity', '1');
                    }
                    
                    // Show warning message when entering warning period
                    if (totalSeconds === warningThreshold && typeof Swal !== 'undefined') {
                        showWarningMessage();
                    }
                }
            }
            
            // Function to show warning message
            function showWarningMessage() {
                Swal.fire({
                    title: 'Warning: 5 Minutes Left!',
                    text: 'You have only 5 minutes remaining. Please finish your test and submit now!',
                    icon: 'warning',
                    confirmButtonText: 'Continue Test'
                });
            }
            
            // Function to show time up message
            function showTimeUpMessage() {
                Swal.fire({
                    title: 'Time\'s Up!',
                    text: 'Your time has expired. The test will be submitted automatically.',
                    icon: 'info',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false
                });
            }
            
            // Start the timer
            updateTimer();
            timerInterval = setInterval(updateTimer, 1000);
            
            // Check for inactivity and show a reminder
            let inactivityThreshold = 60; // 1 minute
            let inactivityCounter = 0;
            let inactivityInterval;
            
            function resetInactivityTimer() {
                inactivityCounter = 0;
            }
            
            function checkInactivity() {
                inactivityCounter++;
                if (inactivityCounter >= inactivityThreshold) {
                    // Show reminder after 1 minute of inactivity
                    Swal.fire({
                        title: 'Still there?',
                        text: 'The timer is still running. Please continue your test.',
                        icon: 'info',
                        confirmButtonText: 'Continue Test'
                    });
                    resetInactivityTimer();
                }
            }
            
            // Reset inactivity timer on user action
            $(document).on('mousemove keypress click', resetInactivityTimer);
            inactivityInterval = setInterval(checkInactivity, 1000);
            
            // Initialize pagination for mobile
            updatePaginationDisplay();
            
            // Initialize question navigation
            updateQuestionStatus();
            
            // Add event listeners
            document.querySelectorAll('.question-nav-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    navigateToQuestion(parseInt(this.dataset.question));
                });
            });
            
            document.querySelectorAll('.next-question-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const target = parseInt(this.dataset.target);
                    const totalQuestions = {{ $mcq_test->questions->count() }};
                    
                    if (this.textContent.trim() === 'Review Answers') {
                        // Show the review modal
                        const submitModal = new bootstrap.Modal(document.getElementById('submitTestModal'));
                        updateReviewAndUnattemptedLists();
                        submitModal.show();
                    } else {
                        // Navigate to next question
                        navigateToQuestion(target + 1);
                    }
                });
            });
            
            document.querySelectorAll('.mark-review').forEach(btn => {
                btn.addEventListener('click', function() {
                    const questionNumber = parseInt(this.dataset.question);
                    toggleReviewQuestion(questionNumber);
                });
            });
            
            document.querySelectorAll('.option-input').forEach(input => {
                input.addEventListener('change', function(event) {
                    // Prevent any default form submission
                    event.preventDefault();
                    
                    const questionNumber = parseInt(this.dataset.question);
                    markQuestionAnswered(questionNumber);
                });
            });
            
            // Pagination controls for mobile
            document.getElementById('prev-page').addEventListener('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    updatePaginationDisplay();
                }
            });
            
            document.getElementById('next-page').addEventListener('click', function() {
                if (currentPage < totalPages) {
                    currentPage++;
                    updatePaginationDisplay();
                }
            });
            
            // Show modal event handler - update the lists of marked and unattempted questions
            const submitModal = document.getElementById('submitTestModal');
            submitModal.addEventListener('show.bs.modal', function() {
                updateReviewAndUnattemptedLists();
            });
            
            // Prevent Enter key from submitting the form
            $(document).on('keydown', function(event) {
                if (event.key === 'Enter' && !$(event.target).is('textarea')) {
                    event.preventDefault();
                    return false;
                }
            });
            
            // Prevent radio buttons from triggering form submission
            $('.option-input').on('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    return false;
                }
            });
        });
        
        // Function to update pagination display for mobile
        function updatePaginationDisplay() {
            const start = (currentPage - 1) * questionsPerPage + 1;
            const end = Math.min(currentPage * questionsPerPage, {{ $mcq_test->questions->count() }});
            document.getElementById('pagination-info').textContent = `Questions ${start}-${end}`;
            
            // Show/hide question buttons based on current page
            document.querySelectorAll('.question-nav-btn').forEach(btn => {
                const btnPage = parseInt(btn.dataset.page);
                if (btnPage === currentPage) {
                    btn.style.display = '';
                } else {
                    btn.style.display = window.innerWidth < 768 ? 'none' : '';
                }
            });
        }
        
        // Update pagination on window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                // Show all buttons on desktop
                document.querySelectorAll('.question-nav-btn').forEach(btn => {
                    btn.style.display = '';
                });
            } else {
                // On mobile, only show current page
                updatePaginationDisplay();
            }
        });
        
        // Function to navigate between questions
        function navigateToQuestion(questionNumber) {
            // Hide all question cards
            document.querySelectorAll('.question-card').forEach(card => {
                card.style.display = 'none';
            });
            
            // Show the selected question
            document.getElementById(`question-${questionNumber}`).style.display = 'block';
            
            // Update current active question
            currentQuestion = questionNumber;
            
            // Mark as visited (but we won't use this for styling anymore)
            visitedQuestions[questionNumber] = true;
            updateQuestionStatus();
            
            // Clear active outline from all buttons and add it only to current question
            document.querySelectorAll('.question-nav-btn').forEach(btn => {
                btn.classList.remove('active-outline');
            });
            document.getElementById(`nav-btn-${questionNumber}`).classList.add('active-outline');
            
            // Update pagination page if on mobile
            if (window.innerWidth < 768) {
                const page = Math.ceil(questionNumber / questionsPerPage);
                if (page !== currentPage) {
                    currentPage = page;
                    updatePaginationDisplay();
                }
            }
            
            // Scroll to top of question - add a delay to ensure rendering completes
            setTimeout(() => {
                window.scrollTo({
                    top: document.getElementById(`question-${questionNumber}`).offsetTop - 150,
                    behavior: 'smooth'
                });
            }, 50);
        }
        
        // Function to mark question as answered
        function markQuestionAnswered(questionNumber) {
            answeredQuestions[questionNumber] = true;
            
            // Automatically remove the "marked for review" status when answering
            if (reviewedQuestions[questionNumber]) {
                delete reviewedQuestions[questionNumber];
                
                // Update the mark for review button text
                const reviewBtn = document.querySelector(`.mark-review[data-question="${questionNumber}"]`);
                if (reviewBtn) {
                    reviewBtn.innerHTML = '<i class="bi bi-flag"></i> Mark for Review';
                    reviewBtn.classList.remove('btn-secondary');
                    reviewBtn.classList.add('btn-warning');
                }
            }
            
            updateQuestionStatus();
            
            // Auto-navigate to next question after selecting an answer
            const totalQuestions = {{ $mcq_test->questions->count() }};
            if (questionNumber < totalQuestions) {
                setTimeout(() => {
                    navigateToQuestion(questionNumber + 1);
                }, 300);
            } else if (questionNumber === totalQuestions) {
                // If it's the last question, don't auto-submit, just stay on the question
                // This prevents accidental submission
            }
        }
        
        // Function to toggle review status
        function toggleReviewQuestion(questionNumber) {
            if (reviewedQuestions[questionNumber]) {
                delete reviewedQuestions[questionNumber];
            } else {
                reviewedQuestions[questionNumber] = true;
            }
            updateQuestionStatus();
            
            // Update all related button text based on review status
            const allReviewButtons = document.querySelectorAll(`.mark-review[data-question="${questionNumber}"]`);
            const reviewStatus = reviewedQuestions[questionNumber];
            
            allReviewButtons.forEach(btn => {
                if (reviewStatus) {
                    btn.innerHTML = '<i class="bi bi-flag-fill"></i> Marked for Review';
                    btn.classList.remove('btn-warning');
                    btn.classList.add('btn-secondary');
                } else {
                    btn.innerHTML = '<i class="bi bi-flag"></i> Mark for Review';
                    btn.classList.remove('btn-secondary');
                    btn.classList.add('btn-warning');
                }
            });
            
            // Also update the bottom button
            const reviewBtn = document.getElementById(`mark-review-btn-${questionNumber}`);
            if (reviewBtn) {
                if (reviewStatus) {
                    reviewBtn.textContent = 'Marked for Review';
                    reviewBtn.classList.remove('btn-warning');
                    reviewBtn.classList.add('btn-secondary');
                } else {
                    reviewBtn.textContent = 'Mark for Review';
                    reviewBtn.classList.remove('btn-secondary');
                    reviewBtn.classList.add('btn-warning');
                }
            }
            
            // Auto-navigate to next question after marking for review
            const totalQuestions = {{ $mcq_test->questions->count() }};
            if (questionNumber < totalQuestions) {
                setTimeout(() => {
                    navigateToQuestion(questionNumber + 1);
                }, 300);
            }
        }
        
        function markForReview(questionId) {
            // Find the question number from the question id
            const questionCards = document.querySelectorAll('.question-card');
            let questionNumber = 0;
            
            for (let i = 0; i < questionCards.length; i++) {
                if (questionCards[i].id === `question-${questionId}`) {
                    questionNumber = i + 1;
                    break;
                }
            }
            
            if (questionNumber === 0) {
                // If we couldn't find it, try to use the id directly
                questionNumber = questionId;
            }
            
            // Call the toggleReviewQuestion function which handles all the UI updates
            toggleReviewQuestion(questionNumber);
            
            // Also update server status if needed
            updateQuestionReviewStatus(questionId, reviewedQuestions[questionNumber] || false);
        }
        
        // Update review and unattempted questions in the modal
        function updateReviewAndUnattemptedLists() {
            const totalQuestions = {{ $mcq_test->questions->count() }};
            const reviewContainer = document.getElementById('review-marked-container');
            const reviewList = document.getElementById('review-marked-list');
            const unattemptedList = document.getElementById('unattempted-list');
            
            // Clear previous content
            reviewList.innerHTML = '';
            unattemptedList.innerHTML = '';
            
            // Add marked for review questions (only include those not answered)
            const reviewedKeys = Object.keys(reviewedQuestions).filter(num => !answeredQuestions[num]);
            if (reviewedKeys.length > 0) {
                reviewContainer.style.display = 'block';
                reviewedKeys.forEach(num => {
                    const btn = document.createElement('button');
                    btn.className = 'btn btn-sm btn-warning me-1 mb-1';
                    btn.textContent = num;
                    btn.onclick = function() {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('submitTestModal'));
                        modal.hide();
                        navigateToQuestion(parseInt(num));
                    };
                    reviewList.appendChild(btn);
                });
            } else {
                reviewContainer.style.display = 'none';
            }
            
            // Add unattempted questions
            let unattemptedCount = 0;
            for (let i = 1; i <= totalQuestions; i++) {
                if (!answeredQuestions[i]) {
                    const btn = document.createElement('button');
                    btn.className = 'btn btn-sm btn-outline-secondary me-1 mb-1';
                    btn.textContent = i;
                    btn.onclick = function() {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('submitTestModal'));
                        modal.hide();
                        navigateToQuestion(parseInt(i));
                    };
                    unattemptedList.appendChild(btn);
                    unattemptedCount++;
                }
            }
            
            // Hide unattempted section if all questions are attempted
            if (unattemptedCount === 0) {
                document.getElementById('unattempted-container').style.display = 'none';
            } else {
                document.getElementById('unattempted-container').style.display = 'block';
            }
        }
        
        // Update the status of all question navigation buttons
        function updateQuestionStatus() {
            const totalQuestions = {{ $mcq_test->questions->count() }};
            
            // Update navigation buttons
            for (let i = 1; i <= totalQuestions; i++) {
                const btn = document.getElementById(`nav-btn-${i}`);
                
                // Remove all styling classes first
                btn.classList.remove('btn-outline-primary', 'btn-success', 'btn-warning', 'btn-light');
                
                // Apply the appropriate class based on question status
                if (answeredQuestions[i]) {
                    // Answered questions get green
                    btn.classList.add('btn-success');
                } else if (reviewedQuestions[i]) {
                    // Marked for review get yellow
                    btn.classList.add('btn-warning');
                } else if (i === currentQuestion) {
                    // Current question gets outline-primary
                    btn.classList.add('btn-outline-primary');
                } else {
                    // All other questions (whether visited or not) get light background
                    btn.classList.add('btn-light');
                }
                
                // Add active outline to current question button
                if (i === currentQuestion) {
                    btn.classList.add('active-outline');
                } else {
                    btn.classList.remove('active-outline');
                }
            }
            
            // Update summary counts
            const answeredCount = Object.keys(answeredQuestions).length;
            // Only count reviewed questions that are NOT answered
            const reviewCount = Object.keys(reviewedQuestions)
                .filter(num => !answeredQuestions[num])
                .length;
            
            document.getElementById('summary-answered').textContent = `Answered: ${answeredCount}/${totalQuestions}`;
            document.getElementById('summary-review').textContent = `Marked for Review: ${reviewCount}`;
            document.getElementById('summary-unanswered').textContent = `Unanswered: ${totalQuestions - answeredCount}`;
        }
        
        // Function to update the review status in the database
        function updateQuestionReviewStatus(questionId, isReviewed) {
            const attemptId = {{ $attempt->id }};
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch('/student/test/update-review-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    attempt_id: attemptId,
                    question_id: questionId,
                    is_marked_for_review: isReviewed
                })
            })
            .then(response => {
                if (!response.ok) {
                    console.error('Failed to update review status');
                }
            })
            .catch(error => {
                console.error('Error updating review status:', error);
            });
        }
    </script>

    <style>
        .blink {
            animation: blinker 1s linear infinite;
        }
        
        @keyframes blinker {
            50% {
                opacity: 0.5;
            }
        }
        
        .question-nav-btn {
            margin: 0.15rem;
            min-width: 2.5rem;
        }
        
        .active-outline {
            box-shadow: 0 0 0 2px #4e73df !important;
            font-weight: bold;
        }
        
        .btn-answered {
            background-color: #1cc88a !important;
            color: white !important;
        }
        
        .btn-reviewed {
            background-color: #f6c23e !important;
            color: white !important;
        }
        
        .sticky-top {
            position: sticky;
            top: 10px;
        }
        
        #timer {
            font-size: 1rem;
            transition: opacity 0.3s ease, background-color 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        #time-remaining-minutes, 
        #time-remaining-seconds {
            font-weight: bold;
            font-family: monospace;
        }
        
        .bg-danger #time-remaining-minutes,
        .bg-danger #time-remaining-seconds {
            font-size: 1.1rem;
        }
        
        @media (max-width: 576px) {
            #timer {
                font-size: 0.8rem;
                padding: 0.25rem 0.5rem !important;
            }
        }
        
        .option-input:checked + label {
            font-weight: bold;
        }
        
        /* Add some space after the navigation to prevent hiding content */
        .question-card {
            margin-top: 15px;
        }
        
        /* Responsive styles */
        @media (max-width: 767.98px) {
            #timer {
                font-size: 0.9rem;
            }
            
            .question-nav-btn {
                width: 32px;
                height: 32px;
                font-size: 0.8rem;
            }
            
            /* Make the navigation take less vertical space on mobile */
            .sticky-top {
                top: 60px;
            }
            
            .card-body.py-2 {
                padding-top: 0.5rem !important;
                padding-bottom: 0.5rem !important;
            }
        }
        
        /* Fix gap utility for older browsers */
        .gap-1 {
            gap: 0.25rem !important;
        }
    </style>
    @endpush
</x-student-layout> 