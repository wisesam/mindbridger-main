<?PHP
    $adminMode=Auth::user()->isAdmin();
    if(isset($barcode) && !$adminMode) unset($barcode); // illegal operation

    if(isset($barcode)) { // if searching by barcode has a result
        // Pre-loading the code values for performance
        $field_arr=array();
        \vwmldbm\code\get_field_name_all('rental',$field_arr);

        $field_arr2=array();
        \vwmldbm\code\get_field_name_all('book',$field_arr2);

        $field_arr3=array();
        \vwmldbm\code\get_field_name_all('book_copy',$field_arr3);
    }
?>

@extends('layouts.root')
@section('content')

@if(isset($barcode))
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Resource Copy Information') }}</div>
                <div class="card-body">
                    <div class="form-group row">
                                                    <label for="book_name" class="col-md-3 col-form-label text-md-right">{{ __("Title") }}</label>
                        <div class="col-md-9">
                            <span class='form-control' style='border:solid black 0px;'>{{ $book->title }}</span>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="barcode" class="col-md-3 col-form-label text-md-right">{{ $field_arr3['barcode'] }}</label>

                        <div class="col-md-9">
                            <span class='form-control' style='border:solid black 0px;'>{{ $book_copy->barcode }}</span>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="call_no" class="col-md-3 col-form-label text-md-right">{{ $field_arr3['call_no'] }}</label>

                        <div class="col-md-9">
                            <span class='form-control' style='border:solid black 0px;'>{{ $book_copy->call_no }}</span>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="location" class="col-md-3 col-form-label text-md-right">{{ $field_arr3['location'] }}</label>
                        <div class="col-md-9">
                            <span class='form-control' style='border:solid black 0px;'>{{ $book_copy->location }}</span>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="c_rstatus" class="col-md-3 col-form-label text-md-right">{{ $field_arr3['c_rstatus'] }}</label>
                        <div class="col-md-9">
                            <span class='form-control' style='border:solid black 0px;'><?=\vwmldbm\code\get_c_name('code_c_rstatus',$book_copy->c_rstatus)?></span>                          
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="comment" class="col-md-3 col-form-label text-md-right">{{ $field_arr3['comment'] }}</label>

                        <div class="col-md-9">                            
                            <span class='form-control' style='border:solid black 0px;'>{{ $book_copy->comment }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        {{__("Rent History")}}
                    </div>
                    @if(!isset($uid) && $adminMode)
                    <form name='form1' id='form1'>
                        <div class="container">
                            {{__("Book Copy Barcode:")}} <input type='text' name='barcode' length=20 autofocus>
                        </div>
                    </form>
                    @endif
                    @include('rental.list_content')
                </div>
            </div>           
        </div>
    </div>
@endsection