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

    // Get all maximum books that users can rent from code_c_utype
    $max_book=array();
    \vwmldbm\code\get_code_name_all($max_book,'code_c_utype','max_book'); 
?>
@extends('layouts.root')
@section('content')
    <div class="row justify-content-center table-responsive-sm">
      <div class="col-md-12">
        <h1>{{__("User List")}}</h1>

        <form name='form_search'>
            <div class="container mb-3">
                <div class='container d-inline'><b>{{__("User ID / Barcode / Name")}}</b>: <input type='text' name='search_key' length=20 autofocus value="<?=$search_key?>"></div>
                &nbsp; <div class='container  d-inline' style='white-space: nowrap;'><b>{{__("Type")}}</b>: <?= Auth::user()::print_utype('utype',$utype," onChange='document.form_search.submit();'",'SHORT') ?></div>
                &nbsp; <div class='container  d-inline' style='white-space: nowrap;'><b>{{__("Category")}}</b>: <?= \vwmldbm\code\print_code('code_c_utype',$code_c_utype,null,null,null,null,null,null,"class='form-control w-auto d-inline' onChange='document.form_search.submit();'") ?></div>
            
                <script>
                    $(document).ready(function(){
                        document.form_search.search_key.focus();
                    });
                </script>
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
                </tr>                
                @foreach($ulist as $u)
                    <?php 
                        if(!Auth::user()->isAdmin('SA') && Auth::user()->isAdmin('SA',$u['id'])) // skip the usper admin if the user is not super admin
                            continue; 

                        $rented_num=App\Rental::num('RENTED',$u['id']);  
                        $all_num=App\Rental::num(null,$u['id']);
                        
                        $rented_num_tag=0;
                        $btn="btn-info";
                        if($rented_num) {
                            if(isset($u['code_c_utype']) && $max_book[$u['code_c_utype']] <= $rented_num) {
                                $rented_num_color="red";
                                $btn="btn-danger";
                            }
                            else $rented_num_color=null;
                            $rented_num_tag="<a href='".config('app.url','/wlibrary')."/rental/".$u['id']."/rented'><font color='$rented_num_color'>$rented_num</font></a>";
                        }

                        $all_num_tag=0;
                        if($all_num) {
                            $all_num_tag="<a href='".config('app.url','/wlibrary')."/rental/".$u['id']."/all'>$all_num</a>";
                        }
                        $late_rental_exist=App\Rental::late_rental_check($u['id']);
                        if($late_rental_exist) $btn="btn-danger";

                        $over_due_tag=null;
                        if($late_rental_exist) $over_due_tag="<br><font color='red'> (".__("Overdue").")</font>";

                    ?>
                    <tr>
                        <td>{{$u['id']}}</td>
                        <td>{{$u['name']}}</td>
                        <td><?=App\User::get_utype($u['utype'])?></td>
                        <td>
                            <?php
                                if($u['code_c_utype']) $c_utype_arr[$u['code_c_utype']]
                            ?>
                        </td>
                        <td><?=$rented_num_tag?> / <?=$all_num_tag?> <?=$over_due_tag?></td>
                        <td>
                            <a href="javascript:choose_user('{{$u['id']}}','{{$u['code_c_utype']}}')" class="btn {{$btn}}" >{{__("Choose")}}</a>
                            <script>
                                function choose_user(u,c){                                  
                                    window.parent.choose_user_from_iframe(u,c);
                                }
                            </script>
                        </td>
                    </tr>
                @endforeach
        </table>
        <div class="d-flex">
            <div class="mx-auto">
                <?PHP                
                if(isset($request)) {
                    echo $ulist->appends(request()->query())->links();                    
                }
                else  echo $ulist->links();
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