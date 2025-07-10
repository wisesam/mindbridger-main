<?php
    // Pre-loading the code values for performance
    $field_arr=array();
    \vwmldbm\code\get_field_name_all('users',$field_arr);
?>

@extends('layouts.root')
@section('content')
    <h1>{{__("Edit Profile")}}</h1>
    {!! Form::open(['route' => ['users.update', $user->id], 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
        {{ csrf_field() }}
        {{ method_field('patch') }}

        <div class="form-group">
            <?PHP echo Form::label('uid',$field_arr["id"]); ?>
            <span style='display:block;font-weight:bold;margin-left:5px;'>{{$user->id}}</span>
        </div>

        <div class="form-group">
            <?PHP echo Form::label('name',$field_arr["name"]); ?>
            {{Form::text('name',$user->name,['class' => 'form-control','placeholder'=>'Name'])}}
        </div>

        <div class="form-group">
            {{Form::label('email',$field_arr["email"])}}
            {{Form::text('email',$user->email,['class' => 'form-control','placeholder'=>'Email'])}}
        </div>
                
        <div class="form-group">
            {{Form::label('utype',$field_arr["utype"])}}
            <span style='display:block;font-weight:bold;margin-left:5px;'>
              <?PHP
                if(Auth::user()->isAdmin() && $user->id !=Auth::user()->id) 
                    echo Auth::user()::print_utype('utype',$user->utype,'required');
                else {
                    echo Auth::user()::get_utype($user->utype);
                }
              ?>
            </span>
        </div>

        <div class="form-group">
            {{Form::label('code_c_utype',$field_arr["code_c_utype"])}}  
            <?PHP                
                if(Auth::user()->isAdmin() && $user->id !=Auth::user()->id) $OPT=null;
                else $OPT="RD_ONLY";
            ?>          
            <span style='display:block;font-weight:bold;margin-left:5px;'>
                <?=\vwmldbm\code\print_code('code_c_utype',$user->code_c_utype,null,null,null,null,$OPT,null,"class='browser-default custom-select'");?>
            </span>
        </div>

        <div class="form-group">
            {{Form::label('ustatus',$field_arr["ustatus"])}}            
            <span style='display:block;font-weight:bold;margin-left:5px;'>
                <?PHP
                if(Auth::user()->isAdmin() && $user->id !=Auth::user()->id) 
                    echo Auth::user()::print_ustatus('ustatus',$user->ustatus);
                else {
                    echo Auth::user()::get_ustatus($user->ustatus);
                }
                ?>    
            </span>
        </div>
    @if(trim($theInst->mode) == 'INDEPENDENT')
        <div class="form-group">
            {{Form::label('password',$field_arr["password"])}} <br>
            {{Form::password('password',null,['class' => 'form-control'])}}
        </div>

        <div class="form-group">
            {{Form::label('password_confirmation',__('confirm_pwd'))}} <br>
            {{Form::password('password_confirmation',null,['class' => 'form-control'])}}
        </div>
    @endif
        {{Form::hidden('_method','PATCH')}} <? // according to route:list, it should be PUT|PATCH so spoof?>
        {{Form::submit(__("Submit"),['class'=>'btn btn-primary'])}}

        <?PHP  $list_uri=config('app.url','/wlibrary')."/users/list"; ?>
        @if(Auth::user()->isAdmin() && Auth::user()->id!=$user->id)
         {{Form::button(__("List"),['class'=>'btn btn-info','onClick'=>"window.location='$list_uri';"])}}
        @endif
    {!! Form::close() !!}
    <br>
@endsection