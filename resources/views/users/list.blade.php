<?php
    // Pre-loading the field names for performance
    $field_arr=array();
    \vwmldbm\code\get_field_name_all('users',$field_arr);

    // Pre-loading the code values for performance
    $c_utype_arr=array();
    \vwmldbm\code\get_code_name_all($c_utype_arr,'code_c_utype');

    if(!isset($search_key)) $search_key=null; 
    if(!isset($utype)) $utype=null; 
    if(!isset($code_c_utype)) $code_c_utype=null; 
?>

@extends('layouts.root')
@section('content')
    <div class="row justify-content-center table-responsive-sm">
      <div class="col-md-12">
        <h1>{{__("User List")}}
            @if( Auth::user()->isAdmin())                
               &nbsp; <a class="btn btn-outline-info" href="{{config('app.url','/wlibrary')}}/register"> + </a>
            @endif
        </h1>

        <form name='form_search' id='form_search'>
            <div class="container mb-3">
                <div class='container d-inline'><b>{{__("User ID / Barcode / Name")}}</b>: <input type='text' name='search_key' length=20 autofocus value="<?=$search_key?>"></div>
                &nbsp; <div class='container  d-inline' style='white-space: nowrap;'><b>{{__("Type")}}</b>: <?= Auth::user()::print_utype('utype',$utype," onChange='document.form_search.submit();'",'SHORT') ?></div>
                &nbsp; <div class='container  d-inline' style='white-space: nowrap;'><b>{{__("Category")}}</b>: <?= \vwmldbm\code\print_code('code_c_utype',$code_c_utype,null,null,null,null,null,null,"class='form-control w-auto d-inline' onChange='document.form_search.submit();'") ?></div>
            </div>
        </form>
        {!!Form::open(['method'=>'POST','class'=>'float-center','id'=>'uDelForm'])!!}
        <table class="table table-striped">
                <tr>
                    <th>{{$field_arr["id"]}}</th>
                    <th>{{$field_arr["name"]}}</th>
                    <th>{{$field_arr["utype"]}}</th>
                    <th>{{$field_arr["code_c_utype"]}}</th>
                    <th>{{__("Rental")}}</th>
                    <th></th>
                    <th></th>
                </tr>                
                @foreach($ulist as $u)
                    @php 
                        if(!Auth::user()->isAdmin('SA') && Auth::user()->isAdmin('SA',$u['id'])) // skip the usper admin if the user is not super admin
                            continue; 

                        $rented_num=App\Models\Rental::num('RENTED',$u['id']);  
                        $all_num=App\Models\Rental::num(null,$u['id']);

                        $rented_num_tag=0;
                        if($rented_num) {
                            $rented_num_tag="<a href='".config('app.url','/wlibrary')."/rental/".$u['id']."/rented'>$rented_num</a>";
                        }

                        $all_num_tag=0;
                        if($all_num) {
                            $all_num_tag="<a href='".config('app.url','/wlibrary')."/rental/".$u['id']."/all'>$all_num</a>";
                        }
                    @endphp
                    <tr>
                        <td>{{$u['id']}}</td>
                        <td>{{$u['name']}}</td>
                        <td><?=App\Models\User::get_utype($u['utype'])?></td>
                        <td>
                            <?PHP
                                if($u['code_c_utype']) echo $c_utype_arr[$u['code_c_utype']]
                            ?>
                        </td>
                        <td><?=$rented_num_tag?> / <?=$all_num_tag?></td>
                        <td>
                            <a href="{{config('app.url','/wlibrary')}}/users/{{$u['id']}}/edit">
                                <img src="{{config('app.url','/wlibrary')}}/image/button/mod_bw.png" class="zoom">
                            </a>                            
                        </td>
                        <td> 
                            <a href="javascript:confirm_delete('{{$u['id']}}');">
                                <img src="{{config('app.url','/wlibrary')}}/image/button/del_bw.png" class="zoom">
                            </a>                    
                        </td>
                    </tr>
                @endforeach                
        </table>
        <div class="d-flex">
            <div class="mx-auto">
                <?PHP                
                if(isset($request)) {
                    echo $ulist->appends(request()->query())->links('vendor.pagination.bootstrap-4');                    
                }
                else  echo $ulist->links('vendor.pagination.bootstrap-4');
                ?>
            </div>
        </div>

        {{Form::hidden('_method','DELETE')}}
        {!!Form::close()!!}
        
        <script>
            function confirm_delete(id){
                if(confirm("{{__('messages.delete')}}"+" ["+id+"]")) {
                    document.getElementById('uDelForm').action="{{config('app.url','/wlibrary')."/users/"}}"+id+"/destroy";
                    document.getElementById('uDelForm').submit();
                }
            }
        </script>
      </div>   
    </div>
@endsection