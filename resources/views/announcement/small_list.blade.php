<?php
    // Pre-loading the field values for performance
    // $field_arr=array();
    // \vwmldbm\code\get_field_name_all('announcement',$field_arr);
    if(!isset($ann)) {
        $ann=App\Announcement::where('inst',$_SESSION['lib_inst'])->orderBy('top_yn','desc')->take(3)->get();
    }
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2>{{__("Announcement")}}</h2>

        <div class="table-responsive">
          <table class="table table-responsive-sm">
            <tr>
                <th>{{__("title")}}</th>
                <th>{{__("create_id")}}</th>
                <th>{{__("ctime")}}</th>
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
                        {{$a->ctime}}
                    </td>
                </tr>
            @endforeach
          </table>
        </div>
    </div>
</div>