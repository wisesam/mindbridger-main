<?php
    // Pre-loading the code values for performance
    $field_arr=array();
    \vwmldbm\code\get_field_name_all('book',$field_arr);

    $field_arr2=array();
    \vwmldbm\code\get_field_name_all('book_copy',$field_arr2);

    $c_rstatus_arr=array();
    \vwmldbm\code\get_code_name_all($c_rstatus_arr,'code_c_rstatus');

    $IMG_WIDTH =600; // cover photo image resolution

    $post_max_size=\wlibrary\code\post_max_size('bytes');
    $upload_max_filesize=\wlibrary\code\upload_max_filesize('bytes');
    $max_file_uploads=\wlibrary\code\max_file_uploads();

    $ADM_FILE_SIZE_LIMIT=250000000; // default is 110MB
    if($post_max_size>$ADM_FILE_SIZE_LIMIT) {
		$post_max_size=$ADM_FILE_SIZE_LIMIT;
		$upload_max_filesize=$ADM_FILE_SIZE_LIMIT;		
	}
    
    $MAX_FSIZE=$upload_max_filesize;  // set this to limit size
    $num_already_files=0; // global variable for checking number of already uploaded files
  
    $perm['R'] ='Y'; // Read permission
    $perm['M'] ='Y'; // Modificiation permission
    $perm['D'] ='Y'; // Delete permission
	
?>

@extends('layouts.root')
@section('content')

<!-- to be refactored-->
<!-- i18n (optional, used by the viewer UI) -->
<link rel="resource" type="application/l10n"
      href="<?= $_SESSION['app.url'] ?>/lib/pdf.js/web/locale/locale.properties">

<!-- Core library (v2.x UMD build) -->
<script src="<?= $_SESSION['app.url'] ?>/lib/pdf.js/build/pdf.js"></script>

<script>
  // pdf.js v2.x exposes itself here; create the familiar alias:
  window.pdfjsLib = window['pdfjs-dist/build/pdf'] || window.pdfjsLib || window.PDFJS;

  if (!window.pdfjsLib) {
    console.error('pdf.js failed to load: check the path to build/pdf.js');
  } else {
    // Point the worker to your local copy (must be same-origin)
    pdfjsLib.GlobalWorkerOptions.workerSrc =
      "<?= $_SESSION['app.url'] ?>/lib/pdf.js/build/pdf.worker.js";
  }
</script>

<!-- Your code that uses pdfjsLib (or the stock viewer) -->
<script src="<?= $_SESSION['app.url'] ?>/lib/pdf.js/web/viewer.js"></script>


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
        <div class="col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header">{{ __('Edit Book') }}
                    &nbsp; <img style='cursor:pointer;' src="{{config('app.url','/wlibrary')}}/image/button/doc2.png" class="zoom" onClick="window.location='{{config('app.url','/wlibrary')."/book/".$book->id}}'">
                </div>
                <div class="card-body">
                    <form method="POST" name='editForm' id='pform' action="{{config('app.url','/wlibrary')."/book/".$book->id}}" enctype="multipart/form-data">
                        @csrf 
                        <input type='hidden' name='_method' value='PUT'>
                        <input type='hidden' name="progress_up_flag">                   
                        <input type='hidden' name="id" value='{{$book->id}}'>                   
                        <input type='hidden' name="del_file">                   
                        <div class="form-group row">
                            <label for="title" class="col-md-3 col-form-label text-md-right">{{ $field_arr['title'] }}</label>

                            <div class="col-md-7">
                                <input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ $book->title }}" required autocomplete="{{ old('title') }}" autofocus>
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
                                <input id="author" type="text" class="form-control @error('author') is-invalid @enderror" name="author" value="{{ $book->author }}" autocomplete="{{ old('author') }}" autofocus>

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
                                     echo \vwmldbm\code\print_code('code_c_rtype',$book->c_rtype,'c_rtype',null,null,null,null,null,"class='form-control'");
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
                                    echo \vwmldbm\code\print_code('code_c_genre',$book->c_genre,'c_genre',null,null,null,null,null,"class='form-control'");
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
                                    echo \vwmldbm\code\print_code('code_c_grade',$book->c_grade,'c_grade',null,null,null,null,null,"class='form-control'");
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
                                    echo \vwmldbm\code\print_code('code_c_category',$book->c_category,'c_category',null,null,null,null,null,"class='form-control'");
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
                                    echo \vwmldbm\code\print_code('code_c_category2',$book->c_category2,'c_category2',null,null,null,null,null,"class='form-control'");
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
                                    echo \vwmldbm\code\print_code('vwmldbm_c_lang',$book->c_lang,'c_lang',null,null,null,null,null,"class='form-control'");
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
                                  <?PHP                                  
                                    echo \vwmldbm\code\print_c_yn('hide_yn',$book->hide_yn,null,'RADIO',"");
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
                            <label for="hide_from_guest_yn" class="col-md-3 col-form-label text-md-right">{{ $field_arr['hide_from_guest_yn'] }}</label>

                            <div class="col-md-7">
                                <span class='form-control border-0'>
                                  <?PHP                                  
                                    echo \vwmldbm\code\print_c_yn('hide_from_guest_yn',$book->hide_from_guest_yn,null,'RADIO',"");
                                  ?>
                                </span>
                                @error('hide_from_guest_yn')
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
                                    echo \vwmldbm\code\print_c_yn('e_resource_yn',$book->e_resource_yn,null,'RADIO',null,'Y_BLUE');
                                  ?>
                                </span>
                                @error('e_resource_yn')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        
                        <?PHP                           
                            $video_txt = edit_list_videos($book,$perm,$book->rid);
                        ?>
                        @if($video_txt!='')
                            <div class="form-group row">
                                <label for="video" class="col-md-3 col-form-label text-md-right">{{__('Video') }}</label>
                                <div class="col-md-7">
                                    <?=$video_txt?>                            
                                </div>
                            </div>
                        @endif

                        <div class="form-group row">
                            <label for="e-Resources" class="col-md-3 col-form-label text-md-right">
                                {{ __('e-Resources') }}
                                <?PHP 
                                    $post_max_size_mb=round($post_max_size/1000000,2);
                                ?>
                                <br><span style='color:magenta;'>({{__('Max File Size')}}: {{$post_max_size_mb}} MB) </span>
                            </label>

                            <div class="col-md-7">
                                <?PHP 
                                     echo edit_list_old_files($book,$perm,$book->rid); 
                             
                                ?>
                                <input name='openFile[]' id='filesToUpload' type='file' multiple='' class='dnd_file' onchange='check_ext(this);'>
                                <ol id='fileList'></ol>
                                
                                <div class='progress' id='progress_div' style='height:24px;'>
                                    <div class='bar' id='bar1'></div>
                                    <div class='percent' id='percent1'>0%</div>
                                </div>                                        
                                <script>
                                    var post_max_size=<?=$post_max_size?>;
                                    var upload_max_filesize=<?=$upload_max_filesize?>;
                                    var max_file_uploads=<?=($max_file_uploads-$num_already_files)?>;

                                    var input;
                                    var list;
                                    var file_ok;

                                    function progress_upload(mode) {
                                        var f=document.getElementById('pform');                                        
                                        if(f.filesToUpload.value=='') { // no file upload
                                            document.getElementById('pform').action="{{config('app.url','/wlibrary')."/book/".$book->id}}"; // reset the action. Double check.
                                            return;
                                        }                                        
                                        f.operation.value="Modify";
                                        f.action='{{config('app.url')}}/lib/progress_up.php';
                                        var bar = $('#bar1');
                                        var percent = $('#percent1');

                                        $('#pform').ajaxForm({
                                            beforeSubmit: function() {                                                
                                                document.getElementById('progress_div').style.display='block';                                                
                                                var percentVal = '0%';
                                                bar.width(percentVal)
                                                percent.html(percentVal);
                                                f.action='{{config('app.url')}}/lib/progress_up.php';                                                
                                            },

                                            uploadProgress: function(event, position, total, percentComplete) {
                                                var percentVal = percentComplete + '%';
                                                bar.width(percentVal)
                                                percent.html(percentVal);
                                                
                                                document.getElementById('filesToUpload').focus(); // to show the progress bar after submitting (scroll up)
                                            },
                                            
                                            success: function() {
                                                var percentVal = '100%';
                                                bar.width(percentVal)
                                                percent.html(percentVal);
                                                f.action="{{config('app.url','/wlibrary')."/book/".$book->id}}"; // reset the action. Double check.
                                                f.filesToUpload.value='';
                                            },

                                            complete: function(xhr) {
                                                if(xhr.responseText)
                                                {
                                                    console.log(xhr.responseText);
                                                    if(xhr.responseText!='MOD_SUCCESS') return;
                                                    //document.getElementById('output_image').innerHTML=xhr.responseText;
                                                    f.action="{{config('app.url','/wlibrary')."/book/".$book->id}}"; // reset the action. Double check.
                                                    f.progress_up_flag.value='Y';
                                                    if(mode=='HANDLE_COVER_IMG_TOO') {                                                        
                                                        var f_name = document.getElementById('cover_image').files[0];

                                                        if(typeof f_name !='undefined') {
                                                            f_name_arr=f_name.name.split('.');
                                                            var f_ext = f_name_arr[f_name_arr.length - 1].toLowerCase();
                                                            
                                                            var dataURL;
                                                            if(f_ext=='jpg' || f_ext=='jpeg') dataURL=photoCanvas.toDataURL("image/jpeg");
                                                            else if(f_ext=='png') dataURL=photoCanvas.toDataURL("image/png");
                                                            
                                                            document.getElementById('wise_photo_data').value = dataURL;
                                                            f.cover_image.value=''; // empty file because we are not uploading file but string (wise_photo_data)
                                                            //f.operation.value='FUPLOAD';
                                                        }                                                
                                                    }
                                                    f.submit();
                                                }
                                            }
                                        });
                                    }
                                    
                                    function toggle_file_sub(obj){
                                        if(obj.value!='') document.editForm.action='progress_up.php'; 
                                        else document.editForm.action=''; 
                                    }

                                    function check_ext(fobj) {
                                        if(fobj.files.length<1) return;
                                        for(var i=0; i<fobj.files.length; i++){
                                            var f_arr=fobj.files[i].name.split('.');
                                            var f_ext=f_arr[f_arr.length -1 ].toLowerCase();
                                            if(f_ext!='pdf' && f_ext!='mp4' && f_ext!='ogg'  && f_ext!='webm') {
                                                alert('{{__('PDF, mp4, ogg, webm only!')}}');
                                                fobj.value='';
                                                document.getElementById('fileList').innerHTML='';
                                                return;
                                            }
                                        }
                                        list_files();
                                        toggle_file_sub(fobj);
                                    }

                                    function list_files() {
                                        file_ok=true;
                                        input = document.getElementById('filesToUpload');
                                        list = document.getElementById('fileList');
                                        var total_size=0;
                                        //empty list for now...
                                        while (list.hasChildNodes()) {
                                            list.removeChild(list.firstChild);
                                        }
                                        //for every file...
                                        for (var x = 0; x < input.files.length; x++) {
                                            //add to list
                                            var li = document.createElement('li');
                                            li.innerHTML = input.files[x].name;
                                            var fsize=input.files[x].size;                                        
                                                                               
                                            if(total_size>post_max_size) {
                                                file_ok=false;
                                            }
                                            if(input.files.length>max_file_uploads) {
                                                file_ok=false;
                                            }
                                                
                                            var fsizeVal;
                                            if(Math.ceil(fsize/1000)>1000) fsizeVal=Math.ceil(fsize/1000000) + " MB";
                                            else fsizeVal=Math.ceil(fsize/1000) + " KB";
                                            
                                            
                                            total_size+=fsize;
                                            if(fsize > upload_max_filesize) {
                                                li.innerHTML+= ":<font color=red> "+fsizeVal+"</font>";
                                                li.innerHTML+="<b> > <?=number_format($upload_max_filesize/1000000)."MB"?></b>";
                                                file_ok=false;			
                                                alert("{{__('File size is too big!')}} ("+Math.ceil(total_size/1000000)+" MB)");
                                            }
                                            else li.innerHTML+= ":<font color=blue> "+fsizeVal+"</font>";

                                            if(file_ok==false) {
                                                input.value=null;                                                
                                            }
                                            list.append(li);
                                        }
                                        
                                        if(total_size>post_max_size) {
                                            alert("{{__('Total file size is too big!')}} ("+Math.ceil(total_size/1000000)+" MB)");                                            
                                        }
                                        if(input.files.length>max_file_uploads) {
                                            alert("{{__('Number of files is too many!')}} ("+x+")");                                            
                                        }                                      
                                    }

                                    function del_file_func(val) {
                                        if(confirm("{{__("Would you like to delete?")}} "+val)) {
                                            document.editForm.del_file.value=val;
                                            document.editForm.operation.value='DEL_FILE';
                                            document.editForm.submit();
                                        }
                                    }
                                </script>
                                @error('e_book')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Table of Contents (TOC) -->
                        <div class="form-group row">
                            <label for="toc" class="col-md-3 col-form-label text-md-right">{{ $field_arr['toc'] }}</label>
                            <div class="col-md-7">
                                <textarea id="toc"
                                    class="form-control @error('toc') is-invalid @enderror"
                                    name="toc"
                                    rows="10"
                                    autocomplete="off"
                                    autofocus>{{ old('toc', $book->toc ?? "Go into all the world and preach the gospel to all creation. — Mark 16:15") }}
                                </textarea>

                                @error('toc')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        
                        
                    @if(!empty($book->files))
                        <!-- Table of Contents (Auto_TOC) -->
                        <div class="form-group row">
                            <label for="auto_toc" class="col-md-3 col-form-label text-md-right">
                            <div>{{ $field_arr['auto_toc'] }}</div>
                            <div>
                                <button
                                    type="button"
                                    id="btn-auto-toc"
                                    class="btn btn-primary"
                                    data-url="{{ route('book.auto_toc', ['book' => $book->id]) }}">
                                    {{ __('Auto ToC') }}
                                </button>
                            </div>

                        <!-- PDF Viewer URL -->
                            <?php
                            // Ensure the PDF URL is correctly formed
                                $rfiles=explode(';',$book->rfiles);        
                                $pdfUrl = $_SESSION['app.url']."/lib/get_book_file.php?rf={$rfiles[0]}&rid={$book->rid}";
                            ?>
                            <div class="mb-2">
                                <button id="btn-auto_toc_js" class="btn btn-secondary"
                                    data-pdf-url="{{ $pdfUrl }}">Extract ToC
                                </button>

                                <script>
                                    (async function () {
                                        const btn = document.getElementById('btn-auto_toc_js');
                                        if (!btn) return;

                                        async function resolveDestToPageNum(pdf, dest) {
                                            let explicit = dest;
                                            if (typeof dest === 'string') {
                                            explicit = await pdf.getDestination(dest); // named -> explicit
                                            }
                                            if (Array.isArray(explicit) && explicit[0]) {
                                            const pageIndex = await pdf.getPageIndex(explicit[0]); // 0-based
                                            return pageIndex + 1;
                                            }
                                            return null;
                                        }

                                        async function flattenOutline(pdf, items, level = 1, acc = []) {
                                            for (const it of items) {
                                            const page = it.dest ? await resolveDestToPageNum(pdf, it.dest) : null;
                                            acc.push({ title: (it.title || '').trim(), page, level });
                                            if (it.items && it.items.length) await flattenOutline(pdf, it.items, level + 1, acc);
                                            }
                                            return acc;
                                        }

                                        btn.addEventListener('click', async () => {
                                            const url = btn.dataset.pdfUrl;
                                            if (!url) return alert('Missing PDF URL');

                                            btn.disabled = true; const label = btn.innerHTML; btn.innerHTML = 'Reading…';
                                            try {
                                                // Old-phone friendly options
                                                const loadingTask = pdfjsLib.getDocument({
                                                    url,
                                                    disableAutoFetch: true,  // reduce memory/network
                                                    disableStream: true      // simpler path for old browsers
                                                });
                                                const pdf = await loadingTask.promise;

                                                let outline = await pdf.getOutline();
                                                if (!outline) outline = [];

                                                const flat = await flattenOutline(pdf, outline);
                                                const tocText = flat.map((x, i) =>
                                                    `${'  '.repeat(Math.max(0, x.level-1))}${i+1}. ${x.title}${x.page ? ' (p.'+x.page+')' : ''}`
                                                ).join('\n');

                                                document.getElementById('auto_toc').innerHTML = tocText;
                                        
                                            } catch (e) {
                                                console.error(e); alert('ToC read failed: ' + e.message);
                                            } finally {
                                                btn.disabled = false; btn.innerHTML = label;
                                            }
                                        });
                                    })();
                                </script>
                            </div>
                        <!-- End PDF Viewer URL -->

                            </label>

                            <div class="col-md-7">
                               <span id='auto_toc'>
                                {{old('auto_toc', json_encode($book->auto_toc ?? []))}}
                               </span>

                                @error('auto_toc')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror

                               
                            </div>

                            <script>
                            document.getElementById('btn-auto-toc').addEventListener('click', async function () {
                                const btn = this;
                                const url = btn.dataset.url;

                                const orig = btn.innerHTML;
                                btn.disabled = true;
                                btn.innerHTML = 'Generating…';

                                try {
                                    const res = await fetch(url, {
                                        method: 'GET',
                                        headers: { 'Accept': 'application/json' }
                                    });
                                    if (!res.ok) throw new Error(`HTTP ${res.status}`);

                                    const data = await res.json();
                                    document.getElementById('auto_toc').innerHTML = JSON.stringify(data.auto_toc ?? {});
                                } catch (e) {
                                    alert('Failed to generate Auto ToC: ' + e.message);
                                } finally {
                                    btn.disabled = false;
                                    btn.innerHTML = orig;
                                }
                            });
                            </script>

                        </div>
                    @endif
                        <div class="form-group row">
                            <label for="rdonly_pdf_yn" class="col-md-3 col-form-label text-md-right">{{ $field_arr['rdonly_pdf_yn'] }}</label>

                            <div class="col-md-7">
                                <span class='form-control border-0'>
                                  <?PHP                                  
                                    echo \vwmldbm\code\print_c_yn('rdonly_pdf_yn',$book->rdonly_pdf_yn,null,'RADIO',"");
                                  ?>
                                </span>
                                @error('rdonly_pdf_yn')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="rdonly_video_yn" class="col-md-3 col-form-label text-md-right">{{ $field_arr['rdonly_video_yn'] }}</label>

                            <div class="col-md-7">
                                <span class='form-control border-0'>
                                  <?PHP                                  
                                    echo \vwmldbm\code\print_c_yn('rdonly_video_yn',$book->rdonly_video_yn,null,'RADIO',"");
                                  ?>
                                </span>
                                @error('rdonly_video_yn')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="e_res_af_login_yn" class="col-md-3 col-form-label text-md-right">{{ $field_arr['e_res_af_login_yn'] }}</label>

                            <div class="col-md-7">
                                <span class='form-control border-0'>
                                  <?PHP                                  
                                    echo \vwmldbm\code\print_c_yn('e_res_af_login_yn',$book->e_res_af_login_yn,null,'RADIO',"");
                                  ?>
                                </span>
                                @error('e_res_af_login_yn')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="desc" class="col-md-3 col-form-label text-md-right">{{ $field_arr['desc'] }}</label>

                            <div class="col-md-7">
                                <textarea id="desc"  class="form-control @error('desc') is-invalid @enderror" name="desc" autocomplete="{{ old('desc') }}" autofocus>
                                    {{ $book->desc }}
                                </textarea>
                                @error('desc')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="url" class="col-md-3 col-form-label text-md-right">{{ $field_arr['url'] }}</label>

                            <div class="col-md-7">
                                <input id="url" type="text" class="form-control @error('url') is-invalid @enderror" name="url" value="{{ $book->url }}">

                                @error('url')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="publisher" class="col-md-3 col-form-label text-md-right">{{ $field_arr['publisher'] }}</label>

                            <div class="col-md-7">
                                <input id="publisher" type="text" class="form-control @error('publisher') is-invalid @enderror" name="publisher" value="{{ $book->publisher }}" autocomplete="{{ old('publisher') }}" autofocus>

                                @error('publisher')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="pub_date" class="col-md-3 col-form-label text-md-right">{{ $field_arr['pub_date'] }}</label>

                            <div class="col-md-7">
                                <?PHP
                                    $pub_date="";
                                    if($book->pub_date) $pub_date=$book->pub_date->format('Y-m-d');
                                ?>
                                <input id="pub_date" type="text" autocomplete='off' class="form-control @error('pub_date') is-invalid @enderror" name="pub_date" value="{{ $pub_date }}"  autocomplete="{{ old('pub_date') }}" autofocus>
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
                                <input id="isbn" type="text" class="form-control @error('isbn') is-invalid @enderror" name="isbn" value="{{ $book->isbn }}" autocomplete="{{ old('isbn') }}" autofocus>

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
                                <input id="eisbn" type="text" class="form-control @error('eisbn') is-invalid @enderror" name="eisbn" value="{{ $book->eisbn }}" autocomplete="{{ old('eisbn') }}" autofocus>

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
                                <input id="keywords" type="text" class="form-control @error('keywords') is-invalid @enderror" name="keywords" value="{{ $book->keywords }}" autocomplete="{{ old('Keywords') }}" autofocus>

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
                                <input id="price" type="text" class="form-control @error('price') is-invalid @enderror" name="price" value="{{ $book->price }}" autocomplete="{{ old('price') }}" autofocus>

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
                                @if(!$book->cover_image)
                                    <input id="cover_image" type="file" class="form-file @error('cover_image') is-invalid @enderror" name="cover_image" value="{{ old('cover_image') }}" autofocus  onChange='check_file(this)'>
                                    @error('cover_image')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                @else 
                                    <img onClick='open_cover_img(this)' style='cursor:pointer;' src='{{config('app.url','/nwlibrary')}}/storage/cover_images/{{$_SESSION['lib_inst']}}/{{$book->cover_image}}' height='200'>
                                    &nbsp; <a href="javascript:confirm_del()"><img src='{{config('app.url','/nwlibrary')}}/image/button/trash.png' height='40' class='wlibrary_icon'></a>
                                    <input type='hidden' name='del_cover_image'>
                                    <script>
                                        function confirm_del(){
                                            if(confirm("{{__('messages.delete')}}")){
                                                document.editForm.del_cover_image.value="DEL";
                                                document.editForm.submit();
                                            }
                                        }
                                   
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
                                @endif
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-7 offset-md-4">
                                <button type="submit" onClick="check_img_submit()" class="btn btn-primary">
                                    {{ __('Submit') }}
                                </button>
                                &nbsp; 
                                <button type="button" class="btn btn-success" onClick="window.location='{{config('app.url','/wlibrary')}}/book'">
                                    {{ __('List') }}
                                </button>
                            </div>
                            <input type='hidden' name="operation">
                            <input name='file_name' type='hidden'/>
                            <input name='wise_photo_data' id='wise_photo_data' type='hidden'/>
                        </div>

                        @if(!$book->cover_image)
                          <div class="d-flex">
                            <div class="mx-auto container text-center">                                
                                <center>
                                <canvas id='photoCanvas' width='300' height='200' style='display:block;'></canvas>
                                <script>
                                    var photoCanvas=document.getElementById('photoCanvas');
                                    var phtoImg = new Image();
                                    
                                    function check_img_submit() {
                                        if(document.editForm['openFile[]'].value!='') { // Check if e-resource file was selected
                                            progress_upload('HANDLE_COVER_IMG_TOO'); 
                                            return; 
                                        }
                                        var f_name = document.getElementById('cover_image').files[0];
                                        if(typeof f_name !='undefined') {
                                            f_name_arr=f_name.name.split('.');
                                            var f_ext = f_name_arr[f_name_arr.length - 1].toLowerCase();
                                            
                                            var dataURL;
                                            if(f_ext=='jpg' || f_ext=='jpeg') dataURL=photoCanvas.toDataURL("image/jpeg");
                                            else if(f_ext=='png') dataURL=photoCanvas.toDataURL("image/png");
                                            
                                            document.getElementById('wise_photo_data').value = dataURL;
                                            document.editForm.cover_image.value=''; // empty file because we are not uploading file but string (wise_photo_data)
                                            document.editForm.operation.value='FUPLOAD'; 
                                          
                                        }
                                       document.editForm.submit();
                                    }

                                    function check_file(fobj) {
                                        if(fobj.files[0].name=='') document.editForm.file_name.value='';
                                        else document.editForm.file_name.value=fobj.files[0].name;
                                        
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
                        @else 
                            <script>
                                function check_img_submit(){                                   
                                    if(document.editForm['openFile[]'].value!='') progress_upload(null);
                                    else document.editForm.submit();
                                }
                            </script>
                        @endif                       
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container col-md-8"> 
    <table class="table table-striped">
      <tr>
        <th> </th>
        <th>{{$field_arr2["barcode"]}}</th>
        <th>{{$field_arr2["call_no"]}}</th>
        <th>{{$field_arr2["location"]}}</th>
        <th>{{$field_arr2["c_rstatus"]}}</th>
        <th>
            <button type='button' class='btn btn-outline-info' onClick="window.location='{{config('app.url','/wlibrary')}}/book_copy/create/{{$book->id}}'">
                    + 
            </button>           
        </th>
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
            <td>
                <a href="{{config('app.url','/wlibrary')}}/book_copy/{{$bc['id']}}/edit">
                    <img src="{{config('app.url','/wlibrary')}}/image/button/mod_bw.png" class="zoom">
                </a>
            </td>
        </tr>
      @endforeach
    </table>
</div>
<br><br>
@endsection