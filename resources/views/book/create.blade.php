<?php
    // Pre-loading the code values for performance
    $field_arr=array();
    \vwmldbm\code\get_field_name_all('book',$field_arr);
    $IMG_WIDTH =600; // cover photo image resolution
    
    $book=Session::get('book');
    if(!isset($book)){ // fresh creation. If it is set, comes back due to duplicate ISBN/e-ISBN 
        $book=array();
        foreach($field_arr as $key => $val) $book[$key]=null;
    }
?>

@extends('layouts.root')
@section('content')
<script src="{{ auto_asset('/lib/jquery/jquery.form.min.js') }}"></script>
<script src="{{ auto_asset('/lib/ckeditor_4c/ckeditor.js') }}"></script>
<script>
    $(document).ready(function() {
        CKEDITOR.replace( 'desc',{
            customConfig: '{{config('app.url')}}/lib/ckeditor_4c/config_gen.js'
        });
    });
</script>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">{{ __('Register Book') }}</div>

                <div class="card-body">
                    <form method="POST" name='createForm' action="{{auto_url(route('book.store', [], false))}}" enctype="multipart/form-data">
                        @csrf
                      
                        <div class="form-group row">
                            <label for="title" class="col-md-3 col-form-label text-md-right">{{ $field_arr['title'] }}</label>

                            <div class="col-md-7">
                                <input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ $book['title'] }}" required  autofocus>

                                @error('title')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="author" class="col-md-3 col-form-label text-md-right">{{ $field_arr['author'] }}</label>

                            <div class="col-md-7">
                                <input id="author" type="text" class="form-control @error('author') is-invalid @enderror" name="author" value="{{ $book['author'] }}"  autofocus>

                                @error('author')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="rtype" class="col-md-3 col-form-label text-md-right">{{ $field_arr['c_rtype'] }}</label>

                            <div class="col-md-7">
                                <?PHP
                                     echo \vwmldbm\code\print_code('code_c_rtype',$book['c_rtype'],'c_rtype',null,null,null,null,null,"class='form-control'");
                                ?>
                                @error('rtype')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    
                    @if(\vwmldbm\code\is_code_usable('code_c_genre'))
                        <div class="form-group row">
                            <label for="code_c_genre" class="col-md-3 col-form-label text-md-right">{{ $field_arr['c_genre'] }}</label>

                            <div class="col-md-7">
                                <?PHP                                  
                                    echo \vwmldbm\code\print_code('code_c_genre',$book['c_genre'],'c_genre',null,null,null,null,null,"class='form-control'");
                                ?>
                                @error('genre')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    @endif

                    @if(\vwmldbm\code\is_code_usable('code_c_grade'))    
                        <div class="form-group row">
                            <label for="code_c_grade" class="col-md-3 col-form-label text-md-right">{{ $field_arr['c_grade'] }}</label>

                            <div class="col-md-7">
                                <?PHP                                  
                                    echo \vwmldbm\code\print_code('code_c_grade',$book['c_grade'],'c_grade',null,null,null,null,null,"class='form-control'");
                                ?>
                                @error('c_grade')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    @endif

                    @if(\vwmldbm\code\is_code_usable('code_c_category'))
                        <div class="form-group row">
                            <label for="code_c_category" class="col-md-3 col-form-label text-md-right">{{ $field_arr['c_category'] }}</label>

                            <div class="col-md-7">
                                <?PHP                                  
                                    echo \vwmldbm\code\print_code('code_c_category',$book['c_category'],'c_category',null,null,null,null,null,"class='form-control'");
                                ?>
                                @error('c_category')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    @endif

                    @if(\vwmldbm\code\is_code_usable('code_c_category2'))
                        <div class="form-group row">
                            <label for="code_c_category2" class="col-md-3 col-form-label text-md-right">{{ $field_arr['c_category2'] }}</label>

                            <div class="col-md-7">
                                <?PHP                                  
                                    echo \vwmldbm\code\print_code('code_c_category2',$book['c_category2'],'c_category2',null,null,null,null,null,"class='form-control'");
                                ?>
                                @error('c_category2')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    @endif
                    
                        <div class="form-group row">
                            <label for="c_lang" class="col-md-3 col-form-label text-md-right">{{ $field_arr['c_lang'] }}</label>

                            <div class="col-md-7">
                                <?PHP                                  
                                    echo \vwmldbm\code\print_code('vwmldbm_c_lang',null,'c_lang',null,null,null,null,null,"class='form-control'");
                                ?>
                                @error('c_lang')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="hide_yn" class="col-md-3 col-form-label text-md-right">{{ $field_arr['hide_yn'] }}</label>

                            <div class="col-md-7">
                                <span class='form-control border-0'>
                                    <?php
                                        $hide_yn=($book['hide_yn']?$book['hide_yn']:'N');
                                        
                                        echo \vwmldbm\code\print_c_yn('hide_yn',$hide_yn,null,'RADIO',"");
                                    ?>
                                </span>
                                @error('hide_yn')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="e_resource_yn" class="col-md-3 col-form-label text-md-right">{{ $field_arr['e_resource_yn'] }}</label>

                            <div class="col-md-7">
                                <span class='form-control border-0'>
                                  <?PHP   
                                     $e_resource_yn=($book['e_resource_yn']?$book['e_resource_yn']:'N');                              
                                    echo \vwmldbm\code\print_c_yn('e_resource_yn',$e_resource_yn,null,'RADIO'," onChange='e_resource_alert(this)'");
                                  ?>
                                </span>
                                @error('e_resource_yn')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <script>
                                    function e_resource_alert(obj){
                                        if(obj.value=='Y') {
                                            alert("{{__("First save it, then you can upload e-Reousces!")}}");
                                        }
                                    }
                                </script>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="desc" class="col-md-3 col-form-label text-md-right">{{ $field_arr['desc'] }}</label>

                            <div class="col-md-7">
                                <textarea id="desc" type="text" class="form-control @error('desc') is-invalid @enderror" name="desc">
                                    {{$book['desc']}}
                                </textarea>
                                @error('desc')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="publisher" class="col-md-3 col-form-label text-md-right">{{ $field_arr['publisher'] }}</label>

                            <div class="col-md-7">
                                <input id="publisher" type="text" class="form-control @error('publisher') is-invalid @enderror" name="publisher" value="{{ $book['publisher'] }}">

                                @error('publisher')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="url" class="col-md-3 col-form-label text-md-right">{{ $field_arr['url'] }}</label>

                            <div class="col-md-7">
                                <input id="url" type="text" class="form-control @error('url') is-invalid @enderror" name="url" value="{{ $book['url'] }}">

                                @error('url')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="pub_date" class="col-md-3 col-form-label text-md-right">{{ $field_arr['pub_date'] }}</label>

                            <div class="col-md-7">
                                <input id="pub_date" type="text" class="form-control @error('pub_date') is-invalid @enderror" name="pub_date" value="{{ $book['pub_date'] }}">
                                <script>
                                    $('#pub_date').datepicker({
                                        changeMonth: true,
                                        changeYear: true,
                                        yearRange:'1600:<?=date('Y')?>',
                                        showButtonPanel: true,                                        
                                        dateFormat:"yy-mm-dd",
                                    });
                                </script>
                                @error('pub_date')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="isbn" class="col-md-3 col-form-label text-md-right">{{ $field_arr['isbn'] }}</label>

                            <div class="col-md-7">
                                <input id="isbn" type="text" class="form-control @error('isbn') is-invalid @enderror" name="isbn" value="{{ $book['isbn'] }}">

                                @error('isbn')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="eisbn" class="col-md-3 col-form-label text-md-right">{{ $field_arr['eisbn'] }}</label>

                            <div class="col-md-7">
                                <input id="eisbn" type="text" class="form-control @error('eisbn') is-invalid @enderror" name="eisbn" value="{{ $book['eisbn'] }}">

                                @error('eisbn')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="keywords" class="col-md-3 col-form-label text-md-right">{{ $field_arr['keywords'] }}</label>

                            <div class="col-md-7">
                                <input id="keywords" type="text" class="form-control @error('keywords') is-invalid @enderror" name="keywords" value="{{ $book['keywords'] }}">

                                @error('keywords')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="price" class="col-md-3 col-form-label text-md-right">{{ $field_arr['price'] }}</label>

                            <div class="col-md-7">
                                <input id="price" type="text" class="form-control @error('price') is-invalid @enderror" name="price" value="{{ $book['price'] }}">

                                @error('price')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="cover_image" class="col-md-3 col-form-label text-md-right">{{ $field_arr['cover_image'] }}</label>
                            <div class="col-md-7">
                                <input id="cover_image" type="file" class="form-file @error('cover_image') is-invalid @enderror" name="cover_image" onChange='check_file(this)'>
                                @error('cover_image')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-7 offset-md-4">
                                <button type="submit" onClick="check_img_submit()"  class="btn btn-primary">
                                    {{ __('Register') }}
                                </button>
                                &nbsp; 
                                <button type="button" class="btn btn-success" onClick="window.location='{{config('app.url','/wlibrary')}}/book'">
                                    {{ __('List') }}
                                </button>
                            </div>
                            <input type=hidden name="operation">
                            <input type='hidden' name='file_name'>
                            <input name='wise_photo_data' id='wise_photo_data' type='hidden'/>
                        </div>
                        <div class="d-flex">
                            <div class="mx-auto container text-center">                                
                                <center>
                                <canvas id='photoCanvas' width=300 height=200 style='display:block;'></canvas>
                                <script>
                                    var photoCanvas=document.getElementById('photoCanvas');
                                    var phtoImg = new Image();
                                    
                                    function check_img_submit() {
                                        var f_name = document.getElementById('cover_image').files[0];
                                        if(f_name!='') {
                                            f_name_arr=f_name.name.split('.');
                                            var f_ext = f_name_arr[f_name_arr.length - 1].toLowerCase();
                                            
                                            var dataURL;
                                            if(f_ext=='jpg' || f_ext=='jpeg') dataURL=photoCanvas.toDataURL("image/jpeg");
                                            else if(f_ext=='png') dataURL=photoCanvas.toDataURL("image/png");
                                            
                                            document.getElementById('wise_photo_data').value = dataURL;
                                            document.createForm.cover_image.value=''; // empty file because we are not uploading file but string (wise_photo_data)
                                            document.createForm.operation.value='FUPLOAD'; 
                                        }
                                        
                                        document.createForm.submit();
                                    }

                                    function check_file(fobj) {                                       
                                        if(fobj.files[0].name=='') document.createForm.file_name.value='';
                                        else document.createForm.file_name.value=fobj.files[0].name;

                                        var f_name_arr = fobj.files[0].name.split('.');
                                        var f_ext = f_name_arr[f_name_arr.length - 1].toLowerCase();
                                        if(f_ext!='jpg' && f_ext!='jpeg' && f_ext!='png') {
                                            alert('JPG/PNG File only!');
                                            fobj.value='';
                                        }
                                        else pic_load_resize(phtoImg,photoCanvas,<?=$IMG_WIDTH?>);
                                    }                               

                                    function pic_load_resize(img,canvas,width) { 
                                                         
                                        var ctx = canvas.getContext("2d");
                                        var fileObj=document.getElementById('cover_image').files[0];
                                        img.src=URL.createObjectURL(fileObj);
                                        img.onload = function(){
                                            var factor=1;
                                            var iw=img.width;
                                            var ih=img.height;
                                            if(iw>width) factor=width/iw;
                                            canvas.width=iw*factor;
                                            canvas.height=ih*factor;                                        
                                                
                                            ctx.drawImage(img, 0,0, canvas.width, canvas.height);
                                            document.getElementById('rot_bttn').style.display='';
                                        }
                                        
                                    }

                                    function rotate(canvas,deg){
                                        // deg has no effect here
                                        
                                        // 1. Change Canvas Size
                                        // 2. ctx rotation
                                        // 3. drawImage
                                        
                                        // http://jsfiddle.net/remkohdev/7fw66/
                                        
                                        var ctx = canvas.getContext("2d");
                                        
                                        if(typeof rotate.cnt =='undefined') rotate.cnt=0;
                                        rotate.cnt++;

                                        h=canvas.height;
                                        w=canvas.width;

                                        canvas.width=h;
                                        canvas.height=w;

                                        if(rotate.cnt%4==1){
                                            ctx.translate(h,0);
                                            ctx.rotate(90 * Math.PI / 180); 
                                        }
                                        else if(rotate.cnt%4==2){
                                            ctx.translate(h/2,w/2);
                                            ctx.rotate(180 * Math.PI / 180); 
                                            ctx.translate(-h/2,-w/2);
                                        }
                                        else if(rotate.cnt%4==3){
                                            ctx.translate(w-h,w);
                                            ctx.rotate(270 * Math.PI / 180); 
                                            ctx.translate(0,-(w-h));
                                        }
                                        
                                        if(rotate.cnt%2==1){
                                            ctx.drawImage(phtoImg,0,0,w,h);
                                        }
                                        else ctx.drawImage(phtoImg,0,0,h,w);
                                    }
                                 </script>
                                </center>        
                            </div>                                                   
                          </div>                        
                          <div class='container text-center'>
                            <button id='rot_bttn' class='btn btn-outline-primary' type='button' onClick="rotate(document.getElementById('photoCanvas'),90)" style='display:none;'>
                                {{__('Rotate')}}
                            </button>
                          </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
