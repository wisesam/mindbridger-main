<?PHP
// [SJH] Multi-lang change if there is any

    if(!session()->has('refreshed'))  session(['refreshed' => false]);  // to prevent infinite refreshing
    if(\vwmldbm\code\manage_lang(app()->getLocale(),'ccode') && ! session('refreshed')) { // if changed, apply
        session(['refreshed' => true]);
        die("<script>location.reload();</script>"); // to reload the vwmldbm/config.php
    }
    else session(['refreshed' => false]);

    if(session()->has('lib_inst')) {
        $about_hf=App\About::where('inst',session('lib_inst'))->first();
    }
?>

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta tttp-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <script src="{{ auto_asset('/lib/js/popper/popper.js') }}"></script>
    <script src="{{ auto_asset('/lib/jquery/jquery-3.4.1.min.js') }}"></script>
    <script src="{{ auto_asset('/lib/jquery/ui/1.12.1/jquery-ui.min.js') }}"></script>
    <script src="{{ auto_asset('/lib/bootstrap-4.4.1-dist/js/bootstrap.js') }}"></script>

    @if(isset($frontHome))
        <script src="{{ auto_asset('/lib/bootstrap-3.4.1/dist/js/bootstrap.min.js') }}"></script>
        <!--script src="{{ asset('/lib/jquery.mobile-1.4.5/jquery-3.4.1.min.js') }}"></script-->
    @endif
    <!--script src="{{ asset('/lib/js/app.js') }}"></script-->   
    
    @if(isset($frontHome))        
        <link href="{{ auto_asset('/lib/bootstrap-4.4.1-dist/css/bootstrap.min.css?nocache') }}" rel="stylesheet">
        <link href="{{ auto_asset('/lib/bootstrap-3.4.1/dist/css/bootstrap_carousel_only.css?nocache=7') }}" rel="stylesheet">
    @else <link href="{{ auto_asset('/lib/bootstrap-4.4.1-dist/css/bootstrap.min.css') }}" rel="stylesheet">
    @endif

    @if(isset($aboutPage))   
        <link href="{{ auto_asset('/lib/open-iconic-master/font/css/open-iconic.css') }}" rel="stylesheet">
    @endif

    <link href="{{ auto_asset('/lib/jquery/ui/1.12.1/jquery-ui.min.css') }}" rel="stylesheet">
    <link href="{{ auto_asset('/lib/css/common.css') }}" rel="stylesheet">
    <title>{{config('app.name','WISE Lib Sys')}}</title>

    <style>
        .dashboardbox {
            height: 100%;
            border-radius: 10px;
            background: #ffffff;
            border : 5px solid #65afc9;
            padding: 2px;
        }

        body {
            padding-top: 0px;
        }

        .carousel {
            padding-top: 20px;
            width: 100%;
        }

        .slide-box {
            display: flex;
            justify-content: space-between;
        }

        @media (min-width: 576px) and (max-width: 767.98px) {             //Small devices (landscape phones and up)
            .slide-box img {
                -ms-flex: 0 0 50%;
                flex: 0 0 50%;
                max-width: 50%;
            }
        }

        @media (min-width: 768px) and (max-width: 991.98px) {             //Medium device (Tablets and up)
            .slide-box img {
                -ms-flex: 0 0 33.3333%;
                flex: 0 0 33.3333%;
                max-width: 33.3333%;
            }
        }

        @media (min-width: 992px)											//Desktops
        {
            .slide-box img {
                -ms-flex: 0 0 25%;
                flex: 0 0 25%;
                max-width: 18%;
            }
        }

        .carousel-caption {
            background-color: rgba(0, 0, 0, 0.5);
            padding: 20px;
            border-radius: .5rem;
        }

        .container {
            margin-top: 30px;
        }

        .dashb {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            font-variant: small-caps;
            align-self:flex-end;
            align-items:flex-start;
            padding-left: 5px;
            padding-right:5px;
        }

        .dashb_num{
            align-self:center;
            font-weight: bold;
            font-size: 25px;
            margin-top:5px;
        }

        .dropdownb{
            position: relative;
            border: 3px solid #73AD21;
        }

        .zoom:hover {
            transition: transform .5s;
            transform: scale(1.5);
        }
    </style>
</head>
<body>
    <div>
        <div class="container text-center mb-3 mt-3">
            <?PHP
                if(isset($about_hf->header)) echo $about_hf->header;
                else echo "<div class='container text-center' style='color:red;font-style:italic;font-weight:bold;font-size:large;' display:'inline;'> WISE Library Beta System</div>";
            ?>
        </div> 

        @include('include.navbar')        
        <div class="container" style='width:100%;'>
            @include('include.messages')
            @yield('content')
        </div>

        <div class="container text-center mb-3 mt-3">
            <?PHP
                if(isset($about_hf->footer)) echo $about_hf->footer;
            ?>
        </div>

        <div class="container text-center mb-3 mt-4">
            Copyright &copy; 2014-<?=date ("Y") ;?>  www.wise4edu.com reserved by WISE Team
        </div>
    </div>


    <script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    </script>
</body>
</html>