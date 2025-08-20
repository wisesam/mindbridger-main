<?php

    // Pre-loading the code values for performance
    $field_arr=array();
    \vwmldbm\code\get_field_name_all('rental',$field_arr);

    $field_arr2=array();
    \vwmldbm\code\get_field_name_all('book',$field_arr2);

    $field_arr3=array();
    \vwmldbm\code\get_field_name_all('book_copy',$field_arr3);

    // Check if there is a corresponding rental code set up properly. If not disable renting
    $rented_code= \vwmldbm\code\get_c_name('code_c_rent_status',null,'code','NO_CODE',null," and rented_yn='Y'");
?>

@extends('layouts.root')
@section('content')
<link href="{{ asset('/lib/datetimepicker-master/jquery.datetimepicker.css?nocache') }}" rel="stylesheet">
<script src="{{ asset('/lib/datetimepicker-master/build/jquery.datetimepicker.full.min.js') }}"></script>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">{{ __('Add new Rental') }}</div>
              @if($rented_code)
                <div class="card-body">
                    <form method="POST" name='create_form' action="{{route('rental.store')}}">
                        @csrf
                        <input type='hidden' name='bcid' value="{{$book_copy->id}}">
                        <input type='hidden' name='barcode' value="{{$book_copy->barcode}}">
                        
                        <div class="form-group row">
                            <label for="uid" class="col-md-3 col-form-label text-md-right">{{ $field_arr['uid'] }}</label>
                            <div class="col-md-7">
                                <input id="uid" type="text" class="form-control @error('uid') is-invalid @enderror" name="uid"  style='background:yellow;' value="" autocomplete="on" readonly required onClick="open_user_search()">    
                                    @error('uid')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    
                                    <script>
                                        var max_book_rent_days=[];
                                        var max_date;
                                        var child_iframe_no_nav=true;
                                        <?PHP
                                            // Get all max_book_rent_days from code_c_utype
                                            $max_book_rent_days=array();
                                            \vwmldbm\code\get_code_name_all($max_book_rent_days,'code_c_utype','max_book_rent_days'); 
                                            foreach($max_book_rent_days as $key => $val) {
                                                echo "max_book_rent_days['{$key}']='{$val}';";
                                            }
                                        ?>
                                        $(document).ready(function (){
                                            $( "#dialog" ).dialog({
                                                width:'90%',
                                                height:'600',
                                                autoOpen: false,
                                                position: {
                                                    my: 'middle',
                                                    at: 'top',
                                                    of: this,
                                                }
                                            });
                                        });
                        
                                        function open_user_search(){ 
                                            $('#dialog').dialog('open');    
                                            document.getElementById('iframe').src="{{config('app.url','/wlibrary')}}/users/choose_list/?mode=NO_NAV";                                            
                                        }

                                        function close_user_search(){ 
                                            $('#dialog').dialog('close');    
                                            document.getElementById('iframe').src="";                                            
                                        }

                                        function choose_user_from_iframe(u,c) {
                                            var f=document.create_form;
                                            f.uid.value=u;
                                            f.rent_date.value="";
                                            f.due_date.value="";
                                            f.rent_date.disabled=false;
                                            f.due_date.disabled=false;
                                            f.rcomment.disabled=false;
                                            f.register_btn.disabled=false;
                                            
                                            document.getElementById('max_date_div').innerHTML="("+max_book_rent_days[c]+" {{__(" Days")}})";
                                            max_date=max_book_rent_days[c];
                                            close_user_search();
                                        }  
                                    </script>
                                    <div id="dialog" title="" style="display:none; align-top;">
                                        <iframe id='iframe' frameborder='0' width='100%' height='100%'></iframe>
                                    </div> 
                            </div>

                            <script>

                            </script>
                        </div>

                        <div class="form-group row">
                            <label for="rent_date" class="col-md-3 col-form-label text-md-right">{{ $field_arr['rent_date'] }}</label>
                            <div class="col-md-7">
                                <input id="rent_date" type="text" class="form-control @error('rent_date') is-invalid @enderror" name="rent_date" disabled onChange="manage_due_date(this)" required autocomplete="off">    
                                    @error('rent_date')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror                                
                            
                                    <script>
                                    function manage_due_date(obj) { // called from input, 'rent_date'
                                        if(obj.value=="") document.create_form.due_date.value="";
                                        else {
                                            if(obj.value.length==19) { // full time length 
                                                var yr=obj.value.substring(0,4);                                                
                                                var mt=obj.value.substring(5,7);                                                
                                                var dt=obj.value.substring(8,10);
                                                var hr=obj.value.substring(11,13);
                                                var min=obj.value.substring(14,16);                                                
                                                mt=parseInt(mt)-1; // because js month start from 0
                                                var d= new Date(yr,mt,dt,hr,min);                                               
                                                
                                                d.setDate(d.getDate()+parseInt(max_date));                                                
                                                document.create_form.due_date.value=dateFormat(d,"yyyy-mm-dd HH:MM:ss");
                                            }
                                        }
                                    }
                                    </script>
                            </div>

                            <script>
                                $( "#rent_date" ).datetimepicker({
                                    changeMonth: true,
                                    changeYear: true,
                                    yearRange:'<?=date('Y')?>:<?=date('Y')?>',
                                    //minDate:Date.now(),
                                    showButtonPanel: true,
                                    // appendText:"(yyyy-mm-dd)",
                                    format:"Y-m-d H:m:s",                                      
                                    hideIfNoPrevNext: true, duration: '',
                                });
                            </script>
                        </div>

                        <div class="form-group row">
                            <label for="due_date" class="col-md-3 col-form-label text-md-right">
                                {{ $field_arr['due_date'] }}
                                <div id='max_date_div' style="color:blue;font-weight:bold;">                                  
                                </div>
                            </label>
                            <div class="col-md-7">
                                <input id="due_date" type="text" class="form-control @error('due_date') is-invalid @enderror" onChange="due_date_check(this)" name="due_date" disabled required autocomplete="off">    
                                    @error('due_date')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror 

                                    <script>
                                    function due_date_check(obj) { // called from input, 'due_date'
                                        var f=document.create_form;
                                        if(obj.value=="") return;
                                        if(document.create_form.rent_date.value >= document.create_form.due_date.value) {
                                            obj.style.color="red";
                                            f.register_btn.disabled=true;
                                        }
                                        else {
                                            obj.style.color="black";
                                            f.register_btn.disabled=false;
                                        }
                                    }
                                    </script>
                            </div>

                            <script>
                                $( "#due_date" ).datetimepicker({
                                    changeMonth: true,
                                    changeYear: true,
                                    yearRange:'<?=date('Y')?>:<?=date('Y')?>',
                                    //minDate:Date.now(),
                                    showButtonPanel: true,
                                    // appendText:"(yyyy-mm-dd)",
                                    format:"Y-m-d H:m:s",                                      
                                    hideIfNoPrevNext: true, duration: '',
                                });
                            </script>
                        </div>

                        <div class="form-group row">
                            <label for="rcomment" class="col-md-3 col-form-label text-md-right">{{ $field_arr['rcomment'] }}</label>
                            <div class="col-md-7">                         
                                <textarea type="text" class="form-control @error('rcomment') is-invalid @enderror" name="rcomment" disabled></textarea>
                                @error('rcomment')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-7 offset-md-4">
                                <button type="submit" id='register_btn' class="btn btn-primary" disabled>
                                    {{ __('Register') }}
                                </button>
                                &nbsp; 
                                <button type="button" class="btn btn-success" onClick="window.history.back()">
                                    {{ __('Back') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
              @else  <?PHP // proper rental code was not set up ?>
              <div class="form-group row">
                <div class="col-md-7 offset-md-4" style='color:red;'>
                    {{ __("First set up 'Rent Status' from admin/vwmldbm/code menu!") }}
                </div>
              </div>

              @endif <?PHP // end of show add rental ?>

            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">{{ __('Resource Copy Information') }}</div>
                <div class="card-body">
                    <div class="form-group row">
                                                    <label for="book_name" class="col-md-3 col-form-label text-md-right">{{ __("Title") }}</label>
                        <div class="col-md-7">
                            <span class='form-control' style='border:solid black 0px;'>{{ $book->title }}</span>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="barcode" class="col-md-3 col-form-label text-md-right">{{ $field_arr3['barcode'] }}</label>

                        <div class="col-md-7">
                            <span class='form-control' style='border:solid black 0px;'>{{ $book_copy->barcode }}</span>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="call_no" class="col-md-3 col-form-label text-md-right">{{ $field_arr3['call_no'] }}</label>

                        <div class="col-md-7">
                            <span class='form-control' style='border:solid black 0px;'>{{ $book_copy->call_no }}</span>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="location" class="col-md-3 col-form-label text-md-right">{{ $field_arr3['location'] }}</label>
                        <div class="col-md-7">
                            <span class='form-control' style='border:solid black 0px;'>{{ $book_copy->location }}</span>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="c_rstatus" class="col-md-3 col-form-label text-md-right">{{ $field_arr3['c_rstatus'] }}</label>
                        <div class="col-md-7">
                            <span class='form-control' style='border:solid black 0px;'><?=\vwmldbm\code\get_c_name('code_c_rstatus',$book_copy->c_rstatus)?></span>                          
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="comment" class="col-md-3 col-form-label text-md-right">{{ $field_arr3['comment'] }}</label>

                        <div class="col-md-7">                            
                            <span class='form-control' style='border:solid black 0px;'>{{ $book_copy->comment }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    /*
 * Date Format 1.2.3
 * (c) 2007-2009 Steven Levithan <stevenlevithan.com>
 * MIT license
 *
 * Includes enhancements by Scott Trenda <scott.trenda.net>
 * and Kris Kowal <cixar.com/~kris.kowal/>
 *
 * Accepts a date, a mask, or a date and a mask.
 * Returns a formatted version of the given date.
 * The date defaults to the current date/time.
 * The mask defaults to dateFormat.masks.default.
 */

var dateFormat = function () {
	var	token = /d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,
		timezone = /\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,
		timezoneClip = /[^-+\dA-Z]/g,
		pad = function (val, len) {
			val = String(val);
			len = len || 2;
			while (val.length < len) val = "0" + val;
			return val;
		};

	// Regexes and supporting functions are cached through closure
	return function (date, mask, utc) {
		var dF = dateFormat;

		// You can't provide utc if you skip other args (use the "UTC:" mask prefix)
		if (arguments.length == 1 && Object.prototype.toString.call(date) == "[object String]" && !/\d/.test(date)) {
			mask = date;
			date = undefined;
		}

		// Passing date through Date applies Date.parse, if necessary
		date = date ? new Date(date) : new Date;
		if (isNaN(date)) throw SyntaxError("invalid date");

		mask = String(dF.masks[mask] || mask || dF.masks["default"]);

		// Allow setting the utc argument via the mask
		if (mask.slice(0, 4) == "UTC:") {
			mask = mask.slice(4);
			utc = true;
		}

		var	_ = utc ? "getUTC" : "get",
			d = date[_ + "Date"](),
			D = date[_ + "Day"](),
			m = date[_ + "Month"](),
			y = date[_ + "FullYear"](),
			H = date[_ + "Hours"](),
			M = date[_ + "Minutes"](),
			s = date[_ + "Seconds"](),
			L = date[_ + "Milliseconds"](),
			o = utc ? 0 : date.getTimezoneOffset(),
			flags = {
				d:    d,
				dd:   pad(d),
				ddd:  dF.i18n.dayNames[D],
				dddd: dF.i18n.dayNames[D + 7],
				m:    m + 1,
				mm:   pad(m + 1),
				mmm:  dF.i18n.monthNames[m],
				mmmm: dF.i18n.monthNames[m + 12],
				yy:   String(y).slice(2),
				yyyy: y,
				h:    H % 12 || 12,
				hh:   pad(H % 12 || 12),
				H:    H,
				HH:   pad(H),
				M:    M,
				MM:   pad(M),
				s:    s,
				ss:   pad(s),
				l:    pad(L, 3),
				L:    pad(L > 99 ? Math.round(L / 10) : L),
				t:    H < 12 ? "a"  : "p",
				tt:   H < 12 ? "am" : "pm",
				T:    H < 12 ? "A"  : "P",
				TT:   H < 12 ? "AM" : "PM",
				Z:    utc ? "UTC" : (String(date).match(timezone) || [""]).pop().replace(timezoneClip, ""),
				o:    (o > 0 ? "-" : "+") + pad(Math.floor(Math.abs(o) / 60) * 100 + Math.abs(o) % 60, 4),
				S:    ["th", "st", "nd", "rd"][d % 10 > 3 ? 0 : (d % 100 - d % 10 != 10) * d % 10]
			};

		return mask.replace(token, function ($0) {
			return $0 in flags ? flags[$0] : $0.slice(1, $0.length - 1);
		});
	};
}();

// Some common format strings
dateFormat.masks = {
	"default":      "ddd mmm dd yyyy HH:MM:ss",
	shortDate:      "m/d/yy",
	mediumDate:     "mmm d, yyyy",
	longDate:       "mmmm d, yyyy",
	fullDate:       "dddd, mmmm d, yyyy",
	shortTime:      "h:MM TT",
	mediumTime:     "h:MM:ss TT",
	longTime:       "h:MM:ss TT Z",
	isoDate:        "yyyy-mm-dd",
	isoTime:        "HH:MM:ss",
	isoDateTime:    "yyyy-mm-dd'T'HH:MM:ss",
	isoUtcDateTime: "UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"
};

// Internationalization strings
dateFormat.i18n = {
	dayNames: [
		"Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat",
		"Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"
	],
	monthNames: [
		"Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
		"January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"
	]
};

// For convenience...
Date.prototype.format = function (mask, utc) {
	return dateFormat(this, mask, utc);
};

</script>
@endsection