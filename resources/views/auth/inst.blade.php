@extends('layouts.root')

<?PHP
 if(!config('app.multi_inst','')) {  // illegal access
    die;
}
?>

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Enter Institution') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ auto_url('inst_process') }}">
                        @csrf
                        <div class="form-group row">
                            <label for="institution" class="col-md-4 col-form-label text-md-right">{{ __('Institution') }}</label>

                            <div class="col-md-6">
                                <input id="institution" type="institution" class="form-control @error('institution') is-invalid @enderror" name="institution" value="{{ old('institution') }}" required autocomplete="on" autofocus>
                                @error('institution')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Enter') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
