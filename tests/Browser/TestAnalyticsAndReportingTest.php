<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Test;
use App\Models\Question;
use App\Models\Option;
use App\Models\TestAttempt;
use App\Models\TestResponse;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TestAnalyticsAndReportingTest extends DuskTestCase
{
    /**
     * Test that a teacher can view basic test analytics
     */
    public function test_teacher_can_view_basic_test_analytics(): void
    {
        // Create a teacher
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);
        
        // Create students
        $student1 = User::factory()->create([
            'email' => 'student1_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);
        
        $student2 = User::factory()->create([
            'email' => 'student2_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);
        
        $student3 = User::factory()->create([
            'email' => 'student3_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        // Create a test
        $test = Test::factory()->create([
            'title' => 'Math Assessment',
            'description' => 'Basic math skills assessment',
            'user_id' => $teacher->id,
            'status' => 'published',
            'time_limit' => 30,
            'pass_score' => 70,
        ]);

        // Create test attempts with different scores
        TestAttempt::factory()->create([
            'user_id' => $student1->id,
            'test_id' => $test->id,
            'status' => 'completed',
            'score' => 90,
            'start_time' => now()->subDays(2),
            'end_time' => now()->subDays(2)->addMinutes(25),
        ]);
        
        TestAttempt::factory()->create([
            'user_id' => $student2->id,
            'test_id' => $test->id,
            'status' => 'completed',
            'score' => 60,
            'start_time' => now()->subDays(1),
            'end_time' => now()->subDays(1)->addMinutes(28),
        ]);
        
        TestAttempt::factory()->create([
            'user_id' => $student3->id,
            'test_id' => $test->id,
            'status' => 'completed',
            'score' => 75,
            'start_time' => now()->subHours(5),
            'end_time' => now()->subHours(5)->addMinutes(22),
        ]);

        $this->browse(function (Browser $browser) use ($teacher, $test) {
            $browser->loginAs($teacher)
                    ->visit('/teacher/tests')
                    ->assertSee('Math Assessment')
                    ->click('@view-analytics-' . $test->id)
                    ->assertSee('Test Analytics: Math Assessment')
                    ->screenshot('test-analytics-overview')
                    
                    // Check for basic statistics
                    ->assertSee('Total Attempts: 3')
                    ->assertSee('Average Score: 75%')
                    ->assertSee('Pass Rate: 67%') // 2 out of 3 passed
                    ->assertSee('Average Completion Time: 25 minutes')
                    
                    // Check for score distribution chart
                    ->assertPresent('@score-distribution-chart')
                    
                    // Check for student performance table
                    ->assertSee('Student Performance')
                    ->assertSee($student1->name)
                    ->assertSee('90%')
                    ->assertSee('Passed')
                    ->assertSee($student2->name)
                    ->assertSee('60%')
                    ->assertSee('Failed')
                    ->assertSee($student3->name)
                    ->assertSee('75%')
                    ->assertSee('Passed');
        });
    }

    /**
     * Test that a teacher can view detailed question analysis
     */
    public function test_teacher_can_view_question_analysis(): void
    {
        // Create a teacher
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);
        
        // Create multiple students
        $students = User::factory()->count(10)->create([
            'role' => 'student',
        ]);

        // Create a test with questions
        $test = Test::factory()->create([
            'title' => 'Science Test',
            'user_id' => $teacher->id,
            'status' => 'published',
        ]);

        // Create questions
        $question1 = Question::factory()->create([
            'test_id' => $test->id,
            'question_text' => 'What is the chemical symbol for gold?',
            'question_type' => 'multiple_choice',
            'order' => 1,
        ]);

        $correctOption1 = Option::factory()->create([
            'question_id' => $question1->id,
            'text' => 'Au',
            'is_correct' => 1,
        ]);

        $incorrectOption1A = Option::factory()->create([
            'question_id' => $question1->id,
            'text' => 'Ag',
            'is_correct' => 0,
        ]);

        $incorrectOption1B = Option::factory()->create([
            'question_id' => $question1->id,
            'text' => 'Fe',
            'is_correct' => 0,
        ]);

        $question2 = Question::factory()->create([
            'test_id' => $test->id,
            'question_text' => 'Which planet is closest to the sun?',
            'question_type' => 'multiple_choice',
            'order' => 2,
        ]);

        $correctOption2 = Option::factory()->create([
            'question_id' => $question2->id,
            'text' => 'Mercury',
            'is_correct' => 1,
        ]);

        $incorrectOption2A = Option::factory()->create([
            'question_id' => $question2->id,
            'text' => 'Venus',
            'is_correct' => 0,
        ]);

        $incorrectOption2B = Option::factory()->create([
            'question_id' => $question2->id,
            'text' => 'Earth',
            'is_correct' => 0,
        ]);

        // Create test attempts and responses with varying correctness patterns
        foreach ($students as $index => $student) {
            $testAttempt = TestAttempt::factory()->create([
                'user_id' => $student->id,
                'test_id' => $test->id,
                'status' => 'completed',
                'score' => $index < 7 ? 50 : 100, // 7 students got 50%, 3 got 100%
            ]);
            
            // Question 1: 8 correct, 2 incorrect (common mistake: Ag)
            TestResponse::factory()->create([
                'test_attempt_id' => $testAttempt->id,
                'question_id' => $question1->id,
                'selected_option_id' => $index < 8 ? $correctOption1->id : $incorrectOption1A->id,
                'is_correct' => $index < 8,
            ]);
            
            // Question 2: 5 correct, 5 incorrect (common mistake: Venus)
            TestResponse::factory()->create([
                'test_attempt_id' => $testAttempt->id,
                'question_id' => $question2->id,
                'selected_option_id' => $index < 5 ? $correctOption2->id : $incorrectOption2A->id,
                'is_correct' => $index < 5,
            ]);
        }

        $this->browse(function (Browser $browser) use ($teacher, $test, $question1, $question2) {
            $browser->loginAs($teacher)
                    ->visit('/teacher/tests')
                    ->click('@view-analytics-' . $test->id)
                    ->click('@question-analysis')
                    ->assertSee('Question Analysis')
                    ->screenshot('question-analysis-overview')
                    
                    // Should see difficulty level for each question
                    ->assertSee('Question 1: What is the chemical symbol for gold?')
                    ->assertSee('Difficulty: Easy (80% correct)')
                    
                    ->assertSee('Question 2: Which planet is closest to the sun?')
                    ->assertSee('Difficulty: Medium (50% correct)')
                    
                    // View details for question 1
                    ->click('@view-question-details-' . $question1->id)
                    ->assertSee('Question Details')
                    ->screenshot('question-1-details')
                    
                    // Check answer distribution
                    ->assertSee('Answer Distribution')
                    ->assertSee('Au: 80%')
                    ->assertSee('Ag: 20%')
                    ->assertSee('Fe: 0%')
                    
                    // Should indicate common mistakes
                    ->assertSee('Common Mistake: Ag')
                    
                    // Go back to question list and view question 2
                    ->click('@back-to-questions')
                    ->click('@view-question-details-' . $question2->id)
                    ->screenshot('question-2-details')
                    
                    // Check answer distribution for question 2
                    ->assertSee('Answer Distribution')
                    ->assertSee('Mercury: 50%')
                    ->assertSee('Venus: 50%')
                    ->assertSee('Earth: 0%')
                    
                    // Should indicate common mistakes
                    ->assertSee('Common Mistake: Venus');
        });
    }

    /**
     * Test that a teacher can generate and export test reports
     */
    public function test_teacher_can_generate_and_export_reports(): void
    {
        // Create a teacher
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);
        
        // Create students in a class
        $students = User::factory()->count(5)->create([
            'role' => 'student',
        ]);

        // Create tests
        $test1 = Test::factory()->create([
            'title' => 'Midterm Exam',
            'user_id' => $teacher->id,
            'status' => 'published',
        ]);
        
        $test2 = Test::factory()->create([
            'title' => 'Final Exam',
            'user_id' => $teacher->id,
            'status' => 'published',
        ]);

        // Create test attempts for each student
        foreach ($students as $student) {
            // Midterm attempts
            TestAttempt::factory()->create([
                'user_id' => $student->id,
                'test_id' => $test1->id,
                'status' => 'completed',
                'score' => rand(60, 95),
            ]);
            
            // Final exam attempts
            TestAttempt::factory()->create([
                'user_id' => $student->id,
                'test_id' => $test2->id,
                'status' => 'completed',
                'score' => rand(65, 98),
            ]);
        }

        $this->browse(function (Browser $browser) use ($teacher, $test1, $test2) {
            $browser->loginAs($teacher)
                    ->visit('/teacher/reports')
                    ->assertSee('Test Reports')
                    ->screenshot('reports-dashboard')
                    
                    // Generate single test report
                    ->select('test_id', $test1->id)
                    ->press('Generate Report')
                    ->assertSee('Test Report: Midterm Exam')
                    ->assertSee('Student Results')
                    ->screenshot('single-test-report')
                    
                    // Should show each student's score
                    ->assertPresent('@student-results-table')
                    ->assertPresent('@export-csv')
                    ->assertPresent('@export-pdf')
                    
                    // Go back and generate comparative report
                    ->visit('/teacher/reports')
                    ->click('@comparative-report')
                    ->select('test_id_1', $test1->id)
                    ->select('test_id_2', $test2->id)
                    ->press('Generate Comparison')
                    ->assertSee('Comparative Report')
                    ->assertSee('Midterm Exam vs. Final Exam')
                    ->screenshot('comparative-report')
                    
                    // Should show comparison chart
                    ->assertPresent('@comparison-chart')
                    
                    // Should show individual improvement
                    ->assertSee('Student Progress')
                    ->assertPresent('@student-progress-table')
                    
                    // Export options should be available
                    ->assertPresent('@export-comparison-csv')
                    ->assertPresent('@export-comparison-pdf');
        });
    }

    /**
     * Test that a teacher can view class performance analytics
     */
    public function test_teacher_can_view_class_performance(): void
    {
        // Create a teacher
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);
        
        // Create a class with students
        $classGroup = \App\Models\ClassGroup::factory()->create([
            'name' => 'Class 10A',
            'teacher_id' => $teacher->id,
        ]);
        
        // Create 15 students in the class
        $students = User::factory()->count(15)->create([
            'role' => 'student',
        ]);
        
        // Add students to class
        foreach ($students as $student) {
            \App\Models\ClassStudent::factory()->create([
                'class_id' => $classGroup->id,
                'student_id' => $student->id,
            ]);
        }

        // Create multiple tests for this class
        $test1 = Test::factory()->create([
            'title' => 'Quiz 1',
            'user_id' => $teacher->id,
            'status' => 'published',
            'class_id' => $classGroup->id,
        ]);
        
        $test2 = Test::factory()->create([
            'title' => 'Quiz 2',
            'user_id' => $teacher->id,
            'status' => 'published',
            'class_id' => $classGroup->id,
        ]);
        
        $test3 = Test::factory()->create([
            'title' => 'Quiz 3',
            'user_id' => $teacher->id,
            'status' => 'published',
            'class_id' => $classGroup->id,
        ]);

        // Create test attempts with a distribution of scores
        // Quiz 1: Average score around 70%
        foreach ($students as $index => $student) {
            TestAttempt::factory()->create([
                'user_id' => $student->id,
                'test_id' => $test1->id,
                'status' => 'completed',
                'score' => 65 + ($index % 10), // Scores from 65 to 74
                'start_time' => now()->subDays(30),
                'end_time' => now()->subDays(30)->addMinutes(20),
            ]);
        }
        
        // Quiz 2: Average score around 75%
        foreach ($students as $index => $student) {
            TestAttempt::factory()->create([
                'user_id' => $student->id,
                'test_id' => $test2->id,
                'status' => 'completed',
                'score' => 70 + ($index % 10), // Scores from 70 to 79
                'start_time' => now()->subDays(20),
                'end_time' => now()->subDays(20)->addMinutes(25),
            ]);
        }
        
        // Quiz 3: Average score around 80%
        foreach ($students as $index => $student) {
            TestAttempt::factory()->create([
                'user_id' => $student->id,
                'test_id' => $test3->id,
                'status' => 'completed',
                'score' => 75 + ($index % 10), // Scores from 75 to 84
                'start_time' => now()->subDays(10),
                'end_time' => now()->subDays(10)->addMinutes(22),
            ]);
        }

        $this->browse(function (Browser $browser) use ($teacher, $classGroup) {
            $browser->loginAs($teacher)
                    ->visit('/teacher/classes')
                    ->assertSee('Class 10A')
                    ->click('@view-class-' . $classGroup->id)
                    ->assertSee('Class 10A')
                    ->click('@class-analytics')
                    ->assertSee('Class Performance Analytics')
                    ->screenshot('class-performance-overview')
                    
                    // Should see performance over time chart
                    ->assertPresent('@performance-trend-chart')
                    ->assertSee('Performance Trend')
                    
                    // Should show improvement from Quiz 1 to Quiz 3
                    ->assertSee('Average Score Trend: 69.5% → 74.5% → 79.5%')
                    ->assertSee('Improvement: 10%')
                    
                    // Should show individual student progress
                    ->assertSee('Student Progress Tracking')
                    ->assertPresent('@student-progress-table')
                    
                    // Should show top performers and struggling students
                    ->assertSee('Top Performers')
                    ->assertSee('Students Needing Assistance')
                    
                    // Export full class report option
                    ->assertPresent('@export-class-report')
                    ->click('@export-class-report')
                    ->assertDialogOpened('Download class performance report?')
                    ->acceptDialog();
        });
    }

    /**
     * Test that a teacher can view individual student analytics
     */
    public function test_teacher_can_view_individual_student_analytics(): void
    {
        // Create a teacher
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);
        
        // Create a student
        $student = User::factory()->create([
            'name' => 'John Smith',
            'email' => 'student_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        // Create multiple tests
        $tests = Test::factory()->count(5)->create([
            'user_id' => $teacher->id,
            'status' => 'published',
        ]);
        
        // Create questions for each test (simplified)
        foreach ($tests as $test) {
            $questions = Question::factory()->count(10)->create([
                'test_id' => $test->id,
                'question_type' => 'multiple_choice',
            ]);
            
            foreach ($questions as $question) {
                Option::factory()->create([
                    'question_id' => $question->id,
                    'is_correct' => 1,
                ]);
                
                Option::factory()->count(3)->create([
                    'question_id' => $question->id,
                    'is_correct' => 0,
                ]);
            }
        }

        // Create test attempts with different scores and patterns
        $scores = [65, 70, 68, 75, 85]; // Showing improvement over time
        
        foreach ($tests as $index => $test) {
            $testAttempt = TestAttempt::factory()->create([
                'user_id' => $student->id,
                'test_id' => $test->id,
                'status' => 'completed',
                'score' => $scores[$index],
                'start_time' => now()->subDays(30 - $index * 5), // Spaced out over time
                'end_time' => now()->subDays(30 - $index * 5)->addMinutes(25),
            ]);
            
            // Create test responses with some pattern (e.g., struggles with certain question types)
            foreach ($test->questions as $qIndex => $question) {
                // Simulate student struggling with later questions
                $isCorrect = $qIndex < 7 || $index >= 3; // Gets later questions wrong in early tests
                
                TestResponse::factory()->create([
                    'test_attempt_id' => $testAttempt->id,
                    'question_id' => $question->id,
                    'selected_option_id' => $isCorrect 
                        ? $question->options->where('is_correct', 1)->first()->id
                        : $question->options->where('is_correct', 0)->first()->id,
                    'is_correct' => $isCorrect,
                ]);
            }
        }

        $this->browse(function (Browser $browser) use ($teacher, $student, $tests) {
            $browser->loginAs($teacher)
                    ->visit('/teacher/students')
                    ->assertSee('John Smith')
                    ->click('@view-student-' . $student->id)
                    ->assertSee('Student Profile: John Smith')
                    ->assertSee('Test History')
                    ->click('@student-analytics')
                    ->assertSee('Student Performance Analytics')
                    ->screenshot('student-analytics-overview')
                    
                    // Should see performance trend chart
                    ->assertPresent('@student-performance-chart')
                    ->assertSee('Performance Over Time')
                    
                    // Should show pattern of improvement
                    ->assertSee('Score Trend: 65% → 70% → 68% → 75% → 85%')
                    ->assertSee('Overall Improvement: 20%')
                    
                    // Should show strength and weakness analysis
                    ->assertSee('Strengths and Weaknesses')
                    ->assertSee('Early Questions (Strong)')
                    ->assertSee('Later Questions (Improving)')
                    
                    // Should show test completion times
                    ->assertSee('Average Completion Time: 25 minutes')
                    
                    // Should show recommendations based on performance
                    ->assertSee('Recommendations')
                    ->assertPresent('@recommendations-section')
                    
                    // Generate individual student report
                    ->click('@generate-student-report')
                    ->assertSee('Student Report: John Smith')
                    ->screenshot('student-detailed-report')
                    
                    // Export options
                    ->assertPresent('@export-student-report-pdf');
        });
    }

    /**
     * Test that a teacher can filter and customize analytics views
     */
    public function test_teacher_can_filter_and_customize_analytics(): void
    {
        // Create a teacher
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);
        
        // Create classes
        $class1 = \App\Models\ClassGroup::factory()->create([
            'name' => 'Class 9A',
            'teacher_id' => $teacher->id,
        ]);
        
        $class2 = \App\Models\ClassGroup::factory()->create([
            'name' => 'Class 10B',
            'teacher_id' => $teacher->id,
        ]);
        
        // Create students in different classes
        $class1Students = User::factory()->count(10)->create(['role' => 'student']);
        $class2Students = User::factory()->count(8)->create(['role' => 'student']);
        
        // Add students to classes
        foreach ($class1Students as $student) {
            \App\Models\ClassStudent::factory()->create([
                'class_id' => $class1->id,
                'student_id' => $student->id,
            ]);
        }
        
        foreach ($class2Students as $student) {
            \App\Models\ClassStudent::factory()->create([
                'class_id' => $class2->id,
                'student_id' => $student->id,
            ]);
        }

        // Create tests for different time periods and classes
        $test1 = Test::factory()->create([
            'title' => 'January Quiz',
            'user_id' => $teacher->id,
            'status' => 'published',
            'class_id' => $class1->id,
            'created_at' => now()->subMonths(3),
        ]);
        
        $test2 = Test::factory()->create([
            'title' => 'February Quiz',
            'user_id' => $teacher->id,
            'status' => 'published',
            'class_id' => $class1->id,
            'created_at' => now()->subMonths(2),
        ]);
        
        $test3 = Test::factory()->create([
            'title' => 'March Quiz',
            'user_id' => $teacher->id,
            'status' => 'published',
            'class_id' => $class2->id,
            'created_at' => now()->subMonth(),
        ]);

        // Create test attempts for all tests
        foreach ($class1Students as $student) {
            TestAttempt::factory()->create([
                'user_id' => $student->id,
                'test_id' => $test1->id,
                'status' => 'completed',
                'score' => rand(60, 85),
                'start_time' => now()->subMonths(3),
            ]);
            
            TestAttempt::factory()->create([
                'user_id' => $student->id,
                'test_id' => $test2->id,
                'status' => 'completed',
                'score' => rand(65, 90),
                'start_time' => now()->subMonths(2),
            ]);
        }
        
        foreach ($class2Students as $student) {
            TestAttempt::factory()->create([
                'user_id' => $student->id,
                'test_id' => $test3->id,
                'status' => 'completed',
                'score' => rand(70, 95),
                'start_time' => now()->subMonth(),
            ]);
        }

        $this->browse(function (Browser $browser) use ($teacher, $class1, $class2, $test1, $test2, $test3) {
            $browser->loginAs($teacher)
                    ->visit('/teacher/analytics')
                    ->assertSee('Analytics Dashboard')
                    ->screenshot('analytics-dashboard')
                    
                    // Should see all tests by default
                    ->assertSee('January Quiz')
                    ->assertSee('February Quiz')
                    ->assertSee('March Quiz')
                    
                    // Filter by class
                    ->select('class_filter', $class1->id)
                    ->press('@apply-filters')
                    ->assertSee('January Quiz')
                    ->assertSee('February Quiz')
                    ->assertDontSee('March Quiz')
                    ->screenshot('filtered-by-class')
                    
                    // Filter by date range
                    ->visit('/teacher/analytics')
                    ->type('date_from', now()->subMonths(2)->format('Y-m-d'))
                    ->type('date_to', now()->format('Y-m-d'))
                    ->press('@apply-filters')
                    ->assertDontSee('January Quiz')
                    ->assertSee('February Quiz')
                    ->assertSee('March Quiz')
                    ->screenshot('filtered-by-date')
                    
                    // Filter by both class and date
                    ->select('class_filter', $class1->id)
                    ->press('@apply-filters')
                    ->assertDontSee('January Quiz')
                    ->assertSee('February Quiz')
                    ->assertDontSee('March Quiz')
                    ->screenshot('filtered-by-class-and-date')
                    
                    // Customize chart view
                    ->select('chart_type', 'bar')
                    ->press('@update-chart')
                    ->assertPresent('@bar-chart')
                    ->screenshot('bar-chart-view')
                    
                    ->select('chart_type', 'line')
                    ->press('@update-chart')
                    ->assertPresent('@line-chart')
                    ->screenshot('line-chart-view')
                    
                    // Export filtered data
                    ->click('@export-filtered-data')
                    ->assertDialogOpened('Download filtered analytics data?')
                    ->acceptDialog();
        });
    }
} 