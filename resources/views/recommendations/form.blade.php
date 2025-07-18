@extends('layouts.root')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <form method="POST" action="{{ route('recommend') }}">
                @csrf
                <div class="card">
                    <div class="card-header">{{ __("Get Book Recommendations") }}</div>
                    <div class="card-body">
                        
                        <div class="form-group mb-3">
                            <label for="age">{{ __("Age") }}</label>
                            <input type="number" class="form-control" name="age" id="age">
                        </div>

                        <div class="form-group mb-3">
                            <label for="grade">{{ __("Grade") }}</label>
                            <input type="text" class="form-control" name="grade" id="grade">
                        </div>

                        <div class="form-group mb-3">
                            <label for="interest">{{ __("Interest Area (e.g., science, fantasy)") }}</label>
                            <input type="text" class="form-control" name="interest" id="interest" required>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" name="free" id="free">
                            <label class="form-check-label" for="free">{{ __("Free E-books Only") }}</label>
                        </div>

                        <button type="submit" class="btn btn-primary">{{ __("Get Recommendations") }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
