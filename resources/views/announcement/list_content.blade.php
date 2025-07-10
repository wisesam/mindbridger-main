<?php
    // Pre-loading the field values for performance
    $field_arr=array();
    \vwmldbm\code\get_field_name_all('announcement',$field_arr);
?>

<div class="row justify-content-center">
    <div class="col-md-12">
        <h2>{{__("Announcement")}}  &nbsp; 
            @if(Auth::check() && Auth::user()->isAdmin() && !isset($no_add_btt)) 
            <button type='button' class='btn btn-outline-info' onClick="window.location='{{config('app.url','/wlibrary')}}/announcement/create'">
                + 
            </button>
            @endif
        </h2>

        {!!Form::open(['method'=>'POST','class'=>'float-center','id'=>'bDelForm'])!!}
        <div class="table-responsive">
          <table class="table table-responsive-sm">
            <tr>
                <th>{{$field_arr["title"]}}</th>
                <th>{{$field_arr["create_id"]}}</th>
                <th>{{$field_arr["top_yn"]}}</th>
                <th>{{$field_arr["ctime"]}}</th>               
                @if(Auth::check() && Auth::user()->isAdmin()) 
                <th></th>
                <th></th>
                @endif
            </tr>                
            @foreach($ann as $a)
                <tr>
                    <td>
                        <a href="{{config('app.url','/wlibrary')}}/announcement/{{$a->id}}" >
                           {{$a->title}}
                        </a>
                    </td>                    
                    <td>                       
                        {{$a->create_id}}
                    </td>
                    
                    <td>
                        <?PHP
                        if($a->top_yn) {
                            echo \vwmldbm\code\get_c_yn($a->top_yn);                       
                        }
                        ?>
                    </td>                   

                    <td>                       
                        {{$a->ctime}}
                    </td>

                    @if(Auth::check() && Auth::user()->isAdmin()) 
                    <td>
                        <a href="{{config('app.url','/wlibrary')}}/announcement/{{$a->id}}/edit">
                            <img src="{{config('app.url','/wlibrary')}}/image/button/mod_bw.png" class="zoom">
                        </a> 
                    </td>
                    <td>
                        <a href="javascript:confirm_delete('{{$a->id}}');">
                            <img src="{{config('app.url','/wlibrary')}}/image/button/del_bw.png" class="zoom">
                        </a>   
                    </td>
                    @endif
                </tr>
            @endforeach
          </table>
        </div>
        <div class="d-flex">
            <div class="mx-auto">
                <?PHP                
                if(isset($request)) {
                    echo $ann->appends(request()->query())->links('vendor.pagination.bootstrap-4');                    
                }
                //else  echo $ann->links();
                ?>
            </div>
        </div>

        {{Form::hidden('_method','DELETE')}}
        {!!Form::close()!!}
        
        <script>
            function confirm_delete(id) {
                if(confirm("Are you sure you want to delete '"+id+"' ?")) {
                    document.getElementById('bDelForm').action="{{config('app.url','/wlibrary')}}/announcement/"+id;
                    document.getElementById('bDelForm').submit();
                }
            }
        </script>
    </div>
</div>