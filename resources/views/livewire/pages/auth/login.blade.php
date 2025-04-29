<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirect($this->form->getRedirectRoute(), navigate: true);
    }
}; ?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
    
    <h2 class="auth-title">{{ __('Welcome Back') }}</h2>
    <p class="auth-subtitle">{{ __('Sign in to your account') }}</p>

    <form wire:submit="login">
        <!-- Validation Error Summary -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Email Address -->
        <div class="form-group">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="form.email" id="email" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="form-group">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input wire:model="form.password" id="password" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Forgot Password -->
        @if (Route::has('password.request'))
            <div class="forgot-password">
                <a class="auth-link" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Forgot your password?') }}
                </a>
            </div>
        @endif

        <!-- Remember Me -->
        <div class="form-check">
            <input wire:model="form.remember" id="remember" type="checkbox" class="form-check-input" name="remember">
            <label for="remember" class="form-check-label">{{ __('Remember me') }}</label>
        </div>

        <div>
            <x-primary-button>
                {{ __('LOG IN') }}
            </x-primary-button>
        </div>

        <!-- Register Link -->
        @if (Route::has('register'))
            <div class="text-center mt-4">
                <span class="text-gray-600">{{ __('Don\'t have an account?') }}</span>
                <a class="auth-link" href="{{ route('register') }}" wire:navigate>
                    {{ __('Register') }}
                </a>
            </div>
        @endif
    </form>
</div>
