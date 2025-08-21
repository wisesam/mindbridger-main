@extends('layouts.root')
@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="recommendation-header text-center mb-4">
                <div class="ai-icon mb-2">
                    <img src="image/ai.png?nocache=4" alt="AI" width="50" height="50">
                </div>
                <h2 class="h3 fw-bold text-dark mb-1">{{ __("Get Book Recommendations") }}</h2>
                <p class="text-secondary mb-0">{{ __("Let AI help you discover your next favorite book") }}</p>
            </div>

            <form method="POST" action="{{ route('recommend') }}" class="recommendation-form">
                @csrf
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-gradient-primary text-white text-center py-4">
                        <h3 class="mb-0">
                            <i class="fas fa-magic me-2"></i>
                            {{ __("AI Book Advisor") }}
                        </h3>
                    </div>
                    <div class="card-body p-5">
                        
                        <div class="form-group mb-4">
                            <label for="age" class="form-label fw-bold text-dark">
                                <i class="fas fa-birthday-cake me-2 text-primary"></i>
                                {{ __("Age") }}
                            </label>
                            <input type="number" class="form-control form-control-lg" name="age" id="age" 
                                   placeholder="Enter your age" min="1" max="120" required>
                            <div class="form-text text-muted">
                                <small>{{ __("This helps us recommend age-appropriate books") }}</small>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="grade" class="form-label fw-bold text-dark">
                                <i class="fas fa-graduation-cap me-2 text-primary"></i>
                                {{ __("Grade") }}
                            </label>
                            <input type="text" class="form-control form-control-lg" name="grade" id="grade" 
                                   placeholder="e.g., 3rd grade, high school, college">
                            <div class="form-text text-muted">
                                <small>{{ __("Your current or preferred grade level") }}</small>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="interest" class="form-label fw-bold text-dark">
                                <i class="fas fa-heart me-2 text-primary"></i>
                                {{ __("Interest Area") }}
                            </label>
                            <input type="text" class="form-control form-control-lg" name="interest" id="interest" 
                                   placeholder="e.g., science, fantasy, history, mystery" required>
                            <div class="form-text text-muted">
                                <small>{{ __("What topics or genres interest you most?") }}</small>
                            </div>
                        </div>

                        <div class="form-check mb-4 p-3 bg-light rounded">
                            <input type="hidden" name="free" value="0">
                            <input type="checkbox" class="form-check-input" name="free" id="free" value="1">
                            <label class="form-check-label fw-bold text-dark" for="free">
                                <i class="fas fa-gift me-2 text-success"></i>
                                {{ __("Free E-books Only") }}
                            </label>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5 py-3 fw-bold">
                                <i class="fas fa-magic me-2"></i>
                                {{ __("Get Recommendations") }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .recommendation-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 2rem 1.5rem;
        border-radius: 16px;
        color: #2c3e50;
        margin-bottom: 2rem;
        border: 2px solid #dee2e6;
    }
    
    .ai-icon {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border-radius: 50%;
        width: 70px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
    }
    
    .ai-icon img {
        filter: brightness(0) invert(1);
    }
    
    .bg-gradient-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    }
    
    .card {
        border-radius: 20px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }
    
    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        transform: translateY(-2px);
    }
    
    .form-control-lg {
        padding: 1rem 1.25rem;
        font-size: 1.1rem;
    }
    
    .form-label {
        font-size: 1.1rem;
        margin-bottom: 0.75rem;
        color: #2c3e50;
    }
    
    .form-text {
        font-size: 0.9rem;
        margin-top: 0.5rem;
    }
    
    .form-check {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        transition: all 0.3s ease;
        padding: 1.5rem !important;
        margin: 0;
    }
    
    .form-check:hover {
        border-color: #007bff;
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    .form-check-input {
        margin-top: 0.25rem;
    }
    
    .form-check-label {
        margin-left: 0.5rem;
        line-height: 1.4;
    }
    
    .form-check-input:checked {
        background-color: #007bff;
        border-color: #007bff;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border: none;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 123, 255, 0.4);
    }
    
    .btn-lg {
        padding: 1rem 2.5rem;
        font-size: 1.1rem;
    }
    
    .text-primary {
        color: #007bff !important;
    }
    
    .text-success {
        color: #28a745 !important;
    }
    
    .bg-light {
        background-color: #f8f9fa !important;
    }
    
    @media (max-width: 768px) {
        .recommendation-header {
            padding: 1.5rem 1rem;
            margin-bottom: 1.5rem;
        }
        
        .ai-icon {
            width: 60px;
            height: 60px;
        }
        
        .ai-icon img {
            width: 35px;
            height: 35px;
        }
        
        .card-body {
            padding: 2rem 1.5rem;
        }
        
        .h3 {
            font-size: 1.5rem;
        }
    }
</style>
@endsection
