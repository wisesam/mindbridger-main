<?php
    // Pre-loading the code values for performance
    $field_arr_book=array();
    \vwmldbm\code\get_field_name_all('book',$field_arr_book);

    $field_arr_book_copy=array();
    \vwmldbm\code\get_field_name_all('book_copy',$field_arr_book_copy);

    $c_rstatus_arr=array();
    \vwmldbm\code\get_code_name_all($c_rstatus_arr,'code_c_rstatus');

    $c_rstatus_available_arr=array();
    \vwmldbm\code\get_code_name_all($c_rstatus_available_arr,'code_c_rstatus','available_yn');
    
    if(!isset($search_key)) $search_key=null; 
    if(!isset($code_c_rstatus)) $code_c_rstatus=null; 

    if(Auth::check() && Auth::user()->isAdmin()) $admin_mode=true;
    else $admin_mode=false;

    // get book_copy's availability to show/hide overdue info
    $bc_avail_arr=array();                 
    \vwmldbm\code\get_code_name_all($bc_avail_arr,'code_c_rstatus','available_yn');
  ?>

@extends('layouts.root')
@section('content')
  <form name='form_search'>
  <div class="row justify-content-center">
    <div class="container col-md-12  mt-0"> 
        <h3>{{__("Resource Copy List")}}</h3>
    </div>
    
    <div class="container col-md-6  mt-1">
        <b>{{__("Recourse Copy Barcode / Call No")}}</b>: <input type='text' name='search_key' length=20 autofocus value="<?=$search_key?>">
    </div>
    <div class="container col-md-6  mt-1">
        <b>{{$field_arr_book_copy["c_rstatus"]}}</b>: <?= \vwmldbm\code\print_code('code_c_rstatus',$code_c_rstatus,null,null,null,null,null,null,"class='form-control w-auto d-inline' onChange='document.form_search.submit();'") ?>

    </div>
  </div>
  </form>

  <div class="row justify-content-center">
    <div class="container col-md-12 mt-1">  
        <table class="table table-striped table-responsive-md">
        <tr>
            <th> </th>
            <th class="text-nowrap">{{$field_arr_book["title"]}}</th>
            <th class="text-nowrap">{{$field_arr_book["author"]}}</th>
            <th class="text-nowrap">{{$field_arr_book_copy["barcode"]}}</th>
            <th class="text-nowrap">{{$field_arr_book_copy["call_no"]}}</th>
            <th class="text-nowrap">{{$field_arr_book_copy["location"]}}</th>
            <th class="text-nowrap">{{$field_arr_book_copy["c_rstatus"]}}</th>
            
            @if($admin_mode)
            <th class="text-nowrap">{{__("Rentals")}}</th>
            @endif
            
        </tr>
        <?PHP 
            $page=Request('page');
            if(!$page) $page=1;
            $cnt=($page-1)*10 +1; 
        ?>
        @foreach($book_copy as $bc)
            <?php
                $b=App\Book::where('inst',$_SESSION['lib_inst'])->where('id',$bc->bid)->first();

                $late_rental_exist=App\Rental::late_rental_check(null,null,$bc->id,'RENTED');
                $over_due_tag=null;
                

                if($late_rental_exist && $bc_avail_arr[$bc->c_rstatus]!='Y') {
                    $over_due_tag="<font color='red'> (".__("Overdue").")</font>";
                }
            ?>
            <tr>
                <td>{{$cnt++}}</td>
                <td>
                    @if($admin_mode)
                    <a href="{{config('app.url','/wlibrary')}}/book_copy/{{$bc['id']}}/edit" class='zoom'>
                        {{$b->title}}
                    </a>
                    @else
                        {{$b->title}}
                    @endif
                </td>
                <td>{{$b->author}}</td>
                <td>{{$bc['barcode']}}</td>
                <td>{{$bc['call_no']}}</td>
                <td>{{$bc['location']}}</td>
                <td>
                    <?PHP
                        if(isset($c_rstatus_arr[$bc['c_rstatus']])) {                            
                            if($c_rstatus_available_arr[$bc['c_rstatus']]=='Y') {
                                if($admin_mode) $href=config('app.url','/wlibrary')."/rental/create/".$bc['id'];
                                else $href="javscript:return;";
                                echo "<a href='$href'><font color='".App\Book_copy::print_status_color($bc['c_rstatus'],$c_rstatus_available_arr)."'>".$c_rstatus_arr[$bc['c_rstatus']]."</font></a>";
                            }
                            else echo "<font color='".App\Book_copy::print_status_color($bc['c_rstatus'],$c_rstatus_available_arr)."'>".$c_rstatus_arr[$bc['c_rstatus']]."</font>";
                        }
                        echo $over_due_tag;
                    ?>
                </td>

                @if($admin_mode)
                <td class="text-nowrap">
                <?PHP
                    $rented_num=App\Rental::num_by_book_copy('RENTED',$bc['id']);  
                    $all_num=App\Rental::num_by_book_copy(null,$bc['id']);
                    
                    $rented_num_tag=0;
                    if($rented_num) {
                        $rented_num_tag="<a href='".config('app.url','/wlibrary')."/rental/book_copy/".$bc['id']."/rented'>$rented_num</a>";
                    }

                    $all_num_tag=0;
                    if($all_num) {
                        $all_num_tag="<a href='".config('app.url','/wlibrary')."/rental/book_copy/".$bc['id']."/all'>$all_num</a>";
                    }
                    
                    echo $rented_num_tag." / ".$all_num_tag;
                ?>

                </td>
                @endif
            </tr>
        @endforeach
        </table>

        <div class="d-flex table-responsive-md">
            <div class="mx-auto">
                <?PHP                
                if(isset($request)) {
                    echo $book_copy->appends(request()->query())->links('vendor.pagination.bootstrap-4');                    
                }
                else  echo $book_copy->links('vendor.pagination.bootstrap-4');
                ?>
            </div>
        </div>
    </div>
  </div>
@endsection