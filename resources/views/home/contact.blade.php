@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <h1 class="text-center mb-4">{{ $title }}</h1>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="/contact">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ @old('name') }}" required>
                            @error('name')
                                <div class="text-danger">{{ $error }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ @old('email') }}" required>
                            @error('email')
                                <div class="text-danger">{{ $error }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required>{{ @old('message') }}</textarea>
                            @error('message')
                                <div class="text-danger">{{ $error }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 