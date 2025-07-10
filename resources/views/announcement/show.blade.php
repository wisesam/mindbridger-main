<?php
    // Pre-loading the code values for performance
    $field_arr=array();
    \vwmldbm\code\get_field_name_all('announcement',$field_arr);
?>

@extends('layouts.root')
@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Announcement Details') }}</div>
                <div class="card-body">  
                        <div class="form-group row">
                            <label for="title" class="col-md-3 col-form-label text-md-right">{{ $field_arr['title'] }}</label>

                            <div class="col-md-9">
                                <span class="form-control">{{ $ann->title }}</span>
                            </div>
                        </div>
                        
                        @if(Auth::check() && Auth::user()->isAdmin())
                        <div class="form-group row">
                            <label for="top_yn" class="col-md-3 col-form-label text-md-right">{{ $field_arr['top_yn'] }}</label>
                
                            <div class="col-md-9">
                                <span class='form-control'>
                                  <?PHP                                  
                                    echo \vwmldbm\code\get_c_yn($ann->top_yn);
                                  ?>
                                </span>                               
                            </div>
                        </div>
                        @endif
                        
                        <div class="form-group row">
                            <label for="body" class="col-md-3 col-form-label text-md-right">{{ $field_arr['body'] }}</label>
                
                            <div class="col-md-9">
                                <div class="border border-1 p-2" style='padding'><?=$ann->body?></div>                             
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
                            <div class="col-md-12 d-flex justify-content-center">                                
                                <button type="button" class="btn btn-success" onClick="window.history.back();">
                                    {{ __('Go Back') }}
                                </button>
                                &nbsp;  &nbsp;
                                <button type="button" class="btn btn-info" onClick="window.location='{{config('app.url','/wlibrary')}}/announcement'">
                                    {{ __('List') }}
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