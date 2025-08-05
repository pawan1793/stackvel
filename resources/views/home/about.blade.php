@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <h1 class="text-center mb-4">{{ $title }}</h1>
            <p class="lead text-center mb-5">{{ $description }}</p>
            
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Framework Version</h5>
                    <p class="card-text">Current version: <strong>{{ $version }}</strong></p>
                    
                    <h5 class="card-title mt-4">About Stackvel Framework</h5>
                    <p class="card-text">
                        Stackvel Framework is a lightweight, modern PHP framework designed for developers who want 
                        maximum control over their applications while maintaining clean, readable code.
                    </p>
                    
                    <h5 class="card-title mt-4">Key Features</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>Lightweight and fast</li>
                        <li><i class="fas fa-check text-success me-2"></i>Secure by default</li>
                        <li><i class="fas fa-check text-success me-2"></i>Eloquent-style ORM</li>
                        <li><i class="fas fa-check text-success me-2"></i>Blade templating</li>
                        <li><i class="fas fa-check text-success me-2"></i>Email support</li>
                        <li><i class="fas fa-check text-success me-2"></i>Easy to extend</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 