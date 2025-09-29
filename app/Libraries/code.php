<?PHP
/* [SJH] Wise Library Basic codes*/
namespace wlibrary\code;

function print_lang($field_name=null, $c=null, $fevent=null) {
    if(!$field_name) $field_name="lang";
    $kr_sel=null;
    $en_sel=null;
    $cn_sel=null;
    $mn_sel=null;

    if($c=='kr') $kr_sel=" selected";
    else if($c=='ar') $mn_sel=" selected";
    else if($c=='cn') $cn_sel=" selected";
    else $en_sel=" selected";

    $rval="
    <select name='$field_name' $fevent> 
        <option value='en' $en_sel>EN</option>
        <option value='cn' $cn_sel>CN</option>
        <option value='kr' $kr_sel>KR</option>
        <option value='ar' $mn_sel>AR</option>
    </select>";
    return $rval;
}

function genRandStr($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function return_bytes($val) {
// http://php.net/manual/en/function.ini-get.php
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val=(int) $val; // to remove 'M'

    switch($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}

function post_max_size($opt=null) {
    if($opt=='bytes') return return_bytes(ini_get('post_max_size'));
    else return ini_get('post_max_size');
}

function upload_max_filesize($opt=null) {
    if($opt=='bytes') return return_bytes(ini_get('upload_max_filesize'));
    else return ini_get('upload_max_filesize');
}

function max_file_uploads($opt=null) {	
    return ini_get('max_file_uploads');
}

function print_view_pdf_tags_js() {
    global $WISE;
    $rval="
        <div id='dialog_wise_view_pdf' title='' style='display:none;'>
            <iframe id='iframe_wise_view_pdf' frameborder=0 width='100%' height='100%'></iframe>
        </div>
        <script>
            function view_pdf(cid,type){
                document.getElementById('iframe_wise_view_pdf').src=\"{config('app.root','/wlibrary')}/app/Library/view_pdf.php?scode=\"+cid+\"&type=\"+type;
                \$('#dialog_wise_view_pdf').dialog('open');
            }
                \$(document).ready(function() {
                \$( '#dialog_wise_view_pdf' ).dialog({
                    width:900,height:800, 
                    autoOpen: false,
                    position: {
                        my: 'middle',
                        at: 'top',
                        of: this,
                    },
                    close: function f() {document.getElementById('iframe_wise_view_pdf').src='';},
                });
                });
            
        </script>
    ";
    return $rval;
}

function highlight($text,$key_arr,$bgcolor='yellow',$color='red') {
    if(!$text || !$key_arr) return $text;
    if(!is_array($key_arr)) return;
    $rval=null;
    foreach($key_arr as $key) {
        $key=mb_strtoupper($key);
        $rval.=mb_eregi_replace($key,"<span style='background:$bgcolor;color:$color;'>$key</span>",$text);
    }
  
	return $rval;
}


function dirSize($directory) {
    $size = 0;
    foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file){
        $size+=$file->getSize();
    }
    return $size;
} 

function format_fsize($n,$unit='MB',$fraction=0){
    if($unit=='KB')
        return number_format($n/1024,$fraction,'.',',');
    else if($unit=='MB')
        return number_format($n/(1024*1024),$fraction,'.',',');
    else if($unit=='GB')
        return number_format($n/(1024*1024*1024),$fraction,'.',',');
}

function page_size($fname='page_size',$p=10,$fevent=null) {
    $rval=null;
    $arr=[10,30,50,100];
    $rval="<select class='form-control'  name='$fname' $fevent style='max-width:100px;display:inline;vertical-align:middle;'>";
    foreach($arr as $n){
        if($p==$n)$sel="selected";
        else $sel=null;
        $rval.="<option value='$n' $sel>$n</option>";
    }
    $rval.="</select>";
    return $rval;
}