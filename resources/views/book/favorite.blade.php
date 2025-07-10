@extends('layouts.root')
@section('content')
<?php
    // Pre-loading the field values for performance
    $field_arr=array();
    \vwmldbm\code\get_field_name_all('book',$field_arr);

    // Pre-loading the code values for performance
    $c_rtype_arr=array();
    \vwmldbm\code\get_code_name_all($c_rtype_arr,'code_c_rtype');
    $c_rtype_arr_default=array();
    \vwmldbm\code\get_code_name_all($c_rtype_arr_default,'code_c_rtype',null,10);

    $c_genre_arr=array();
    \vwmldbm\code\get_code_name_all($c_genre_arr,'code_c_genre');    
    $c_genre_arr_default=array();
    \vwmldbm\code\get_code_name_all($c_genre_arr_default,'code_c_genre',null,10);

    $c_grade_arr=array();
    \vwmldbm\code\get_code_name_all($c_grade_arr,'code_c_grade');    
    $c_grade_arr_default=array();
    \vwmldbm\code\get_code_name_all($c_grade_arr_default,'code_c_grade',null,10);

    $c_category_arr=array();
    \vwmldbm\code\get_code_name_all($c_category_arr,'code_c_category');    
    $c_category_arr_default=array();
    \vwmldbm\code\get_code_name_all($c_category_arr_default,'code_c_category',null,10);

    $c_category2_arr=array();
    \vwmldbm\code\get_code_name_all($c_category2_arr,'code_c_category2');    
    $c_category2_arr_default=array();
    \vwmldbm\code\get_code_name_all($c_category2_arr_default,'code_c_category2',null,10);
    
    if(isset($books))
        $bnum=$books->total();

    $page_size=isset($_REQUEST['page_size'])? $_REQUEST['page_size'] : null;

    $search_word_arr=array();
    $operation=null;
?>

<div class="row justify-content-center">
    <div class="col-sm-12 col-md-12 col-lg-12">
        {!!Form::open(['method'=>'GET','class'=>'float-center','name'=>'bookListForm'])!!}
        <h1>{{__("My Favorites")}}  &nbsp; 
            @if(Auth::check() && Auth::user()->isAdmin() && !isset($no_add_btt)) 
            <button type='button' class='btn btn-outline-info' onClick="window.location='{{config('app.url','/wlibrary')}}/book/create'">
                + 
            </button>
            @endif
           <?php
           $pageUri=null;
           foreach($_GET as $key=>$val) {
               if(is_array($val)) {
                    foreach($val as $v){
                        $pageUri.="&key[]=$v";
                    }
               }
               else {
                    $pageUri.="&$key=$val";
               }
           }
            echo \wlibrary\code\page_size('page_size',$page_size," onChange=\"window.location.href='?{$pageUri}&page_size='+this.value;\"");
           ?>
        </h1>

        @if(isset($search_word) || $operation=='ASEARCH')
            <h4 class="text-center text-info"> {{__('Search Results')}}: {{$bnum}}</h4>
        @endif
        
        <div class="table">
            <script>
                $(document).ready(function (){
                    $( "#dialog" ).dialog({
                        width:'auto',
                        height:'auto',
                        maxWidth:'400',
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

          <table class="table table-responsive-md">
            <tr>
                <th> </th>
                <th class="text-nowrap">{{$field_arr["title"]}}</th>
                <th>{{$field_arr["author"]}}</th>
                <th>{{$field_arr["e_resource_yn"]}}</th>
                <th>{{$field_arr["c_rtype"]}}</th>
                
                @if(\vwmldbm\code\is_code_usable('c_genre'))
                    <th>{{$field_arr["c_genre"]}}</th>
                @endif
                
                @if(\vwmldbm\code\is_code_usable('c_grade'))
                    <th>{{$field_arr["c_grade"]}}</th>
                @endif

                @if(\vwmldbm\code\is_code_usable('c_category'))
                    <th>{{$field_arr["c_category"]}}</th>
                @endif

                @if(\vwmldbm\code\is_code_usable('c_category2'))
                    <th>{{$field_arr["c_category2"]}}</th>
                @endif

                <th>{{$field_arr["publisher"]}}</th>
                <th>{{$field_arr["pub_date"]}}</th>
                <th>{{__("Copies")}}</th>
                
                @if(Auth::check() && Auth::user()->isAdmin()) 
                <th></th>
                <th></th>
                @endif
            </tr>                
            @foreach($books as $b)
                <tr>
                    <td>
                        @if($b->cover_image) 
                            <img onClick="open_cover_img(this)" style='cursor:pointer;' src='{{config('app.url','/wlibrary')}}/storage/cover_images/{{$_SESSION['lib_inst']}}/{{$b->cover_image}}' height='50'>
                        @endif
                    </td>
                    
                    <td>
                        <a href="{{config('app.url','/wlibrary')}}/book/{{$b->id}}" >
                            <?
                            if(isset($search_target) && $search_target=='i_title')
                                echo \wlibrary\code\highlight($b->title,$search_word_arr);
                            else if(!isset($search_target) && isset($search_word))
                                echo \wlibrary\code\highlight($b->title,$search_word_arr);                           
                            else echo $b->title;
                            ?>
                        </a>
                    </td>                        
                    
                    <td>                       
                        <?
                        if(isset($search_target) && $search_target=='i_author')
                            echo \wlibrary\code\highlight($b->author,$search_word_arr);
                        else if(!isset($search_target) && isset($search_word))
                            echo \wlibrary\code\highlight($b->author,$search_word_arr);
                        else echo $b->author;
                        ?>
                    </td>
                    
                    <td>
                        <?PHP
                        if($b->e_resource_yn) {
                            echo \vwmldbm\code\print_c_yn('e_resource_yn',$b->e_resource_yn,null,'RD_ONLY_Y',"");                       
                            $rfiles=explode(';',$b->rfiles);
                            $num_files=count($rfiles)-1; // empty entry is included
                            if($num_files>1) $num_files_txt="({$num_files})";
                            else $num_files_txt=null;

                            $fsize=0;
                            foreach($rfiles as $key => $val) {
                                $fpath=config('app.root')."/storage/app/ebook/{$_SESSION['lib_inst']}/{$b->rid}/{$rfiles[$key]}";
                                if($val && file_exists($fpath)) $fsize+=filesize($fpath);
                            }
                            $fsize=\wlibrary\code\format_fsize($fsize,'MB',1)."MB";
                            if($num_files>0) echo "<br><font color='green'>{$fsize} $num_files_txt</font>";
                            //if($num_files==3) echo $b->rfiles; // for debugging
                        }
                        ?>
                    </td>

                    <td>
                        <?PHP
                        if($b->c_rtype) {
                            if(isset($c_rtype_arr[$b->c_rtype])) echo $c_rtype_arr[$b->c_rtype]; 
                            else echo $c_rtype_arr_default[$b->c_rtype];                           
                        }
                        ?>
                    </td>
                
                @if(\vwmldbm\code\is_code_usable('c_genre'))
                    <td>
                        <?PHP
                        if($b->c_genre) {
                            if(isset($c_genre_arr[$b->c_genre])) echo $c_genre_arr[$b->c_genre]; 
                            else echo $c_genre_arr_default[$b->c_genre];                           
                        }
                        ?>
                    </td>
                @endif

                @if(\vwmldbm\code\is_code_usable('c_grade'))
                    <td>
                        <?PHP
                        if($b->c_grade) {
                            if(isset($c_grade_arr[$b->c_grade])) echo $c_grade_arr[$b->c_grade]; 
                            else echo $c_grade_arr_default[$b->c_grade];                           
                        }
                        ?>
                    </td>
                @endif

                @if(\vwmldbm\code\is_code_usable('c_category'))
                    <td>
                        <?PHP
                        if($b->c_category) {
                            if(isset($c_category_arr[$b->c_category])) echo $c_category_arr[$b->c_category]; 
                            else echo $c_category_arr_default[$b->c_category];                           
                        }
                        ?>
                    </td>
                @endif

                @if(\vwmldbm\code\is_code_usable('c_category2'))
                    <td>
                        <?PHP
                        if($b->c_category2) {
                            if(isset($c_category2_arr[$b->c_category2])) echo $c_category2_arr[$b->c_category2]; 
                            else echo $c_category2_arr_default[$b->c_category2];                           
                        }
                        ?>
                    </td>
                @endif

                    <td>                        
                        <?
                        if(isset($search_target) && $search_target=='i_publisher')
                            echo \wlibrary\code\highlight($b->publisher,$search_word_arr);
                        else if(!isset($search_target) && isset($search_word))
                            echo \wlibrary\code\highlight($b->publisher,$search_word_arr);
                        else echo $b->publisher;
                        ?>
                    </td>

                    @if($b->pub_date) 
                        <td>{{$b->pub_date->format('Y-m-d')}}</td>
                    @else
                        <td> </td>
                    @endif
                    
                    <td>
                        <?php
                            $copy_num=$b->get_copy_num();
                        ?>
                        {{$copy_num}}
                    </td>
                </tr>
            @endforeach
          </table>
        </div>
        
        <div class="d-flex table-responsive-md">
            <div class="mx-auto">
                <?PHP                
                if(isset($request)) {
                    echo $books->appends(request()->query())->links('vendor.pagination.bootstrap-4');                    
                }
                else echo $books->links('vendor.pagination.bootstrap-4');
                ?>
            </div>
        </div>
        {!!Form::close()!!}
    </div>
</div>
@endsection