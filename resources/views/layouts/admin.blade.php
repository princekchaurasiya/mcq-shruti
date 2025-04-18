<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MCQ Management') }} - Admin Panel</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom CSS -->
    <style>
        #sidebar-wrapper {
            min-height: 100vh;
            margin-left: -15rem;
            transition: margin .25s ease-out;
        }
        
        #sidebar-wrapper .sidebar-heading {
            padding: 0.875rem 1.25rem;
            font-size: 1.2rem;
        }
        
        #sidebar-wrapper .list-group {
            width: 15rem;
        }
        
        #wrapper.toggled #sidebar-wrapper {
            margin-left: 0;
        }
        
        @media (min-width: 768px) {
            #sidebar-wrapper {
                margin-left: 0;
            }
        
            #page-content-wrapper {
                min-width: 0;
                width: 100%;
            }
        
            #wrapper.toggled #sidebar-wrapper {
                margin-left: -15rem;
            }
        }

        .sidebar-link {
            padding: 0.5rem 1rem;
            color: #333;
            text-decoration: none;
            transition: all 0.3s;
        }

        .sidebar-link:hover {
            background-color: #f8f9fa;
            color: #0d6efd;
        }

        .sidebar-link.active {
            background-color: #0d6efd;
            color: white;
        }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-light border-end" id="sidebar-wrapper">
            <div class="sidebar-heading border-bottom bg-primary text-white">Admin Panel</div>
            <div class="list-group list-group-flush">
                <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
                <a href="{{ route('teachers.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('teachers.*') ? 'active' : '' }}">
                    <i class="bi bi-person-workspace me-2"></i> Teachers
                </a>
                <a href="{{ route('subjects.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('subjects.*') ? 'active' : '' }}">
                    <i class="bi bi-book me-2"></i> Subjects
                </a>
                <a href="{{ route('students.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('students.*') ? 'active' : '' }}">
                    <i class="bi bi-mortarboard me-2"></i> Students
                </a>
                <a href="{{ route('profile.edit') }}" class="list-group-item list-group-item-action {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <i class="bi bi-person-circle me-2"></i> Profile
                </a>
                <div class="list-group-item list-group-item-action">
                    <livewire:pages.auth.logout />
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <div class="container-fluid">
                    <button class="btn btn-primary" id="sidebarToggle">Toggle Menu</button>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                            <li class="nav-item">
                                <span class="nav-link">Welcome, {{ Auth::user()->name }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="container-fluid p-4">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                {{ $slot }}
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        window.addEventListener('DOMContentLoaded', event => {
            // Toggle the side navigation
            const sidebarToggle = document.body.querySelector('#sidebarToggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', event => {
                    event.preventDefault();
                    document.body.querySelector('#wrapper').classList.toggle('toggled');
                });
            }
        });
    </script>
</body>
</html> 