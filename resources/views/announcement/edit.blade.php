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
            customConfig: '{{config('app.url')}}/lib/ckeditor_4c/config_doc.js'
        });
    });
</script>


<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Edit Announcement') }}</div>
                <div class="card-body">
                    <form method="POST" name='editForm' id='pform' action="{{config('app.url','/wlibrary')."/announcement/".$ann->id}}" enctype="multipart/form-data">
                        @csrf 
                        <input type='hidden' name='_method' value='patch'> 

                        <div class="form-group row">
                            <label for="title" class="col-md-3 col-form-label text-md-right">{{ $field_arr['title'] }}</label>

                            <div class="col-md-9">
                                <input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ $ann->title }}" required autocomplete="on">

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
                                    echo \vwmldbm\code\print_c_yn('top_yn',$ann->top_yn,null,'RADIO',"");
                                  ?>
                                </span>
                                @error('top_yn')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                
                        <div class="form-group row">
                            <label for="body" class="col-md-3 col-form-label text-md-right">{{ $field_arr['body'] }}</label>
                
                            <div class="col-md-9">
                                <textarea id="body"  class="form-control @error('body') is-invalid @enderror" name="body" autocomplete="off">
                                    {{ $ann->body }}
                                </textarea>
                                @error('body')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>                    
                        
                        <div class="form-group row">
                            <label for="create_id" class="col-md-3 col-form-label text-md-right">{{ $field_arr['create_id'] }}</label>

                            <div class="col-md-9">
                                <span class="form-control border-0">
                                    {{$ann->create_id}} ({{$ann->ctime}})
                                </span>                               
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="mod_id" class="col-md-3 col-form-label text-md-right">{{ $field_arr['mod_id'] }}</label>

                            <div class="col-md-9">
                                <span class="form-control border-0">
                                    @if($ann->mod_id)
                                    {{$ann->mod_id}} ({{$ann->mtime}})
                                    @endif
                                </span>                               
                            </div>
                        </div>

                        <div class="row justify-content-center">
                            <div class="col-md-3"></div>
                            <div class="col-md-9 d-flex justify-content-center">  
                                <button type="submit" onClick="check_img_submit()" class="btn btn-primary">
                                    {{ __('Submit') }}
                                </button>
                                &nbsp; 
                                <button type="button" class="btn btn-success" onClick="window.location='{{config('app.url','/wlibrary')}}/announcement'">
                                    {{ __('Go Back') }}
                                </button>
                            </div>
                            <input type='hidden' name="operation">
                        </div>
                    </form>
                </div>
            </div><!--card-->
        </div>
    </div> <!--row-->
</div>
@endsection