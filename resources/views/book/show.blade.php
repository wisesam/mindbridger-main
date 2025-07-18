<?php
    // Pre-loading the code values for performance
    $field_arr=array();
    \vwmldbm\code\get_field_name_all('book',$field_arr);

    $field_arr2=array();
    \vwmldbm\code\get_field_name_all('book_copy',$field_arr2);

    $c_rstatus_arr=array();
    \vwmldbm\code\get_code_name_all($c_rstatus_arr,'code_c_rstatus');
    
    $perm['R'] ='Y'; // Read permission
	$isAdmin = (Auth::check() && Auth::user()->isAdmin()) ? true : false;

    // fix (may not be valid way) 2025.1.23
    session(['lib_inst' => (session('inst_uname')??(session('inst_id')??null))]);  
    $isEresource = ($book->files ? true : false);
?>

@extends('layouts.root')
@section('content')
<style>
.img-icon-pointer {
    cursor:pointer;
    width:40px; 
    height:auto;
}
</style>

<div class="row justify-content-center">
    <div class="container col-12 mt-0">
        <div class="card">
            <div class="card-header col-12">
            @if($isAdmin)
                <span style='margin-left: 12px; display:inline'>
                    <img src="{{config('app.url','/wlibrary')}}/image/button/set.png" class="zoom img-icon-pointer" onClick="window.location='{{config('app.url','/wlibrary')."/book/".$book->id}}/edit'">
                </span>
            @endif
                <span style='margin-left: 12px; display:inline'>
                    <img src="{{config('app.url','/wlibrary')}}/image/button/share.png" class="zoom img-icon-pointer" onClick="textToClipboard('<?=config('app.url','/wlibrary')."/inst/".session('inst_uname')."/book/".$book->id?>')">
                </span>
                <script>
                     function textToClipboard (text) {                        
                        navigator.clipboard.writeText(text)
                            .then(() => { alert(`<?=__("The Resource URL was coppied to your clipboard!")?>`) })
                            .catch((error) => { alert(`Copy failed! ${error}`) });	
                    }
                </script>

            @if(Auth::check() && !$isAdmin)
                <span style='margin-left: 12px; display:inline'>
                    <!-- Favorite Checkbox with Heart Icon -->
                    <label style="cursor:pointer;" class="ml-2 mb-0">
                        <input type="checkbox" id="favorite-checkbox" style="display:none;" onchange="toggleFavorite(this)">
                        <img id="favorite-icon" src="{{ config('app.url','/wlibrary') }}/image/button/heart-empty2.png" class="zoom img-icon-pointer"/>
                    </label>

                    <script>
                            let isFavorited = false;
                            
                            $.get("{{ route('book.favorite.check', ['book' => $book->id]) }}")
                                .done(function (response) {
                                    if (response.favorited) {
                                        isFavorited = true;
                                        $('#favorite-icon').attr('src', '{{ config("app.url","/wlibrary") }}/image/button/heart-filled2.png');
                                        $('#favorite-checkbox').prop('checked', true);
                                    }
                                });

                            $(document).ready(function () {
                                if (isFavorited) {
                                    $('#favorite-checkbox').prop('checked', true);
                                    $('#favorite-icon').attr('src', '{{ config("app.url","/wlibrary") }}/image/button/heart-filled2.png');
                                }
                            });

                            function toggleFavorite(checkbox) {
                                let isChecked = checkbox.checked;
                                let icon = $('#favorite-icon');
                                if (isChecked) { // try to add it as favorite
                                    icon.attr('src', '{{ config("app.url","/wlibrary") }}/image/button/heart-filled2.png');
                                    $.post("{{ route('book.favorite.store', ['book' => $book->id]) }}", {
                                        _token: '{{ csrf_token() }}'
                                    });

                                } else { // try to remove favorite
                                    icon.attr('src', '{{ config("app.url","/wlibrary") }}/image/button/heart-empty2.png');
                                    $.ajax({
                                        url: "{{ route('book.favorite.remove', ['book' => $book->id]) }}",
                                        type: 'DELETE',
                                        data: { _token: '{{ csrf_token() }}' }
                                    });
                                }
                            }
                    </script>
                </span>
            @endif
            </div>
            <div class="card-body col-12">
                <form method="POST" name='form1' id='pform' action="{{config('app.url','/wlibrary')."/book/".$book->id}}" enctype="multipart/form-data">
                    @csrf 
                    <input type='hidden' name='_method' value='PUT'>
                    <input type='hidden' name="progress_up_flag">                   
                    <input type='hidden' name="id" value='{{$book->id}}'>
                    <input type='hidden' name="del_file">                   
                    <div class="form-group row">
                        <label for="title" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['title'] }}</label>

                        <div class="col-md-9">
                            <div class='container border-0 mt-0'>{{ $book->title }}</div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="author" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['author'] }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'>{{ $book->author }}</div>
                        </div>
                    </div>

                    @if($isEresource) 
                        @auth
                            <?PHP
                                $video_txt=show_list_videos($book,$perm,$book->rid);
                            ?>
                            @if($video_txt!='')
                                <div class="form-group row">
                                    <label for="video" class="col-md-3 col-form-label text-md-right font-weight-bold">{{__('Video') }}</label>
                                    <div class="col-md-9">                                     
                                        <?=$video_txt?>                                                         
                                    </div>
                                </div>
                            @endif
                        @endauth
                        
                        <div class="form-group row">
                            <label for="e-Resources" class="col-md-3 col-form-label text-md-right font-weight-bold">
                                {{ __('e-Resources') }}                               
                            </label>
                            @if(Auth::check() || $book->e_res_af_login_yn!='Y') 
                            <div class="col-md-9">
                                <?PHP 
                                        echo show_list_old_files($book,$perm,$book->rid);                                
                                ?>
                                <ol id='fileList'></ol>                               
                                                            
                            </div>
                            @elseif($book->e_resource_yn=='Y' && $book->e_res_af_login_yn=='Y')
                                <div class="col-md-9">
                                <span style='color:magenta;'>
                                    {{__("Log in required")}}
                                </span>
                                </div>
                            @endif
                        </div>

                    @endif

                    <div class="form-group row">
                        <label for="rtype" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['c_rtype'] }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'><?=\vwmldbm\code\get_c_name('code_c_rtype',$book->c_rtype)?></div>
                        </div>
                    </div>

                @if(\vwmldbm\code\is_code_usable('code_c_genre'))    
                    <div class="form-group row">
                        <label for="c_genre" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['c_genre'] }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'><?=\vwmldbm\code\get_c_name('code_c_genre',$book->c_genre)?></div>
                        </div>
                    </div>
                @endif

                @if(\vwmldbm\code\is_code_usable('code_c_grade'))
                    <div class="form-group row">
                        <label for="c_grade" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['c_grade'] }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'><?=\vwmldbm\code\get_c_name('code_c_grade',$book->c_grade)?></div>
                        </div>
                    </div>
                @endif

                @if(\vwmldbm\code\is_code_usable('code_c_category'))
                    <div class="form-group row">
                        <label for="c_category" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['c_category'] }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'><?=\vwmldbm\code\get_c_name('code_c_category',$book->c_category)?></div>
                        </div>
                    </div>
                @endif
                
                @if(\vwmldbm\code\is_code_usable('code_c_category2'))
                    <div class="form-group row">
                        <label for="c_category2" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['c_category2'] }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'><?=\vwmldbm\code\get_c_name('code_c_category2',$book->c_category2)?></div>
                        </div>
                    </div>
                @endif

                    <div class="form-group row">
                        <label for="c_lang" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['c_lang'] }}</label>

                        <div class="col-md-9">
                            <span class='form-control border-0'>
                                <?=\vwmldbm\code\print_code('vwmldbm_c_lang',$book->c_lang,'c_lang',null,null,null,'RD_ONLY',null,"class='form-control'");?>
                            </span>
                        </div>
                    </div>
                    

                    <div class="form-group row">
                        <label for="e_resource_yn" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['e_resource_yn'] }}</label>
                        <div class="col-md-9">
                            <div class='form-control border-0'>                            
                                <?PHP
                                echo \vwmldbm\code\print_c_yn('e_resource_yn',$book->e_resource_yn,null,'RD_ONLY',null,'Y_BLUE');
                                ?>
                            </div>                             
                        </div>
                    </div>                                    

                    <div class="form-group row">
                        <label for="desc" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['desc'] }}</label>

                        <div class="col-md-9">
                            <div class='form-control-static overflow-auto' style='max-height:600px;min-height:100px;'>
                                <?=stripslashes($book->desc)?>
                            </div>                         
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="publisher" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['publisher'] }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'>{{ $book->publisher }}</div>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="url" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['url'] }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'><a href='{{ $book->url }}' target='_blank'>{{ $book->url }}</a></div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="pub_date" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['pub_date'] }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'>{{ $book->pub_date }}</div>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="isbn" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['isbn'] }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'>{{ $book->isbn }}</div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="eisbn" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['eisbn'] }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'>{{ $book->eisbn }}</div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="keywords" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['keywords'] }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'>{{ $book->keywords }}</div>                               
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="price" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['price'] }}</label>

                        <div class="col-md-9">
                            <div class='form-control border-0'>{{ $book->price }}</div>                               
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="cover_image" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['cover_image'] }}</label>
                        <div class="col-md-9">
                            @if($book->cover_image)
                                <img onClick='open_cover_img(this)' style='cursor:pointer;' src='{{config('app.url','/nwlibrary')}}/storage/cover_images/{{$_SESSION['lib_inst']}}/{{$book->cover_image}}' height='200'>
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

                                    function open_cover_img(obj) {
                                        $('#dialog').dialog('open');    
                                        $('#dialog_img').attr("src",obj.src);                                            
                                    }
                                </script>

                                <div id="dialog" title="" style="display:none; align-top;">
                                    <img id='dialog_img' width='100%'>
                                </div>    
                            @endif
                        </div>
                    </div>

                    <div class="form-group row mb-0">
                        <div class="col-md-9 offset-md-4">                               
                            <button type="button" class="btn btn-success" onClick="window.history.back();">
                                {{ __('Go Back') }}
                            </button>
                        </div>                          
                    </div>                                           
                </form>
            </div>
        </div>
    </div>

    <div class="container col-12"> 
        <table class="table table-striped table-responsive-md">
        <tr>
            <th> </th>
            <th>{{$field_arr2["barcode"]}}</th>
            <th>{{$field_arr2["call_no"]}}</th>
            <th>{{$field_arr2["location"]}}</th>
            <th>{{$field_arr2["c_rstatus"]}}</th>
        </tr>
        <?PHP $cnt=1; ?>
        @foreach($book_copy as $bc)                  
            <tr>
                <td>{{$cnt++}}</td> 
                <td>{{$bc['barcode']}}</td>
                <td>{{$bc['call_no']}}</td>
                <td>{{$bc['location']}}</td>
                <td>
                    <?PHP
                        if(isset($c_rstatus_arr[$bc['c_rstatus']])) echo $c_rstatus_arr[$bc['c_rstatus']];
                    ?>
                </td>
            </tr>
        @endforeach
        </table>
    </div>
</div>
<br><br>
@endsection