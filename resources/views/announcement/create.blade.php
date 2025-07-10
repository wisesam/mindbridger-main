<?php
    // Pre-loading the code values for performance
    $field_arr=array();
    \vwmldbm\code\get_field_name_all('announcement',$field_arr);
?>

@extends('layouts.root')
@section('content')
<script src="{{ auto_asset('/lib/jquery/jquery.form.min.js') }}"></script>
<script src="{{ auto_asset('/lib/ckeditor_4c/ckeditor.js') }}"></script>
<script>
    $(document).ready(function() {
        CKEDITOR.replace( 'body',{
            customConfig: '{{config('app.url')}}/lib/ckeditor_4c/config_gen.js'
        });
    });
</script>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Register Announcement') }}</div>

                <div class="card-body">
                    <form method="POST" name='form1' action="{{auto_url(route('announcement.store', [], false))}}" enctype="multipart/form-data">
                        @csrf
                         
                        <div class="form-group row">
                            <label for="title" class="col-md-3 col-form-label text-md-right">{{ $field_arr['title'] }}</label>

                            <div class="col-md-9">
                                <input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title"  required  autofocus>

                                @error('title')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>                      

                        <div class="form-group row">
                            <label for="top_yn" class="col-md-3 col-form-label text-md-right">{{ $field_arr['top_yn'] }}</label>

                            <div class="col-md-9">
                                <span class='form-control border-0'>
                                  <?PHP                                                                   
                                    echo \vwmldbm\code\print_c_yn('top_yn','N',null,'RADIO');
                                  ?>
                                </span>
                                @error('e_resource_yn')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                               
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="body" class="col-md-3 col-form-label text-md-right">{{ $field_arr['body'] }}</label>

                            <div class="col-md-9">
                                <textarea id="body" type="text" class="form-control @error('body') is-invalid @enderror" name="body">
                                    
                                </textarea>
                                @error('body')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        
                        
                        <div class="form-group row mb-0">
                            <div class="col-md-3"></div>
                            <div class="col-md-9 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Register') }}
                                </button>
                                &nbsp; 
                                <button type="button" class="btn btn-success" onClick="window.location='{{config('app.url','/wlibrary')}}/announcement'">
                                    {{ __('List') }}
                                </button>
                            </div>
                            <input type=hidden name="operation">
                            <input name='file_name' type='hidden'/>
                            <input name='wise_photo_data' id='wise_photo_data' type='hidden'/>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
