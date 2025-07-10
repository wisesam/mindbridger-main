<?php
    // Pre-loading the code values for performance
    $field_arr=array();
    \vwmldbm\code\get_field_name_all('book',$field_arr);

    $field_arr2=array();
    \vwmldbm\code\get_field_name_all('book_copy',$field_arr2);

    $barcode = session()->get( 'barcode' ); // it should be like this cuz from Book_copyController
    $call_no = session()->get( 'call_no' );
    $location = session()->get( 'location' );
    $c_rstatus = session()->get( 'c_rstatus' );
    $comment = session()->get( 'comment' );

?>

@extends('layouts.root')
@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">{{ __('Register Book') }}</div>

                <div class="card-body">
                    <form method="POST" name='form1' action="{{route('book_copy.store')}}">
                        @csrf
                        <input type='hidden' name='bid' value="{{$book->id}}">
                        <div class="form-group row">
                            <label for="book_name" class="col-md-3 col-form-label text-md-right">{{ $field_arr['title'] }}</label>

                            <div class="col-md-7">
                                <span class='form-control' style='border:solid black 0px;'>{{ $book->title }}</span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="barcode" class="col-md-3 col-form-label text-md-right">{{ $field_arr2['barcode'] }}</label>

                            <div class="col-md-7">
                                <input id="barcode" type="text" class="form-control @error('barcode') is-invalid @enderror" name="barcode" value="{{ $barcode }}" autofocus>

                                @error('barcode')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="call_no" class="col-md-3 col-form-label text-md-right">{{ $field_arr2['call_no'] }}</label>

                            <div class="col-md-7">
                                <input id="call_no" type="text" class="form-control @error('call_no') is-invalid @enderror" name="call_no" required value="{{ $call_no }}"  autofocus>

                                @error('call_no')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="location" class="col-md-3 col-form-label text-md-right">{{ $field_arr2['location'] }}</label>

                            <div class="col-md-7">
                                <input id="location" type="text" class="form-control @error('location') is-invalid @enderror" name="location" value="{{ $location }}" autofocus>

                                @error('location')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="c_rstatus" class="col-md-3 col-form-label text-md-right">{{ $field_arr2['c_rstatus'] }}</label>

                            <div class="col-md-7">
                                <?PHP
                                     echo \vwmldbm\code\print_code('code_c_rstatus',$c_rstatus,'c_rstatus',null,null,null,null,null,"class='form-control'");
                                ?>
                                @error('c_rstatus')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="comment" class="col-md-3 col-form-label text-md-right">{{ $field_arr2['comment'] }}</label>

                            <div class="col-md-7">                            
                                <textarea type="text" class="form-control @error('comment') is-invalid @enderror" name="comment" autofocus>{{$comment}}</textarea>
                                @error('comment')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-7 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Register') }}
                                </button>
                                &nbsp; 
                                <button type="button" class="btn btn-success" onClick="window.history.back()">
                                    {{ __('Back') }}
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