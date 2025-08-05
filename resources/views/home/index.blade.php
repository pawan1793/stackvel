@extends('layouts.app')

@section('title', $title)

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="container text-center">
        <h1 class="display-4 mb-4">{{ $title }}</h1>
        <p class="lead mb-5">{{ $description }}</p>
        <div class="row">
            <div class="col-md-6 mx-auto">
                <a href="/users" class="btn btn-light btn-lg me-3">View Users</a>
                <a href="/contact" class="btn btn-outline-light btn-lg">Contact Us</a>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Framework Features</h2>
        <div class="row">
            @foreach($features as $feature)
            <div class="col-md-4 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-check-circle text-success" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title">{{ $feature }}</h5>
                        <p class="card-text text-muted">
                            Experience the power of {{ $feature }} with our lightweight framework.
                        </p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Quick Start Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-8 mx-auto text-center">
                <h2>Quick Start</h2>
                <p class="lead mb-4">Get started with Stackvel Framework in minutes</p>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">1. Install</h5>
                                <p class="card-text">Clone the repository and install dependencies</p>
                                <code>composer install</code>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">2. Configure</h5>
                                <p class="card-text">Set up your environment variables</p>
                                <code>cp env.example .env</code>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">3. Run</h5>
                                <p class="card-text">Start the development server</p>
                                <code>php console.php serve</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 mb-3">
                <div class="card border-0">
                    <div class="card-body">
                        <h3 class="text-primary">Lightweight</h3>
                        <p class="text-muted">Minimal overhead, maximum performance</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0">
                    <div class="card-body">
                        <h3 class="text-success">Secure</h3>
                        <p class="text-muted">Built with security best practices</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0">
                    <div class="card-body">
                        <h3 class="text-info">Fast</h3>
                        <p class="text-muted">Optimized for speed and efficiency</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0">
                    <div class="card-body">
                        <h3 class="text-warning">Flexible</h3>
                        <p class="text-muted">Easy to extend and customize</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    // Add any page-specific JavaScript here
    console.log('Welcome to Stackvel Framework!');
</script>
@endsection 