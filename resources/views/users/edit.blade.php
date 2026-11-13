<?php
    // Pre-loading the code values for performance
    $field_arr=array();
    \vwmldbm\code\get_field_name_all('users',$field_arr);
?>

@extends('layouts.root')
@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-gradient-primary text-white text-center py-3">
                    <h3 class="mb-0">
                        <i class="fas fa-user-edit mr-2"></i>{{__("Edit Profile")}}
                    </h3>
                </div>
                
                <div class="card-body p-4">
                    {!! Form::open(['route' => ['users.update', $user->id], 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
                        {{ csrf_field() }}
                        {{ method_field('patch') }}

                        <!-- User ID (Read-only) -->
                        <div class="form-group row mb-3">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold text-muted">
                                <i class="fas fa-id-card mr-2"></i><?PHP echo $field_arr["id"]; ?>
                            </label>
                            <div class="col-md-9">
                                <div class="form-control-plaintext bg-light border rounded p-2">
                                    <i class="fas fa-user mr-2 text-primary"></i>{{$user->id}}
                                </div>
                            </div>
                        </div>

                        <!-- Name -->
                        <div class="form-group row mb-3">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">
                                <i class="fas fa-user mr-2 text-primary"></i><?PHP echo $field_arr["name"]; ?>
                            </label>
                            <div class="col-md-9">
                                <div class="w-100">
                                    {{ Form::text('name', old('name', $user->name), [
                                        'class' => 'form-control form-control-lg' . ($errors->has('name') ? ' is-invalid' : ''),
                                        'placeholder' => 'Enter your name',
                                        'autocomplete' => 'name'
                                    ]) }}
                                    @error('name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="form-group row mb-3">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">
                                <i class="fas fa-envelope mr-2 text-info"></i>{{$field_arr["email"]}}
                            </label>
                            <div class="col-md-9">
                                <div class="w-100">
                                    {{ Form::email('email', old('email', $user->email), [
                                        'class' => 'form-control form-control-lg' . ($errors->has('email') ? ' is-invalid' : ''),
                                        'placeholder' => 'Enter your email',
                                        'autocomplete' => 'email'
                                    ]) }}
                                    @error('email')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                                
                        <!-- User Type -->
                        <div class="form-group row mb-3">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">
                                <i class="fas fa-users-cog mr-2 text-warning"></i><?PHP echo $field_arr["utype"]; ?>
                            </label>
                            <div class="col-md-9">
                                <div class="form-control-plaintext bg-light border rounded p-2">
                                    <?PHP
                                        if(Auth::user()->isAdmin() && $user->id !=Auth::user()->id) 
                                            echo Auth::user()::print_utype('utype',$user->utype,'required');
                                        else {
                                            echo Auth::user()::get_utype($user->utype);
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <!-- Code User Type -->
                        <div class="form-group row mb-3">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">
                                <i class="fas fa-code mr-2 text-success"></i><?PHP echo $field_arr["code_c_utype"]; ?>
                            </label>
                            <div class="col-md-9">
                                <div class="form-control-plaintext bg-light border rounded p-2">
                                    <?PHP                
                                        if(Auth::user()->isAdmin() && $user->id !=Auth::user()->id) $OPT=null;
                                        else $OPT="RD_ONLY";
                                    ?>          
                                    <?=\vwmldbm\code\print_code('code_c_utype',$user->code_c_utype,null,null,null,null,$OPT,null,"class='form-control form-control-lg'");?>
                                </div>
                            </div>
                        </div>

                        <!-- User Status -->
                        <div class="form-group row mb-3">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">
                                <i class="fas fa-toggle-on mr-2 text-danger"></i><?PHP echo $field_arr["ustatus"]; ?>
                            </label>
                            <div class="col-md-9">
                                <div class="form-control-plaintext bg-light border rounded p-2">
                                    <?PHP
                                    if(Auth::user()->isAdmin() && $user->id !=Auth::user()->id) 
                                        echo Auth::user()::print_ustatus('ustatus',$user->ustatus);
                                    else {
                                        echo Auth::user()::get_ustatus($user->ustatus);
                                    }
                                    ?>    
                                </div>
                            </div>
                        </div>

                        <!-- Password change / administrator password reset -->
                        @if(Auth::user()->id == $user->id)
                            <div class="form-group row mb-3">
                                <label for="current_password" class="col-md-3 col-form-label text-md-right font-weight-bold">
                                    <i class="fas fa-key mr-2 text-warning"></i>{{ __('Current Password') }}
                                </label>
                                <div class="col-md-9 d-block">
                                    {{ Form::password('current_password', [
                                        'id' => 'current_password',
                                        'class' => 'form-control form-control-lg' . ($errors->has('current_password') ? ' is-invalid' : ''),
                                        'placeholder' => 'Required only when changing password',
                                        'autocomplete' => 'current-password'
                                    ]) }}
                                    @error('current_password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endif

                        <!-- New Password -->
                        <div class="form-group row mb-3">
                            <label for="password" class="col-md-3 col-form-label text-md-right font-weight-bold">
                                <i class="fas fa-lock mr-2 text-warning"></i>{{ __('New Password') }}
                            </label>
                            <div class="col-md-9 d-block">
                                {{ Form::password('password', [
                                    'id' => 'password',
                                    'class' => 'form-control form-control-lg' . ($errors->has('password') ? ' is-invalid' : ''),
                                    'placeholder' => 'At least 8 characters (leave blank to keep current)',
                                    'autocomplete' => 'new-password',
                                    'minlength' => 8
                                ]) }}
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Confirm New Password -->
                        <div class="form-group row mb-3">
                            <label for="password_confirmation" class="col-md-3 col-form-label text-md-right font-weight-bold">
                                <i class="fas fa-lock mr-2 text-warning"></i>{{ __('Confirm New Password') }}
                            </label>
                            <div class="col-md-9 d-block">
                                {{ Form::password('password_confirmation', [
                                    'id' => 'password_confirmation',
                                    'class' => 'form-control form-control-lg' . ($errors->has('password_confirmation') ? ' is-invalid' : ''),
                                    'placeholder' => 'Re-enter new password',
                                    'autocomplete' => 'new-password',
                                    'minlength' => 8
                                ]) }}
                                @error('password_confirmation')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{Form::hidden('_method','PATCH')}}

                        <!-- Action Buttons -->
                        <div class="form-group row mb-0">
                            <div class="col-md-9 offset-md-3">
                                <div class="d-flex gap-2">
                                    {{Form::submit(__("Edit"),['class'=>'btn btn-primary btn-lg px-4'])}}
                                    
                                    <?PHP  $list_uri=config('app.url','/wlibrary')."/users/list"; ?>
                                    @if(Auth::user()->isAdmin() && Auth::user()->id!=$user->id)
                                        {{Form::button(__("List"),['class'=>'btn btn-info btn-lg px-4','onClick'=>"window.location='$list_uri';"])}}
                                    @endif
                                </div>
                            </div>
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #007bff, #0056b3);
    }
    
    .card {
        border-radius: 15px;
        overflow: hidden;
    }
    
    /* Scope CSS to only this form */
    .card-body .form-control {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .card-body .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
        transform: translateY(-1px);
    }
    
    .card-body .form-control-lg {
        padding: 0.75rem 1rem;
        font-size: 1rem;
    }
    
    .card-body .btn {
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .card-body .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .card-body .btn-lg {
        padding: 0.75rem 1.5rem;
    }
    
    .gap-2 {
        gap: 0.5rem;
    }
    
    .card-body .form-control-plaintext {
        background-color: #f8f9fa !important;
        border: 1px solid #dee2e6 !important;
        min-height: 48px;
        display: flex;
        align-items: center;
    }
    
    .card-header h3 {
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    
    .card-body .col-form-label {
        color: #495057;
        font-size: 0.95rem;
    }

    /* Minimum row height; grow naturally to display validation messages. */
    .card-body .form-group.row {
        min-height: 60px;
        align-items: center;
    }

    .card-body .form-group.row .invalid-feedback {
        height: auto;
    }

    .card-body .form-control, 
    .card-body .form-control-plaintext {
        height: 48px;
        line-height: 1.5;
    }

    /* Ensure consistent spacing */
    .card-body .form-group.row .col-md-3,
    .card-body .form-group.row .col-md-9 {
        display: flex;
        align-items: center;
    }

    .card-body .form-group.row .col-md-3 {
        justify-content: flex-end;
    }

    .card-body .form-group.row .col-md-9 {
        justify-content: flex-start;
    }
</style>
@endsection