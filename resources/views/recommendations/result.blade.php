@extends('layouts.root')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">{{ __('AI Book Recommendations') }}</h2>

    @if(isset($recommendedBooks) && is_array($recommendedBooks) && count($recommendedBooks))
        <div class="row">
        @foreach($recommendedBooks as $book)
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">{{ $book['title'] }}</h5>
                    <h6 class="card-subtitle text-muted">저자: {{ $book['author'] }}</h6>
                    <p class="card-text">선정이유: {{ $book['reason'] }}</p>
                    <p class="card-text">링크(다운로드/ebook신청 하기): Click</p>
                </div>
            </div>
        @endforeach

        </div>

        @if(isset($response))
        <pre>JSON: {{ json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    @endif
    @else
        <p>{{ __('No recommendations found.') }}</p>
    @endif


    <a href="{{ route('recommend.form') }}" class="btn btn-primary mt-3">{{ __('Back to Form') }}</a>
</div>
@endsection
