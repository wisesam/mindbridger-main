@extends('layouts.root')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">{{ __('AI Book Recommendations') }}</h2>

    @if(isset($response))
        <pre>JSON: {{ json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    @endif


    @if(isset($recommendedBooks) && is_array($recommendedBooks) && count($recommendedBooks))
        <div class="row">
        @foreach($recommendedBooks as $book)
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Title: {{ $book['title'] }}</h5>
                    <h6 class="card-subtitle text-muted">Author: {{ $book['author'] }}</h6>
                    <p class="card-text">Reason: {{ $book['reason'] }}</p>
                </div>
            </div>
        @endforeach

        </div>
    @else
        <p>{{ __('No recommendations found.') }}</p>
    @endif


    <a href="{{ route('recommend.form') }}" class="btn btn-primary mt-3">{{ __('Back to Form') }}</a>
</div>
@endsection
