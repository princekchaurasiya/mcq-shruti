<?php

use App\Models\User;
use App\Models\Subject;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = 'student'; // Default role
    public string $subjects = ''; // For teacher's subjects

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:student,teacher'],
            'subjects' => $this->role === 'teacher' ? ['required', 'string'] : '',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        if ($validated['role'] === 'teacher') {
            // Create subjects from comma-separated values
            $subjectNames = array_map('trim', explode(',', $validated['subjects']));
            $subjectIds = [];
            
            foreach ($subjectNames as $name) {
                $subject = Subject::firstOrCreate([
                    'name' => $name
                ], [
                    'description' => 'Subject: ' . $name
                ]);
                $subjectIds[] = $subject->id;
            }

            // Create teacher with first subject as primary
            $user->teacher()->create([
                'subject_id' => $subjectIds[0], // Primary subject
                'qualifications' => '',
                'experience_years' => 0,
                'status' => 'pending',
            ]);
        }

        event(new Registered($user));

        Auth::login($user);

        $this->redirect(route($user->getDashboardRoute()), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="register">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Role Selection -->
        <div class="mt-4">
            <x-input-label :value="__('Register as')" class="mb-3" />
            <div class="mt-2 grid grid-cols-2 gap-4">
                <div class="relative group">
                    <input type="radio" wire:model.live="role" id="student" value="student" class="sr-only" />
                    <label for="student" 
                        @class([
                            'block cursor-pointer select-none rounded-lg p-6 text-center border-2 transition-all duration-300 transform group-hover:-translate-y-1 group-hover:shadow-2xl',
                            'shadow-lg border-gray-400 ring-2 ring-gray-400 bg-gray-100 scale-105 text-gray-700' => $role === 'student',
                            'hover:bg-gray-50 hover:border-gray-300 border-gray-200' => $role !== 'student'
                        ])>
                        <i @class([
                            'bi bi-mortarboard mb-3 text-4xl transition-transform duration-300 group-hover:scale-110',
                            'text-gray-600' => $role === 'student',
                            'text-gray-400 group-hover:text-gray-600' => $role !== 'student'
                        ])></i>
                        <div @class([
                            'font-semibold text-lg',
                            'text-gray-700' => $role === 'student',
                            'text-gray-500 group-hover:text-gray-700' => $role !== 'student'
                        ])>Student</div>
                    </label>
                </div>
                <div class="relative group">
                    <input type="radio" wire:model.live="role" id="teacher" value="teacher" class="sr-only" />
                    <label for="teacher" 
                        @class([
                            'block cursor-pointer select-none rounded-lg p-6 text-center border-2 transition-all duration-300 transform group-hover:-translate-y-1 group-hover:shadow-2xl',
                            'shadow-lg border-gray-400 ring-2 ring-gray-400 bg-gray-100 scale-105 text-gray-700' => $role === 'teacher',
                            'hover:bg-gray-50 hover:border-gray-300 border-gray-200' => $role !== 'teacher'
                        ])>
                        <i @class([
                            'bi bi-person-workspace mb-3 text-4xl transition-transform duration-300 group-hover:scale-110',
                            'text-gray-600' => $role === 'teacher',
                            'text-gray-400 group-hover:text-gray-600' => $role !== 'teacher'
                        ])></i>
                        <div @class([
                            'font-semibold text-lg',
                            'text-gray-700' => $role === 'teacher',
                            'text-gray-500 group-hover:text-gray-700' => $role !== 'teacher'
                        ])>Teacher</div>
                    </label>
                </div>
            </div>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <!-- Subjects (Only for Teachers) -->
        @if($role === 'teacher')
        <div class="mt-6 animate-fade-in">
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="bi bi-info-circle text-blue-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Please enter the subjects you can teach. You can enter multiple subjects separated by commas.
                        </p>
                    </div>
                </div>
            </div>
            <x-input-label for="subjects" :value="__('Subjects You Can Teach')" />
            <div class="mt-2 relative">
                <i class="bi bi-book absolute left-3 top-3 text-gray-400"></i>
                <x-text-input 
                    wire:model="subjects" 
                    id="subjects" 
                    class="block mt-1 w-full pl-10" 
                    type="text" 
                    name="subjects" 
                    required 
                    placeholder="e.g., Mathematics, Physics, Chemistry" />
            </div>
            <p class="mt-2 text-sm text-gray-500">
                <i class="bi bi-lightbulb text-yellow-500 mr-1"></i>
                Examples: "Mathematics", "Physics, Chemistry", "English, Literature, Grammar"
            </p>
            <x-input-error :messages="$errors->get('subjects')" class="mt-2" />
        </div>
        @endif

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input wire:model="password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}" wire:navigate>
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</div>
