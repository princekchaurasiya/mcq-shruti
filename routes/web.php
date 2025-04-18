<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\MCQTestController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TeacherResultController;
use App\Http\Middleware\CheckRole;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Default dashboard route - will redirect to role-specific dashboard
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Profile routes - accessible by all authenticated users
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
});

// Admin routes
Route::middleware(['auth', CheckRole::class . ':admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    
    Route::resource('subjects', SubjectController::class);
    Route::resource('teachers', TeacherController::class);
    Route::resource('students', StudentController::class);
});

// Teacher routes
Route::middleware(['auth', CheckRole::class . ':teacher'])->prefix('teacher')->group(function () {
    Route::get('/dashboard', function () {
        return view('teacher.dashboard');
    })->name('teacher.dashboard');
    
    // MCQ Test routes
    Route::get('/mcq-tests', [MCQTestController::class, 'index'])->name('mcq-tests.index');
    Route::get('/mcq-tests/create', [MCQTestController::class, 'create'])->name('mcq-tests.create');
    Route::post('/mcq-tests', [MCQTestController::class, 'store'])->name('mcq-tests.store');
    Route::get('/mcq-tests/{mcqTest}', [MCQTestController::class, 'show'])->name('mcq-tests.show');
    Route::get('/mcq-tests/{mcqTest}/edit', [MCQTestController::class, 'edit'])->name('mcq-tests.edit');
    Route::put('/mcq-tests/{mcqTest}', [MCQTestController::class, 'update'])->name('mcq-tests.update');
    Route::delete('/mcq-tests/{mcqTest}', [MCQTestController::class, 'destroy'])->name('mcq-tests.destroy');
    Route::post('/mcq-tests/{mcqTest}/questions', [MCQTestController::class, 'storeQuestions'])->name('mcq-tests.questions.store');
    Route::post('/mcq-tests/{mcqTest}/questions/import', [MCQTestController::class, 'importQuestions'])->name('mcq-tests.questions.import');
    Route::get('/mcq-tests/{mcqTest}/questions/mark-correct', [MCQTestController::class, 'showMarkCorrectForm'])->name('mcq-tests.questions.mark-correct');
    Route::post('/mcq-tests/{mcqTest}/questions/mark-correct', [MCQTestController::class, 'updateCorrectOptions'])->name('mcq-tests.questions.update-correct');
    Route::get('/mcq-tests/{mcqTest}/results', [MCQTestController::class, 'getResults'])->name('mcq-tests.results');
    
    // Questions routes
    Route::resource('questions', QuestionController::class);
    
    // Results routes
    Route::get('/student-results', [TeacherResultController::class, 'index'])->name('teacher.results.index');
    Route::get('/student-results/{result}', [TeacherResultController::class, 'show'])->name('teacher.results.show');

    // MCQ Tests
    Route::prefix('tests')->name('tests.')->group(function () {
        Route::get('/results/{test}', [MCQTestController::class, 'getResults'])->name('results');
        Route::get('/student-attempt/{id}', [TeacherResultController::class, 'show'])->name('student.attempt.show');
    });
});

// Student routes
Route::middleware(['auth', CheckRole::class . ':student'])->prefix('student')->group(function () {
    Route::get('/dashboard', [MCQTestController::class, 'index'])->name('student.dashboard');
    
    // Test taking routes
    Route::get('/available-tests', [MCQTestController::class, 'availableTestsPage'])->name('tests.available');
    Route::get('/test/{mcq_test}/attempt', [MCQTestController::class, 'attempt'])->name('test.attempt');
    Route::post('/test/{mcq_test}/submit', [MCQTestController::class, 'submit'])->name('test.submit');
    Route::post('/test/update-review-status', [MCQTestController::class, 'updateReviewStatus'])->name('test.update-review-status');
    
    // Results routes
    Route::get('/results', [ResultController::class, 'index'])->name('results.index');
    Route::get('/results/{result}', [ResultController::class, 'show'])->name('results.show');
});

// Include authentication routes
require __DIR__.'/auth.php';
