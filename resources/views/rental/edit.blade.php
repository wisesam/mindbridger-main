<?php
    // Pre-loading the code values for performance
    $field_arr=array();
    \vwmldbm\code\get_field_name_all('rental',$field_arr);

    $field_arr2=array();
    \vwmldbm\code\get_field_name_all('book',$field_arr2);

    $field_arr3=array();
    \vwmldbm\code\get_field_name_all('book_copy',$field_arr3);
?>

@extends('layouts.root')
@section('content')

<link href="{{ asset('/lib/datetimepicker-master/jquery.datetimepicker.css?nocache') }}" rel="stylesheet">
<script src="{{ asset('/lib/datetimepicker-master/build/jquery.datetimepicker.full.min.js') }}"></script>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">{{ __('Rental Information') }}</div>
                <div class="card-body">
                    <form method="POST" name='form1' action="{{config('app.url','/wlibrary')."/rental/".$rental->id}}">
                        @csrf
                        @method('put')
                        <input type='hidden' name='id' value="{{$rental->id}}">
                        <input type='hidden' name='barcode' value="{{$book_copy->barcode}}">
                        
                        <div class="form-group row">
                            <label for="uid" class="col-md-3 col-form-label text-md-right">{{ $field_arr['uid'] }}</label>

                            <div class="col-md-7">
                                <span class="form-control">{{$rental->uid}}</span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="rent_date" class="col-md-3 col-form-label text-md-right">{{ $field_arr['rent_date'] }}</label>
                            <div class="col-md-7">
                                <span class="form-control">{{$rental->rent_date}}</span>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="c_rent_status" class="col-md-3 col-form-label text-md-right">{{ $field_arr['c_rent_status'] }}</label>

                            <div class="col-md-7">
                                <?PHP
                                     echo \vwmldbm\code\print_code('code_c_rent_status',$rental->c_rent_status,'c_rent_status',null,null,null,null,null,"id='c_rent_status' onChange='rent_status_control()' class='form-control' style='background:gold;'");
                                ?>
                                @error('c_rent_status')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <script>
                                    var rental_terminated_arr=[]; // if the code is 'Y', then returned_date should be enabled
                                    function get_rental_terminated(){ 
                                        <?php
                                        $rental_terminated_arr=array();
                                        \vwmldbm\code\get_code_name_all($rental_terminated_arr,'code_c_rent_status','rental_terminated_yn');
                                        foreach($rental_terminated_arr as $key => $val) {
                                            echo "rental_terminated_arr[$key]='$val';";
                                        }
                                        ?>
                                    }

                                    function rent_status_control() {                                                                         
                                        var status=document.getElementById('c_rent_status');                                        
                                        var rdate=document.getElementById('return_date');
                                        
                                        if(rental_terminated_arr[status.value]=='Y') { // Returned.
                                            rdate.disabled=false; 
                                            if(rdate.value=="") rdate.focus();                                    
                                        }
                                        else {
                                            rdate.value="";
                                            rdate.disabled=true;
                                        }
                                    }

                                    $(document).ready(function(){
                                        get_rental_terminated();
                                        rent_status_control();                                        
                                    });
                                </script>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="return_date" class="col-md-3 col-form-label text-md-right">{{ $field_arr['return_date'] }}</label>

                            <div class="col-md-7">
                                <input id="return_date" type="text" class="form-control @error('return_date') is-invalid @enderror" name="return_date"  onChange='return_date_check(this);' value="{{$rental->return_date }}" autocomplete="off">
                                <script>
                                    var rent_date='<?=$rental->rent_date?>';
                                    
                                    function return_date_check(obj) {
                                       if(obj.value=='') {
                                            obj.style.color='';
                                            document.getElementById('submit_btt').disabled=false;
                                       }
                                       else if(obj.value <=rent_date) {
                                            obj.style.color='red';
                                            document.getElementById('submit_btt').disabled=true;
                                       }
                                       else {
                                            obj.style.color='';
                                            document.getElementById('submit_btt').disabled=false;
                                       }
                                    }

                                    $( "#return_date" ).datetimepicker({
                                        changeMonth: true,
                                        changeYear: true,
                                        yearRange:'<?=date('Y')?>:<?=date('Y')?>',
                                        minDate:Date.now(),
                                        showButtonPanel: true,
                                        // appendText:"(yyyy-mm-dd)",
                                        format:"Y-m-d H:m:s",                                      
                                        hideIfNoPrevNext: true, duration: '',
                                    });

                                    
                                </script>
                                @error('return_date')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="rcomment" class="col-md-3 col-form-label text-md-right">{{ $field_arr['rcomment'] }}</label>

                            <div class="col-md-7">                         
                                <textarea type="text" class="form-control @error('rcomment') is-invalid @enderror" name="rcomment" autocomplete="{{ old('rcomment') }}">{{$rental->rcomment}}</textarea>
                                @error('rcomment')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-7 offset-md-4">
                                <button type="submit" id="submit_btt" class="btn btn-primary">
                                    {{ __('Update') }}
                                </button>
                                &nbsp; 
                                <button type="button" class="btn btn-success" onClick="window.location='{{config('app.url','/wlibrary')."/rental/"}}'">
                                    {{ __('List') }}
                                </button>
                                &nbsp; 
                                <button type="button" class="btn btn-danger" onClick="confirm_delete()">
                                    {{ __('Delete') }}
                                </button>

                                <script>
                                    function confirm_delete() {
                                        if(confirm("Are you sure you want to delete?")) {
                                           document.getElementById('bcDelForm').submit();
                                        }
                                    }
                                </script>
                            </div>
                        </div>
                    </form>
                    
                    <form  id='bcDelForm' method='POST' action='{{config('app.url','/wlibrary')."/rental/".$rental->id}}'>
                        @csrf
                        @method('delete')
                    </form>
                </div>
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
                        <label for="book_name" class="col-md-3 col-form-label text-md-right">{{ $field_arr2['title'] }}</label>
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