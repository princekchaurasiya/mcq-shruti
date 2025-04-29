<?php

use function Livewire\Volt\{state};

state(['user' => fn() => auth()->user()]);

$logout = function () {
    auth()->logout();
    session()->invalidate();
    session()->regenerateToken();
    
    return redirect('/');
};

?>

<nav class="navbar navbar-expand-lg navbar-dark bg-info fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Student Panel</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" 
                       href="{{ route('student.dashboard') }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('tests.available') ? 'active' : '' }}" 
                       href="{{ route('tests.available') }}">
                        <i class="bi bi-journal-text"></i> Available Tests
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('student.results.*') ? 'active' : '' }}" 
                       href="{{ route('student.results.index') }}">
                        <i class="bi bi-graph-up"></i> My Results
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> {{ $user->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person"></i> Profile
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <button wire:click="logout" class="dropdown-item">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </button>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
