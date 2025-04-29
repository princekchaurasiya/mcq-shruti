@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="fas fa-exclamation-circle text-warning" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h1 class="display-6 mb-3">{{ $errorTitle ?? 'Oops! Something went wrong' }}</h1>
                    
                    <p class="lead text-muted mb-4">
                        {{ $errorMessage ?? 'We encountered an unexpected error while processing your request. Our team has been notified.' }}
                    </p>
                    
                    <div class="mb-4">
                        <hr>
                        <p class="small text-muted mb-0">
                            If this problem persists, please contact support.
                        </p>
                    </div>
                    
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Go Back
                        </a>
                        <a href="{{ url('/') }}" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i> Go to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 