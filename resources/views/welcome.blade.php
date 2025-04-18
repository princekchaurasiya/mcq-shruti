<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'MCQ Management') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <style>
            body {
                font-family: 'Nunito', sans-serif;
                background: #f8f9fa;
            }
            .welcome-section {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem;
            }
            .feature-card {
                transition: transform 0.3s ease;
            }
            .feature-card:hover {
                transform: translateY(-5px);
            }
        </style>
    </head>
    <body class="antialiased font-sans">
        <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'MCQ Management') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        @if (Route::has('login'))
                            @auth
                                <li class="nav-item">
                                    <a href="{{ url('/dashboard') }}" class="nav-link">Dashboard</a>
                                </li>
                            @else
                                <li class="nav-item">
                                    <a href="{{ route('login') }}" class="nav-link">Log in</a>
                                </li>
                                @if (Route::has('register'))
                                    <li class="nav-item">
                                        <a href="{{ route('register') }}" class="nav-link">Register</a>
                                    </li>
                                @endif
                            @endauth
                        @endif
                    </ul>
                                        </div>
                                    </div>
        </nav>

        <section class="welcome-section">
            <div class="container">
                <div class="text-center mb-5">
                    <h1 class="display-4 mb-3">Welcome to MCQ Management System</h1>
                    <p class="lead text-muted">A comprehensive platform for managing multiple-choice questions, exams, and student assessments.</p>
                    @if (Route::has('login'))
                        <div class="mt-4">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="btn btn-primary btn-lg">Go to Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-primary btn-lg me-2">Log in</a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg">Register</a>
                                @endif
                            @endauth
                                </div>
                    @endif
                                </div>

                <div class="row g-4 py-5">
                    <div class="col-md-4">
                        <div class="card h-100 shadow-sm feature-card">
                            <div class="card-body text-center">
                                <h3 class="card-title">For Teachers</h3>
                                <p class="card-text">Create and manage MCQ tests, track student progress, and generate detailed reports.</p>
                            </div>
                                </div>
                                </div>
                    <div class="col-md-4">
                        <div class="card h-100 shadow-sm feature-card">
                            <div class="card-body text-center">
                                <h3 class="card-title">For Students</h3>
                                <p class="card-text">Take tests, view results instantly, and track your performance over time.</p>
                                </div>
                                </div>
                                </div>
                    <div class="col-md-4">
                        <div class="card h-100 shadow-sm feature-card">
                            <div class="card-body text-center">
                                <h3 class="card-title">For Admins</h3>
                                <p class="card-text">Manage users, monitor system activity, and maintain the platform efficiently.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
