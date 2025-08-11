<?PHP
function show_list_old_files($book,$perm,$rid){
	global $num_already_files;
	$isAdmin = (Auth::check() && Auth::user()->isAdmin()) ? true : false;
	if(!$book->files) return;
	$result=null;
	$files=explode(';',$book->files);
	
	if(is_array($files)) $num_already_files=count($files)-1;
	
    $rfiles=explode(';',$book->rfiles);
    $cnt=1;
	foreach($files as $key =>$val) {
		$onClickDel=null;
		$pdfDownTag=null; // when read only PDF is not set
		if($val) {
            $video_down_enabled=true;
			if($perm['R']=='Y') {
                $fpath=config('app.root')."/storage/app/ebook/{$_SESSION['lib_inst']}/{$book->rid}/{$rfiles[$key]}";
                if(file_exists($fpath))
                    $fsize=\wlibrary\code\format_fsize(filesize($fpath),'MB',1)."MB";
                else $fsize="N/A";
				$fext = strtolower(substr($val,-3,3)); 
				
				$down_tag=null;
                $down_tag="<a href=\"";
                if($fext=='php') continue; // security
				else if($fext=='pdf'){ 
					$fpath = config('app.root') . "/storage/app/ebook/{$_SESSION['lib_inst']}/{$book->rid}/{$rfiles[$key]}";
                    $fsize = file_exists($fpath) ? \wlibrary\code\format_fsize(filesize($fpath), 'MB', 1) . "MB" : "N/A";

                    $viewerUrl = config('app.url') . "/lib/pdf.js/web/?file=&rf={$rfiles[$key]}&rid=$rid";
                    $downloadUrl = config('app.url') . "/lib/get_book_file.php?rf=" . $rfiles[$key] . "&rid=$rid";

                    $imgSrc = $book->cover_image ?  config('app.url','/nwlibrary')."/storage/cover_images/".$_SESSION['lib_inst']."/".$book->cover_image : config('app.url') . "/image/pdf-icon.png";
                    
                    $savePdfTag=null;
                    if($isAdmin || $book->rdonly_pdf_yn!='Y') {	 // readonly pdf
                        $savePdfTag="
                        <hr>
                        <a href='$downloadUrl' target='_blank'>
                            <img src='" . config('app.url') . "/image/button/save.png' alt='Save' style='height:24px;'>
                        </a>
                        ";
                    }

                    $result .= "
                        <div class='card shadow-sm' style='width: 220px; min-width: 200px; margin: 10px;'>
                            <div class='card-body text-center'>
                                <a href='$viewerUrl' target='_blank'>
                                    <img src='$imgSrc' alt='PDF' style='max-width:100%; max-height:140px; object-fit:contain; display:block; margin:auto;'>
                                    <p class='mt-2 font-weight-bold text-truncate' title='$val'>$val</p>
                                </a>
                                <p class='text-muted' style='font-size:0.9em;'>($fsize)</p>
                                $savePdfTag
                            </div>
                        </div>
                    ";
                }
                else { // non pdf
                    if($isAdmin || $fext=='mp4'||$fext=='ogg'||$fext=='webm') {
                        if($book->rdonly_video_yn!='Y') {	 // readonly video
                            $down_tag.=config('app.url')."/lib/get_book_file.php?rf=".$rfiles[$key]."&rid=$rid";
                        }
                        else $video_down_enabled=false;
                    }
                    else $down_tag.=config('app.url')."/lib/get_book_file.php?rf=".$rfiles[$key]."&rid=$rid";
                    
                    $down_tag.="\" target='_blank'>";		
                    $down_tag.=$val."</a>";
                    
                    if($video_down_enabled==false) ;
                    else {
                        $new_val="<span id='fdiv$key' style='flow:left'>$down_tag $pdfDownTag <font color='magenta'>($fsize)</font></span>";	
                    }	
                }	
			}
			else $new_val=$val." <font color='magenta'>($fsize)</font>";
            
            if($fext != 'pdf') {
                if($video_down_enabled==false) ;
                else $result.=($cnt++).": $new_val <br>";
            }
		}
	}
	return $result;
}


function show_list_videos($book,$perm,$rid){
	if(!$book->files) return;
	$isAdmin = (Auth::check() && Auth::user()->isAdmin()) ? true : false;
    $result=null;
    $no_down_script=null;
	$files=explode(';',$book->files);
	$rfiles=explode(';',$book->rfiles);
	foreach($files as $key =>$val){
		if($val) {
            $fpath=config('app.root')."/storage/app/ebook/{$_SESSION['lib_inst']}/{$book->rid}/{$rfiles[$key]}";
            if(file_exists($fpath)) {
			    $fsize=\wlibrary\code\format_fsize(filesize($fpath),'MB',1)."MB";
            }
            else $fsize="N/A";
            
            $fext = strtolower(substr($val,-3,3)); 

			if($fext=='mp4' || $fext=='ogg' || $fext=='webm') {				
				
				if(!isAdmin && $book->rdonly_video_yn=='Y') {
					$no_down_script.="
					<script>				
					$(document).ready(function(){
					   $('#w2_cms_video$key').bind('contextmenu',function() { return false; });
					});
					</script>
					";
					$no_down_tag="controlsList='nodownload'";
				}
				else {
					$no_down_script=null;
					$no_down_tag=null;
				}
								
				$down_tag=config('app.url')."/lib/get_book_file.php?rf=".$rfiles[$key]."&rid=$rid";
				$result.= "
                    <b>$val</b>  <font color='magenta'>($fsize)</font><br\>
                    <video id='w2_cms_video$key' width='100%' controls $no_down_tag >
                        <source src='$down_tag' type='video/$fext'>
                    </video><br\><br\>$no_down_script
				";
			}
		}
	}

	return $result;
}


function edit_list_old_files($book,$perm,$rid){
	global $num_already_files;
	$isAdmin = (Auth::check() && Auth::user()->isAdmin()) ? true : false;
	if(!$book->files) return;
	$result=null;
	$files=explode(';',$book->files);
	
	if(is_array($files)) $num_already_files=count($files)-1;
	
	$rfiles=explode(';',$book->rfiles);
	foreach($files as $key =>$val){
		$onClickDel=null;
		$pdfDownTag=null; // when read only PDF is not set
		if($val) {
			if($perm['R']=='Y') {
				$fext = strtolower(substr($val,-3,3)); 
                
                $fpath=config('app.root')."/storage/app/ebook/{$_SESSION['lib_inst']}/{$book->rid}/{$rfiles[$key]}";
                if(file_exists($fpath)) {
                    $fsize=\wlibrary\code\format_fsize(filesize($fpath),'MB',1)."MB";
                }
                else $fsize='N/A';

				$down_tag=null;
				$down_tag="<a href=\"";
				if($fext=='pdf'){ // readonly pdf
					$down_tag.=config('app.url')."/lib/pdf.js/web/?file=&rf={$rfiles[$key]}&rid=$rid";
									
					if($isAdmin || $book->rdonly_pdf_yn!='Y') {						
						$pdfDownTag=" &nbsp; <a href=\"".config('app.url')."/lib/get_book_file.php?rf=".$rfiles[$key]."&rid=$rid\" target='_blank'>";
						$pdfDownTag.="<img src='".config('app.url')."/image/button/save.png' class='wlibrary_icon'>";
						$pdfDownTag.="</a>";
					}
				}
				else $down_tag.=config('app.url')."/lib/get_book_file.php?rf=".$rfiles[$key]."&rid=$rid";
				
				$down_tag.="\" target='_blank'>";		
				$down_tag.=$val."</a>";
		
				$new_val="<span id='fdiv$key' style='flow:left'>$down_tag $pdfDownTag</span>";
				if($perm['D']=='Y') {
					$onClickDel="onClick=\"del_file_func('".addslashes($val)."');\"";
					$new_val.=" <span $onClickDel> &nbsp;<img src='".config('app.url')."/image/button/trash.png' class='wlibrary_icon'> </span>";
				}
			}
			else $new_val=$val;
			$result.=($key+1).": $new_val <font color='magenta'>($fsize)</font><br>";
		}
	}
	return $result;
}


function edit_list_videos($book,$perm,$rid){
	if(!$book->files) return;
	$isAdmin = (Auth::check() && Auth::user()->isAdmin()) ? true : false;
    $result=null;
    $no_down_script=null;
	$files=explode(';',$book->files);
	$rfiles=explode(';',$book->rfiles);
	foreach($files as $key =>$val){
		if($val) {
			$fext = strtolower(substr($val,-3,3)); 			
			if($fext=='mp4' || $fext=='ogg' || $fext=='webm') {				
				
				if(!$isAdmin && $book->rdonly_video_yn=='Y') {
					$no_down_script.="
					<script>				
					$(document).ready(function(){
					   $('#w2_cms_video$key').bind('contextmenu',function() { return false; });
					});
					</script>
					";
					$no_down_tag="controlsList='nodownload'";
				}
				else {
					$no_down_script=null;
					$no_down_tag=null;
				}
								
				$down_tag=config('app.url')."/lib/get_book_file.php?rf=".$rfiles[$key]."&rid=$rid";
				$result.= "
                    <b>$val</b> <br\>
                    <video id='w2_cms_video$key' width='100%' controls $no_down_tag >
                        <source src='$down_tag' type='video/$fext'>
                    </video><br\><br\>$no_down_script
				";
			}
		}
	}

	return $result;
}
?>