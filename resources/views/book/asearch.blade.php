<?php
    // Pre-loading the code values for performance
    $field_arr=array();
    \vwmldbm\code\get_field_name_all('book',$field_arr);

    $field_arr2=array();
    \vwmldbm\code\get_field_name_all('book_copy',$field_arr2);

    $c_rstatus_arr=array();
    \vwmldbm\code\get_code_name_all($c_rstatus_arr,'code_c_rstatus');

    $title=null;
    $author=null;
    $publisher=null;
    $isbn=null;
    $eisbn=null;
    $keywords=null;
    $c_rtype=null;
    $c_genre=null;
    $c_grade=null;
    $c_category=null;
    $c_lang=null;
    $e_resource_yn=null;
    $pub_date1=null;
    $pub_date2=null;
    
    if(isset($request)) {
        $title=$request->title;
        $author=$request->author;
        $publisher=$request->publisher;
        $isbn=$request->isbn;
        $eisbn=$request->eisbn;
        $keywords=$request->keywords;
        $c_rtype=$request->c_rtype;
        $c_genre=$request->c_genre;
        $c_grade=$request->c_grade;
        $c_category=$request->c_category;
        $c_category2=$request->c_category2;
        $c_lang=$request->c_lang;
        $e_resource_yn=$request->e_resource_yn;
        $pub_date1=$request->pub_date1;
        $pub_date2=$request->pub_date2;
    }
?>

@extends('layouts.root')
@section('content')
<form name='form1' id='form1'  style='width:100%;'>
  <div class="container">
    <div class="card">
        <div class="card-header font-weight-bold">{{ __('Advanced Search') }}</div>
        <div class="card-body">
            <div class="form-group row">
                <label for="rtype" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['c_rtype'] }}</label>

                <div class="col-md-7 border-0">
                    <?PHP
                        if($c_rtype) {
                            $rtype=implode(",",$c_rtype);
                        }
                        else $rtype=null;
                        echo \vwmldbm\code\print_code('code_c_rtype',$rtype,'c_rtype',null,true,null,"CHECKBOX_ALL",null,"class='form-check-input' onClick='check_checkbox(this)'");
                    ?>                 
                </div>
            </div>

            <div class="form-group row">
                <label for="genre" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['c_genre'] }}</label>

                <div class="col-md-7  border-0">                    
                    <?PHP
                        if($c_genre) {
                            $genre=implode(",",$c_genre);
                        }
                        else $genre=null;
                        echo \vwmldbm\code\print_code('code_c_genre',$genre,'c_genre',null,true,null,"CHECKBOX_ALL",null,"class='form-check-input' onClick='check_checkbox(this)'");
                    ?> 
                                   
                </div>
            </div>
        
        @if(\vwmldbm\code\is_code_usable('c_grade'))
            <div class="form-group row">
                <label for="genre" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['c_grade'] }}</label>

                <div class="col-md-7 form-control border-0">
                    <?PHP
                        if($c_grade) {
                            $grade=implode(",",$c_grade);
                        }
                        else $c_grade=null;
                        echo \vwmldbm\code\print_code('code_c_grade',$grade,'c_grade',null,true,null,"CHECKBOX_ALL",null,"class='form-check-input' onClick='check_checkbox(this)'");
                    ?>                   
                </div>
            </div>
        @endif
        
        @if(\vwmldbm\code\is_code_usable('c_category'))
            <div class="form-group row">
                <label for="category" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['c_category'] }}</label>

                <div class="col-md-7 form-control border-0">
                    <?PHP
                        if($c_category) {
                            $category=implode(",",$c_category);
                        }
                        else $category=null;
                        echo \vwmldbm\code\print_code('code_c_category',$category,'c_category',null,true,null,"CHECKBOX_ALL",null,"class='form-check-input' onClick='check_checkbox(this)'");
                    ?>                   
                </div>
            </div>
        @endif
        
        @if(\vwmldbm\code\is_code_usable('c_category2'))
            <div class="form-group row">
                <label for="category2" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['c_category2'] }}</label>

                <div class="col-md-7 form-control border-0">
                    <?PHP
                        if($c_category2) {
                            $category2=implode(",",$c_category2);
                        }
                        else $category2=null;
                        echo \vwmldbm\code\print_code('code_c_category2',$category2,'c_category2',null,true,null,"CHECKBOX_ALL",null,"class='form-check-input' onClick='check_checkbox(this)'");
                    ?>                   
                </div>
            </div>
        @endif

            <div class="form-group row">
                <label for="c_lang" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['c_lang'] }}</label>

                <div class="col-md-7 form-control border-0">
                    <?PHP                      
                        echo \vwmldbm\code\print_code('vwmldbm_c_lang',$c_lang,'c_lang',null,true,null,null,null,"class='form-control'");
                    ?>                   
                </div>
            </div>


            <div class="form-group row">
                <label for="e_resource_yn" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['e_resource_yn'] }}</label>

                <div class="col-md-7">
                    <span class='form-control border-0'>                            
                        <?PHP
                        echo \vwmldbm\code\print_c_yn('e_resource_yn',$e_resource_yn,null,'RADIO',null,null);
                        ?>
                    </span>
                    @error('e_resource_yn')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="title" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['title'] }}</label>

                <div class="col-md-7">
                    <input id="title" type="text" class="form-control" name="title"  value='{{$title}}' autofocus>
                </div>
            </div>

            <div class="form-group row">
                <label for="author" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['author'] }}</label>

                <div class="col-md-7">
                    <input id="author" type="text" class="form-control" name="author"  value='{{$author}}'>
                </div>
            </div>

            <div class="form-group row">
                <label for="publisher" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['publisher'] }}</label>

                <div class="col-md-7">
                    <input id="publisher" type="text" class="form-control" name="publisher" value="{{ $publisher }}">
                </div>
            </div>

            <div class="form-group row">
                <label for="isbn" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['isbn'] }}</label>

                <div class="col-md-7">
                    <input id="isbn" type="text" class="form-control" name="isbn" value="{{ $isbn }}">
                </div>
            </div>

            <div class="form-group row">
                <label for="eisbn" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['eisbn'] }}</label>

                <div class="col-md-7">
                    <input id="eisbn" type="text" class="form-control" name="eisbn" value="{{ $eisbn }}">
                </div>
            </div>

            <div class="form-group row">
                <label for="keywords" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['keywords'] }}</label>

                <div class="col-md-7">
                    <input id="keywords" type="text" class="form-control" name="keywords" value="{{ $keywords }}">
                </div>
            </div>
            
            <div class="form-group row">
                <label for="pub_date" class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $field_arr['pub_date'] }}</label>

                <div class="col-md-7">
                    <input id="pub_date1" type="text" onChange="date1_changed(this)" autocomplete='off' size=8 name="pub_date1" value="{{ $pub_date1 }}">
                        ~
                    <input id="pub_date2" type="text" onChange="check_date(this)" autocomplete='off' size=8 name="pub_date2" value="{{ $pub_date2 }}">
                    <script>
                        $('#pub_date1').datepicker({
                            changeMonth: true,
                            changeYear: true,
                            yearRange:'1600:<?=date('Y')?>',
                            showButtonPanel: true,                                        
                            dateFormat:"yy-mm-dd",
                        });

                        $('#pub_date2').datepicker({
                            changeMonth: true,
                            changeYear: true,
                            yearRange:'1600:<?=date('Y')?>',
                            showButtonPanel: true,                                        
                            dateFormat:"yy-mm-dd",
                        });


                        function date1_changed(d1){
                            var d2=document.getElementById('pub_date2');
                            if(d2.value < d1.value) {
                                d2.value=d1.value;
                                d1.style.background='';
                                d2.style.background='';
                            }
                        }

                        function check_date(tobj) {
                            var d1=document.getElementById('pub_date1');
                            var d2=document.getElementById('pub_date2');
                            
                            d1.style.background='';
                            d2.style.background='';
                            
                            if(tobj.id==d1.id){ //datepickerDate1
                                if(d2.value && d1.value>d2.value) {
                                    d1.value='';
                                    d1.style.background='yellow';
                                    d1.focus();
                                    alert("{{__("Wrong Date!")}}");
                                }
                                else if(d1.value!='' && d2.value=='')
                                    $('#pub_date2').datepicker('setDate', d1.value);
                            }
                            else { // datepickerDate2
                                if(d1.value=='') {
                                    d2.value='';
                                }
                                else if(d2.value!='' && d1.value>d2.value) {
                                    d2.value='';
                                    alert("{{__("Wrong Date!")}}");
                                    d2.style.background='yellow';
                                    d2.focus();
                                }		
                            }
                        }
                    </script>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-md-10 text-md-center">
                    <button type="submit" class="btn btn-primary">
                        {{ __('Search') }}
                    </button>
                    &nbsp; 
                    <button type="button" onClick='clear_form()' class="btn btn-info">
                        {{ __('Clear') }}
                    </button>
                </div>
            </div>

            <script>
                function check_checkbox(obj){
                    if(!obj.checked){ // check if all the boxes were not checked then cancel it
                        var rtype=document.getElementsByName(obj.name);
                        var at_least_one_checked=false;
                        for(i of rtype){
                            if(i.checked) at_least_one_checked=true;
                        }
                        if(!at_least_one_checked) {                                    
                            alert("{{__("At least one should be checked!")}}");
                            obj.checked=true;
                        }
                    }
                }

                function clear_form(){
                    $('#form1').find('input').each(function(){                        
                        if(this.type=='text') {
                            this.value="";
                        }
                        else if(this.type=='radio') {
                            if(document.querySelector('input[name='+this.name+']:checked') !=null)
                                document.querySelector('input[name='+this.name+']:checked').checked = false;
                        }
                        else if(this.type=='checkbox'){
                            var box=document.getElementsByName(this.name); 
                            for(i of box){
                                if(!i.checked) i.checked=true;
                            } 
                        }
                    });

                    $('#form1').find('select').each(function(){                          
                        this.selectedIndex=0;
                    });
                }
            </script>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-12 text-md-center">
            @if(isset($books))
                @include('book.list_content',['no_add_btt'=>true]) 
            @endif
        </div>
    </div>
  </div>
</form>   
@endsection
