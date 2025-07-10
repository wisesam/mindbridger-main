@extends('layouts.root')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class='d-inline'>{{__("About")}}</div>
                    @if(Auth::check() && Auth::user()->isAdmin()) 
                        <div class='d-inline'>                            
                            <a href="{{config('app.url','/wlibrary')}}/about/edit"><span class="oi" data-glyph="cog"></span></a>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                   <?=stripslashes($about->about_txt)?>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection