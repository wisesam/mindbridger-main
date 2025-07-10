<?php
    // Pre-loading the code values for performance
    $field_arr_book=array();
    \vwmldbm\code\get_field_name_all('book',$field_arr_book);

    $field_arr_book_copy=array();
    \vwmldbm\code\get_field_name_all('book_copy',$field_arr_book_copy);

    $field_arr_rental=array();
    \vwmldbm\code\get_field_name_all('rental',$field_arr_rental);

    // Pre-loading the code values for performance
    $c_rent_status_arr=array();
    \vwmldbm\code\get_code_name_all($c_rent_status_arr,'code_c_rent_status');

    $c_rent_status_arr_default=array();
    \vwmldbm\code\get_code_name_all($c_rent_status_arr_default,'code_c_rent_status',null,10);
?>

@extends('layouts.root')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
                <h4>{{ __('Resource Copy') }}</h4>
                <div class="container bt-1">
                    <form method="POST" name='form1' action="{{config('app.url','/wlibrary')."/book_copy/".$book_copy->id}}">
                        @csrf
                        @method('put')
                        <input type='hidden' name='bid' value="{{$book->id}}">
                        <div class="form-group row">
                            <label for="book_name" class="col-md-4 col-form-label text-md-right">{{ $field_arr_book['title'] }}</label>

                            <div class="col-md-8">
                                <span class='container bt-0' style='border:solid black 0px;'>{{ $book->title }}</span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="barcode" class="col-md-4 col-form-label text-md-right">{{ $field_arr_book_copy['barcode'] }}</label>

                            <div class="col-md-8">
                                <input id="barcode" type="text" class="form-control @error('barcode') is-invalid @enderror" name="barcode" value="{{ $book_copy->barcode }}" autocomplete="{{ old('author') }}" autofocus>

                                @error('barcode')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="call_no" class="col-md-4 col-form-label text-md-right">{{ $field_arr_book_copy['call_no'] }}</label>

                            <div class="col-md-8">
                                <input id="call_no" type="text" class="form-control @error('call_no') is-invalid @enderror" name="call_no" required value="{{$book_copy->call_no }}" autocomplete="{{ old('call_no') }}" autofocus>

                                @error('call_no')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="location" class="col-md-4 col-form-label text-md-right">{{ $field_arr_book_copy['location'] }}</label>

                            <div class="col-md-8">
                                <input id="location" type="text" class="form-control @error('location') is-invalid @enderror" name="location" value="{{ $book_copy->location }}" autocomplete="{{ old('location') }}" autofocus>

                                @error('location')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="c_rstatus" class="col-md-4 col-form-label text-md-right">{{ $field_arr_book_copy['c_rstatus'] }}</label>

                            <div class="col-md-8">
                                <?PHP
                                    $direct_change_yn=\vwmldbm\code\get_c_name('code_c_rstatus',$book_copy->c_rstatus,'direct_change_yn');
                                    if($direct_change_yn=='Y') 
                                        echo \vwmldbm\code\print_code('code_c_rstatus',$book_copy->c_rstatus,'c_rstatus',null,null,null,null,null,"class='form-control'", "and direct_change_yn<>'N'");
                                    else if(!$direct_change_yn) // status code was not assigned then just let available be selected
                                        echo \vwmldbm\code\print_code('code_c_rstatus',$book_copy->c_rstatus,'c_rstatus',null,null,null,null,null,"class='form-control'", "and available_yn='Y'");
                                    else echo "<span class='form-control border-0' style='color:blue;font-weight:bold;'>".\vwmldbm\code\get_c_name('code_c_rstatus',$book_copy->c_rstatus)."</span>";
                                ?>
                                @error('c_rstatus')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="comment" class="col-md-4 col-form-label text-md-right">{{ $field_arr_book_copy['comment'] }}</label>

                            <div class="col-md-8">                            
                                <textarea type="text" class="form-control @error('comment') is-invalid @enderror" name="comment" autocomplete="{{ old('comment') }}" autofocus>{{$book_copy->comment}}</textarea>
                                @error('comment')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Update') }}
                                </button>
                                &nbsp; 
                                <button type="button" class="btn btn-success" onClick="window.location='{{config('app.url','/wlibrary')."/book/".$book_copy->bid."/edit"}}'">
                                    {{ __('Book Info') }}
                                </button>
                                &nbsp; 
                                <button type="button" class="btn btn-info" onClick="window.location='{{config('app.url','/wlibrary')."/book_copy/"}}'">
                                    {{ __('Book Copy List') }}
                                </button>
                                &nbsp; 
                                <button type="button" class="btn btn-danger" onClick="confirm_delete()">
                                    {{ __('Delete') }}
                                </button>

                                <script>
                                    function confirm_delete() {
                                        if(confirm("Are you sure you want to delete?")) {
                                           document.getElementById('bcDelForm').submit();
                                        }
                                    }
                                </script>
                            </div>
                        </div>
                    </form>
                    
                    <form  id='bcDelForm' method='POST' action='{{config('app.url','/wlibrary')."/book_copy/".$book_copy->id}}'>
                        @csrf
                        @method('delete')
                    </form>
                </div>

        </div>
    </div>
</div>

    @if(isset($rentals))
    <div class="card-body">
        {!!Form::open(['method'=>'POST','class'=>'float-center','id'=>'bDelForm'])!!}
 
        <div class="table-responsive">
            <script>
                $(document).ready(function (){
                    $( "#dialog" ).dialog({
                        width:'75%',
                        autoOpen: false,
                        position: {
                            my: 'middle',
                            at: 'top',
                            of: this,
                        }
                    });
                });
    
                function open_cover_img(obj){ 
                    $('#dialog').dialog('open');    
                    $('#dialog_img').attr("src",obj.src);                                            
                }               
            </script>
            <div id="dialog" title="" style="display:none; align-top;">
                <img id='dialog_img' width='100%'>
            </div> 
    
        <table class="table table-striped">
            <tr>
                <th> </th>
                <th>{{$field_arr_rental["uid"]}}</th>            
                <th>{{$field_arr_rental["rent_date"]}}</th>
                <th>{{$field_arr_rental["due_date"]}}</th>
                <th>{{$field_arr_rental["return_date"]}}</th>
                <th>{{$field_arr_rental["c_rent_status"]}}</th>
                <th>{{$field_arr_rental["rcomment"]}}</th>
                
                @if(Auth::check() && Auth::user()->isAdmin()) 
                <th></th>
                <th></th>
                @endif
            </tr>                
            @if(isset($rentals) && count($rentals))
                @foreach($rentals as $r)
                
                <?PHP
                    $book_copy = App\Book_copy::where('inst',$_SESSION['lib_inst'])
                        ->where('id',$r->bcid)
                        ->first();
                    
                    $book = App\Book::where('inst',$_SESSION['lib_inst'])
                        ->where('id',$book_copy->bid)
                        ->first();
                ?>
                
                <tr>
                    <td></td>
                    <td>{{$r->uid}}</td>  
                    <td>{{$r->rent_date->format('Y-m-d H:i')}}</td>  
                    <td>{{$r->due_date->format('Y-m-d H:i')}}</td>      
                    <td>
                        <?PHP
                            if($r->return_date) echo $r->return_date->format('Y-m-d H:i')
                        ?>
                    </td>  
                    
                    <td>
                        <?PHP
                        if($r->c_rent_status) {
                            $aTag= "<a href='".config('app.url','/wlibrary')."/rental/".$r->id."/edit'>";
                            if(isset($c_rent_status_arr[$r->c_rent_status])) echo $aTag.$c_rent_status_arr[$r->c_rent_status]."</a>"; 
                            else echo $aTag.$c_rent_status_arr_default[$r->c_rent_status]."</a>";                           
                        }
                        ?>
                    </td>
    
                    <td>
                        <?php 
                            if(mb_strlen($r->rcomment) > 20) {
                                $rcomment=mb_substr($r->rcomment,0,18)."..";
                            }
                            else $rcomment=$r->rcomment;
                        ?>    
                        {{$rcomment}} 
                    </td> 
                    @if(Auth::check() && Auth::user()->isAdmin()) 
                    <th></th>
                    <th></th>
                    @endif
                </tr>
                @endforeach
            @endif
        </table>
        
        <div class="d-flex">
            <div class="mx-auto">
                <?PHP                
                if(isset($request)) {
                    echo $rentals->appends(request()->query())->links();                    
                }
                else  echo $rentals->links();
                ?>
            </div>
        </div>
    
        {{Form::hidden('_method','DELETE')}}
        {!!Form::close()!!}
    
    </div>
    @endif

@endsection