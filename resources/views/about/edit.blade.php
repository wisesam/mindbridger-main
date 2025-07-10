<?php
    // Pre-loading the code values for performance
    $field_arr=array();
    \vwmldbm\code\get_field_name_all('about',$field_arr);

?>

@extends('layouts.root')
@section('content')
<script src="{{ auto_asset('/lib/jquery/jquery.form.min.js') }}"></script>
<script src="{{ auto_asset('/lib/ckeditor_4c/ckeditor.js') }}"></script>
<script>
    $(document).ready(function() {
        CKEDITOR.replace( 'about_txt',{
            customConfig: '{{config('app.url')}}/lib/ckeditor_4c/config_doc.js'
        });

        CKEDITOR.replace( 'header',{
            customConfig: '{{config('app.url')}}/lib/ckeditor_4c/config_doc.js'
        });

        CKEDITOR.replace( 'footer',{
            customConfig: '{{config('app.url')}}/lib/ckeditor_4c/config_doc.js'
        });
    });
</script>
<form method="POST" name='editForm' id='pform' action="{{config('app.url','/wlibrary')."/about/update"}}">
<div class="container">
    <div class="row justify-content-center">
        @csrf 
        <input type='hidden' name='_method' value='patch'>                   

        <div class="form-group row">
            <label for="about_txt" class="col-md-3 col-form-label text-md-right">{{ $field_arr['about_txt'] }}</label>

            <div class="col-md-9">
                <textarea id="about_txt"  class="form-control @error('about_txt') is-invalid @enderror" name="about_txt" autocomplete="off" autofocus>
                    {{ $about->about_txt }}
                </textarea>
                @error('about_txt')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        
        <div class="form-group row">
            <label for="header" class="col-md-3 col-form-label text-md-right">{{ $field_arr['header'] }}</label>
    
            <div class="col-md-9">
                <textarea id="header"  class="form-control @error('header') is-invalid @enderror" name="header" autocomplete="off" autofocus>
                    {{ $about->header }}
                </textarea>
                @error('header')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="form-group row">
            <label for="footer" class="col-md-3 col-form-label text-md-right">{{ $field_arr['footer'] }}</label>
    
            <div class="col-md-9">
                <textarea id="footer"  class="form-control @error('footer') is-invalid @enderror" name="footer" autocomplete="off" autofocus>
                    {{ $about->footer }}
                </textarea>
                @error('footer')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-3"></div>
        <div class="col-md-9 d-flex justify-content-center">  
            <button type="submit" onClick="check_img_submit()" class="btn btn-primary">
                {{ __('Submit') }}
            </button>
            &nbsp; 
            <button type="button" class="btn btn-success" onClick="window.location='{{config('app.url','/wlibrary')}}/about'">
                {{ __('Go Back') }}
            </button>
        </div>
        <input type='hidden' name="operation">
    </div>
</div>
</form>
@endsection