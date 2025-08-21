@extends('layouts.root')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">{{ __('AI Book Recommendations') }}</h2>

    @if(isset($recommendedBooks) && is_array($recommendedBooks) && count($recommendedBooks))
        <div class="d-flex flex-column">
            @foreach($recommendedBooks as $book)
                <div class="card mb-3">
                    <div class="card-body">
                        {{-- Title clickable (always new tab) --}}
                        @if(!empty($book['link']))
                            <h5 class="card-title">
                                <a href="{{ $book['link'] }}" target="_blank" rel="noopener">
                                    {{ $book['title'] }}
                                </a>
                            </h5>
                        @else
                            <h5 class="card-title">{{ $book['title'] }}</h5>
                        @endif

                        {{-- Author --}}
                        <h6 class="card-subtitle text-muted mb-2">
                            {{ __('Author') }}: {{ $book['author'] }}
                        </h6>

                        {{-- Reason --}}
                        @if(!empty($book['reason']))
                            <p class="card-text">{{ __("Reason") }}: {{ $book['reason'] }}</p>
                        @endif

                        {{-- Source badge --}}
                        @if(isset($book['source']) && $book['source']==='local')
                            <span class="badge badge-success">{{ __("Our Library") }}</span>
                        @elseif(isset($book['source']) && $book['source']==='ai')
                            <span class="badge badge-primary">{{ __("From the Web") }}</span>
                        @endif
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
