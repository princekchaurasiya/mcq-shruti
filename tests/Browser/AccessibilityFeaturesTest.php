<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Test;
use App\Models\Question;
use App\Models\Option;
use App\Models\AccessibilitySettings;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AccessibilityFeaturesTest extends DuskTestCase
{
    /**
     * Test that students can configure accessibility settings
     */
    public function test_student_can_configure_accessibility_settings(): void
    {
        // Create a student
        $student = User::factory()->create([
            'email' => 'student_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        $this->browse(function (Browser $browser) use ($student) {
            $browser->loginAs($student)
                    ->visit('/student/settings/accessibility')
                    ->assertSee('Accessibility Settings')
                    ->screenshot('accessibility-settings')
                    
                    // Test font size settings
                    ->assertSee('Font Size')
                    ->select('font_size', 'large')
                    
                    // Test high contrast mode
                    ->assertSee('High Contrast Mode')
                    ->check('high_contrast')
                    
                    // Test screen reader compatibility
                    ->assertSee('Screen Reader Compatibility')
                    ->check('screen_reader_compatibility')
                    
                    // Test extended time accommodations
                    ->assertSee('Time Accommodations')
                    ->select('time_extension', '50')
                    ->type('time_extension_reason', 'Dyslexia accommodation')
                    
                    // Test text-to-speech feature
                    ->assertSee('Text-to-Speech')
                    ->check('text_to_speech')
                    
                    // Test color overlay feature
                    ->assertSee('Color Overlay')
                    ->select('color_overlay', 'yellow')
                    
                    // Save settings
                    ->press('Save Settings')
                    ->waitForText('Settings saved successfully')
                    ->screenshot('accessibility-settings-saved');
                    
            // Verify the settings were saved to the database
            $settings = AccessibilitySettings::where('user_id', $student->id)->first();
            $this->assertEquals('large', $settings->font_size);
            $this->assertEquals(1, $settings->high_contrast);
            $this->assertEquals(1, $settings->screen_reader_compatibility);
            $this->assertEquals(50, $settings->time_extension);
            $this->assertEquals('Dyslexia accommodation', $settings->time_extension_reason);
            $this->assertEquals(1, $settings->text_to_speech);
            $this->assertEquals('yellow', $settings->color_overlay);
        });
    }

    /**
     * Test that accessibility settings are applied to test interface
     */
    public function test_accessibility_settings_are_applied_to_test_interface(): void
    {
        // Create a student with predefined accessibility settings
        $student = User::factory()->create([
            'email' => 'student_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);
        
        // Create accessibility settings
        AccessibilitySettings::create([
            'user_id' => $student->id,
            'font_size' => 'large',
            'high_contrast' => 1,
            'screen_reader_compatibility' => 1,
            'time_extension' => 50,
            'text_to_speech' => 1,
            'color_overlay' => 'yellow',
        ]);
        
        // Create a test
        $test = Test::factory()->create([
            'title' => 'Science Quiz',
            'description' => 'Basic science concepts',
            'status' => 'published',
            'time_limit' => 20,
        ]);
        
        // Create questions
        $question = Question::factory()->create([
            'test_id' => $test->id,
            'question_text' => 'What is photosynthesis?',
            'question_type' => 'multiple_choice',
        ]);
        
        Option::factory()->create([
            'question_id' => $question->id,
            'text' => 'The process by which plants make food using sunlight',
            'is_correct' => 1,
        ]);
        
        Option::factory()->count(3)->create([
            'question_id' => $question->id,
            'is_correct' => 0,
        ]);

        $this->browse(function (Browser $browser) use ($student, $test) {
            $browser->loginAs($student)
                    ->visit('/student/tests')
                    ->assertSee('Science Quiz')
                    ->click('@start-test-' . $test->id)
                    ->assertSee('Science Quiz')
                    ->screenshot('test-with-accessibility')
                    
                    // Check that font size is applied
                    ->assertPresent('.large-font')
                    ->assertSourceHas('class="large-font"')
                    
                    // Check that high contrast mode is applied
                    ->assertPresent('.high-contrast-mode')
                    ->assertSourceHas('class="high-contrast-mode"')
                    
                    // Check that color overlay is applied
                    ->assertPresent('.color-overlay-yellow')
                    ->assertSourceHas('class="color-overlay-yellow"')
                    
                    // Check that time extension is applied
                    ->assertSee('30 minutes remaining') // 20 + 50% = 30 minutes
                    
                    // Check text-to-speech controls are present
                    ->assertPresent('@text-to-speech-control')
                    ->click('@text-to-speech-control')
                    ->waitFor('.tts-active')
                    ->assertPresent('.tts-active')
                    ->screenshot('text-to-speech-active');
        });
    }

    /**
     * Test that teacher can approve accessibility accommodation requests
     */
    public function test_teacher_can_approve_accessibility_accommodation_requests(): void
    {
        // Create a teacher
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);
        
        // Create students with accommodation requests
        $student1 = User::factory()->create([
            'name' => 'Alice Johnson',
            'email' => 'student1_' . Str::random(8) . '@example.com',
            'role' => 'student',
        ]);
        
        $student2 = User::factory()->create([
            'name' => 'Bob Smith',
            'email' => 'student2_' . Str::random(8) . '@example.com',
            'role' => 'student',
        ]);
        
        // Create accessibility settings with pending approvals
        AccessibilitySettings::create([
            'user_id' => $student1->id,
            'time_extension' => 100, // 100% extra time
            'time_extension_reason' => 'ADHD accommodation',
            'time_extension_approval' => 'pending',
        ]);
        
        AccessibilitySettings::create([
            'user_id' => $student2->id,
            'time_extension' => 50, // 50% extra time
            'time_extension_reason' => 'Dyslexia accommodation',
            'time_extension_approval' => 'pending',
        ]);

        $this->browse(function (Browser $browser) use ($teacher, $student1, $student2) {
            $browser->loginAs($teacher)
                    ->visit('/teacher/accommodations')
                    ->assertSee('Accommodation Requests')
                    ->screenshot('accommodation-requests')
                    
                    // Should see pending requests
                    ->assertSee('Alice Johnson')
                    ->assertSee('ADHD accommodation')
                    ->assertSee('100% extra time')
                    ->assertPresent('@approve-' . $student1->id)
                    ->assertPresent('@deny-' . $student1->id)
                    
                    ->assertSee('Bob Smith')
                    ->assertSee('Dyslexia accommodation')
                    ->assertSee('50% extra time')
                    
                    // Approve first request
                    ->click('@approve-' . $student1->id)
                    ->waitForText('Accommodation approved')
                    ->assertSee('Approved')
                    ->screenshot('after-approval')
                    
                    // Deny second request but provide alternative
                    ->click('@deny-' . $student2->id)
                    ->waitFor('#alternative-accommodation-modal')
                    ->assertSee('Provide Alternative Accommodation')
                    ->type('alternative_time_extension', '25')
                    ->type('denial_reason', 'Approved for 25% extension instead of requested 50%')
                    ->press('Submit Alternative')
                    ->waitForText('Alternative accommodation provided')
                    ->screenshot('after-alternative-accommodation');
                    
            // Verify the database was updated correctly
            $settings1 = AccessibilitySettings::where('user_id', $student1->id)->first();
            $this->assertEquals('approved', $settings1->time_extension_approval);
            
            $settings2 = AccessibilitySettings::where('user_id', $student2->id)->first();
            $this->assertEquals('alternative', $settings2->time_extension_approval);
            $this->assertEquals(25, $settings2->time_extension);
            $this->assertEquals('Approved for 25% extension instead of requested 50%', $settings2->denial_reason);
        });
    }

    /**
     * Test that tests can be taken using keyboard navigation only
     */
    public function test_tests_can_be_taken_using_keyboard_navigation_only(): void
    {
        // Create a student
        $student = User::factory()->create([
            'email' => 'student_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);
        
        // Create accessibility settings with keyboard navigation
        AccessibilitySettings::create([
            'user_id' => $student->id,
            'keyboard_navigation' => 1,
        ]);
        
        // Create a test
        $test = Test::factory()->create([
            'title' => 'History Quiz',
            'status' => 'published',
        ]);
        
        // Create questions
        $question1 = Question::factory()->create([
            'test_id' => $test->id,
            'question_text' => 'Who was the first president of the US?',
            'question_type' => 'multiple_choice',
            'order' => 1,
        ]);
        
        Option::factory()->create([
            'question_id' => $question1->id,
            'text' => 'George Washington',
            'is_correct' => 1,
        ]);
        
        Option::factory()->count(3)->create([
            'question_id' => $question1->id,
            'is_correct' => 0,
        ]);
        
        $question2 = Question::factory()->create([
            'test_id' => $test->id,
            'question_text' => 'When was the Declaration of Independence signed?',
            'question_type' => 'multiple_choice',
            'order' => 2,
        ]);
        
        Option::factory()->create([
            'question_id' => $question2->id,
            'text' => '1776',
            'is_correct' => 1,
        ]);
        
        Option::factory()->count(3)->create([
            'question_id' => $question2->id,
            'is_correct' => 0,
        ]);

        $this->browse(function (Browser $browser) use ($student, $test) {
            $browser->loginAs($student)
                    ->visit('/student/tests')
                    ->assertSee('History Quiz')
                    ->screenshot('before-keyboard-nav')
                    
                    // Use keyboard to start test (tab to focus, enter to select)
                    ->keys('', ['{tab}', '{tab}']) // Focus on start button
                    ->keys('', ['{enter}']) // Press enter
                    ->assertSee('Who was the first president of the US?')
                    ->screenshot('question-1-keyboard')
                    
                    // Use keyboard to select an option (tab to focus, space to select)
                    ->keys('', ['{tab}', '{tab}', '{tab}']) // Focus on first option
                    ->keys('', [' ']) // Press space
                    ->assertPresent('.selected-option')
                    
                    // Use keyboard to navigate to next question (tab to next button, enter to select)
                    ->keys('', ['{tab}', '{tab}']) // Focus on next button
                    ->keys('', ['{enter}']) // Press enter
                    ->assertSee('When was the Declaration of Independence signed?')
                    ->screenshot('question-2-keyboard')
                    
                    // Use keyboard to select an option for question 2
                    ->keys('', ['{tab}', '{tab}', '{tab}']) // Focus on first option
                    ->keys('', [' ']) // Press space
                    
                    // Use keyboard to submit test (tab to submit button, enter to select)
                    ->keys('', ['{tab}', '{tab}', '{tab}', '{tab}']) // Focus on submit button
                    ->keys('', ['{enter}']) // Press enter
                    ->assertSee('Test Submitted')
                    ->screenshot('test-submitted-keyboard');
        });
    }

    /**
     * Test that math equations are accessible with MathML
     */
    public function test_math_equations_are_accessible_with_mathml(): void
    {
        // Create a student with screen reader compatibility
        $student = User::factory()->create([
            'email' => 'student_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);
        
        AccessibilitySettings::create([
            'user_id' => $student->id,
            'screen_reader_compatibility' => 1,
        ]);
        
        // Create a test with math equations
        $test = Test::factory()->create([
            'title' => 'Math Test',
            'status' => 'published',
        ]);
        
        // Create a question with math equation
        $question = Question::factory()->create([
            'test_id' => $test->id,
            'question_text' => 'Solve the equation: \(x^2 + 5x + 6 = 0\)',
            'question_type' => 'multiple_choice',
            'has_math_equations' => 1,
        ]);
        
        Option::factory()->create([
            'question_id' => $question->id,
            'text' => '\(x = -2\) or \(x = -3\)',
            'is_correct' => 1,
            'has_math_equations' => 1,
        ]);
        
        Option::factory()->count(3)->create([
            'question_id' => $question->id,
            'is_correct' => 0,
            'has_math_equations' => 1,
        ]);

        $this->browse(function (Browser $browser) use ($student, $test) {
            $browser->loginAs($student)
                    ->visit('/student/tests')
                    ->assertSee('Math Test')
                    ->click('@start-test-' . $test->id)
                    ->assertSee('Solve the equation:')
                    ->screenshot('math-equation-test')
                    
                    // Check that MathML is present in the source
                    ->assertSourceHas('<math xmlns="http://www.w3.org/1998/Math/MathML">')
                    
                    // Check that MathJax has rendered the equations
                    ->assertPresent('.MathJax')
                    
                    // Check that there are aria-label attributes for screen readers
                    ->assertSourceHas('aria-label="x squared plus 5x plus 6 equals 0"')
                    
                    // Check that screen reader toggle is available
                    ->assertPresent('@toggle-equation-descriptions')
                    ->click('@toggle-equation-descriptions')
                    
                    // Check that equation verbal descriptions appear
                    ->assertVisible('.equation-verbal-description')
                    ->assertSee('x squared plus 5x plus 6 equals 0')
                    ->screenshot('math-equation-verbal-description');
        });
    }

    /**
     * Test alternative input methods for accessibility
     */
    public function test_alternative_input_methods_are_supported(): void
    {
        // Create a student with motor disability accommodations
        $student = User::factory()->create([
            'email' => 'student_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);
        
        AccessibilitySettings::create([
            'user_id' => $student->id,
            'alternative_input' => 1,
            'voice_input' => 1,
        ]);
        
        // Create a test
        $test = Test::factory()->create([
            'title' => 'Geography Quiz',
            'status' => 'published',
        ]);
        
        // Create a question
        $question = Question::factory()->create([
            'test_id' => $test->id,
            'question_text' => 'What is the capital of France?',
            'question_type' => 'multiple_choice',
        ]);
        
        Option::factory()->create([
            'question_id' => $question->id,
            'text' => 'Paris',
            'is_correct' => 1,
        ]);
        
        Option::factory()->create([
            'question_id' => $question->id,
            'text' => 'London',
            'is_correct' => 0,
        ]);
        
        Option::factory()->create([
            'question_id' => $question->id,
            'text' => 'Berlin',
            'is_correct' => 0,
        ]);
        
        Option::factory()->create([
            'question_id' => $question->id,
            'text' => 'Madrid',
            'is_correct' => 0,
        ]);

        $this->browse(function (Browser $browser) use ($student, $test) {
            $browser->loginAs($student)
                    ->visit('/student/tests')
                    ->assertSee('Geography Quiz')
                    ->click('@start-test-' . $test->id)
                    ->assertSee('What is the capital of France?')
                    ->screenshot('alternative-input-test')
                    
                    // Check that voice input controls are available
                    ->assertPresent('@voice-input-start')
                    ->click('@voice-input-start')
                    ->waitFor('.voice-input-active')
                    ->assertPresent('.voice-input-active')
                    ->screenshot('voice-input-active')
                    
                    // Simulate voice command (mock)
                    ->script([
                        'window.dispatchEvent(new CustomEvent("voiceInputResult", { 
                            detail: { text: "select Paris" } 
                        }));'
                    ])
                    
                    // Check that the correct option was selected via voice
                    ->assertPresent('.selected-option')
                    ->assertSeeIn('.selected-option', 'Paris')
                    
                    // Check that switch navigation is available
                    ->assertPresent('@switch-navigation-controls')
                    ->assertSourceHas('class="switch-navigation-enabled"')
                    
                    // Check that scanning selection is available
                    ->assertPresent('@scanning-selection')
                    ->click('@scanning-selection-start')
                    ->waitFor('.scanning-active')
                    ->assertPresent('.scanning-active')
                    ->screenshot('scanning-selection-active')
                    
                    // Simulate scanning selection (mock)
                    ->script([
                        'window.dispatchEvent(new CustomEvent("scanningSelectionMade"));'
                    ])
                    
                    // Verify navigation controls for motor disabilities
                    ->assertPresent('@large-tap-targets')
                    ->assertSourceHas('class="large-tap-target-area"');
        });
    }

    /**
     * Test that students can request live assistance during a test
     */
    public function test_students_can_request_live_assistance(): void
    {
        // Create a student with accommodation for live assistance
        $student = User::factory()->create([
            'email' => 'student_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);
        
        AccessibilitySettings::create([
            'user_id' => $student->id,
            'live_assistance_allowed' => 1,
        ]);
        
        // Create a proctor
        $proctor = User::factory()->create([
            'email' => 'proctor_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'proctor',
        ]);
        
        // Create a test
        $test = Test::factory()->create([
            'title' => 'Physics Quiz',
            'status' => 'published',
        ]);
        
        // Create questions
        Question::factory()->count(3)->create([
            'test_id' => $test->id,
            'question_type' => 'multiple_choice',
        ]);

        $this->browse(function (Browser $browser) use ($student, $test) {
            $browser->loginAs($student)
                    ->visit('/student/tests')
                    ->assertSee('Physics Quiz')
                    ->click('@start-test-' . $test->id)
                    ->screenshot('test-with-assistance-option')
                    
                    // Check that live assistance button is available
                    ->assertPresent('@request-assistance')
                    ->click('@request-assistance')
                    
                    // Check assistance request modal
                    ->waitFor('#assistance-request-modal')
                    ->assertSee('Request Assistance')
                    ->select('assistance_type', 'question_clarification')
                    ->type('assistance_message', 'I need help understanding what this question is asking for.')
                    ->press('Request Help')
                    
                    // Check that the request was sent
                    ->waitForText('Assistance request sent')
                    ->assertSee('Assistance request sent')
                    ->assertSee('A proctor will be with you shortly')
                    ->screenshot('assistance-requested');
        });
        
        // Login as proctor in a second browser and check for assistance requests
        $this->browse(function (Browser $browser) use ($proctor, $student) {
            $browser->loginAs($proctor)
                    ->visit('/proctor/dashboard')
                    ->assertSee('Active Assistance Requests')
                    ->assertSee($student->name)
                    ->assertSee('question_clarification')
                    ->screenshot('proctor-sees-request')
                    
                    // Accept the request
                    ->click('@accept-request-' . $student->id)
                    ->waitFor('#assistance-chat')
                    ->assertSee('Assistance Chat')
                    ->assertSee('I need help understanding what this question is asking for.')
                    
                    // Send a response
                    ->type('assistance_response', 'The question is asking you to calculate the force given the mass and acceleration.')
                    ->press('Send')
                    ->waitForText('The question is asking you to calculate the force given the mass and acceleration.')
                    ->screenshot('proctor-sends-response');
        });
        
        // Check that the student received the response
        $this->browse(function (Browser $browser) use ($student) {
            $browser->loginAs($student)
                    ->visit('/student/tests/current')
                    ->waitFor('#assistance-chat')
                    ->assertSee('Assistance Chat')
                    ->assertSee('The question is asking you to calculate the force given the mass and acceleration.')
                    ->assertPresent('@close-assistance')
                    ->click('@close-assistance')
                    ->assertDontSee('Assistance Chat')
                    ->screenshot('student-received-assistance');
        });
    }
} 