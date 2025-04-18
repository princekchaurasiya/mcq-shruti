<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MCQ Management') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background-color: #f8f9fa;
        }
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .role-card {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .role-card:hover {
            transform: translateY(-5px);
        }
        .role-card.selected {
            border-color: #0d6efd;
            background-color: #f8f9ff;
        }
        .role-card.selected .role-icon {
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">Register for {{ config('app.name', 'MCQ Management') }}</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('register') }}" id="registerForm">
                                @csrf

                                <!-- Role Selection -->
                                <div class="mb-4">
                                    <label class="form-label">Select Your Role</label>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="card role-card" onclick="selectRole('student')">
                                                <div class="card-body text-center">
                                                    <i class="bi bi-mortarboard role-icon display-4"></i>
                                                    <h5 class="mt-3">Student</h5>
                                                    <p class="text-muted small">Take tests and track your progress</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card role-card" onclick="selectRole('teacher')">
                                                <div class="card-body text-center">
                                                    <i class="bi bi-person-workspace role-icon display-4"></i>
                                                    <h5 class="mt-3">Teacher</h5>
                                                    <p class="text-muted small">Create and manage tests</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="role" id="selectedRole" value="student">
                                    @error('role')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Name -->
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required autofocus>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Password -->
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Confirm Password -->
                                <div class="mb-4">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" 
                                           id="password_confirmation" name="password_confirmation" required>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="{{ route('login') }}" class="text-decoration-none">
                                        Already registered?
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        Register
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function selectRole(role) {
            document.getElementById('selectedRole').value = role;
            document.querySelectorAll('.role-card').forEach(card => {
                card.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');
        }

        // Select default role on page load
        document.addEventListener('DOMContentLoaded', function() {
            selectRole('student');
        });
    </script>
</body>
</html> 