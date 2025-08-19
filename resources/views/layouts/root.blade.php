<?PHP
// [SJH] Multi-lang change if there is any
    if(!session()->has('refreshed'))  session(['refreshed' => false]);  // to prevent infinite refreshing
    if(\vwmldbm\code\manage_lang(app()->getLocale(),'ccode') && ! session('refreshed')) { // if changed, apply
        session(['refreshed' => true]);
        die("<script>location.reload();</script>"); // to reload the vwmldbm/config.php
    }
    else session(['refreshed' => false]);

    if(session()->has('lib_inst')) {
        $about_hf=App\Models\About::where('inst',session('lib_inst'))->first();
    }
?>
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta tttp-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <script src="{{ auto_asset('/lib/js/popper.1.16.0.min.js') }}"></script>
    <script src="{{ auto_asset('/lib/jquery/jquery-3.4.1.min.js') }}"></script>
    <script src="{{ auto_asset('/lib/jquery/ui/1.12.1/jquery-ui.min.js') }}"></script>
    <script src="{{ auto_asset('/lib/bootstrap-4.6.2-dist/js/bootstrap.js') }}"></script>

    <script src="{{ auto_asset('/lib/js/util.js') }}?v=20250810"></script>

    @if(isset($frontHome))
        <script src="{{ auto_asset('/lib/bootstrap-3.4.1/dist/js/bootstrap.min.js') }}"></script>
        <!--script src="{{ asset('/lib/jquery.mobile-1.4.5/jquery-3.4.1.min.js') }}"></script-->
    @endif
    
    @if(isset($frontHome))        
        <link href="{{ auto_asset('/lib/bootstrap-4.6.2-dist/css/bootstrap.min.css?nocache') }}" rel="stylesheet">
        <link href="{{ auto_asset('/lib/bootstrap-3.4.1/dist/css/bootstrap_carousel_only.css?nocache=7') }}" rel="stylesheet">
    @else <link href="{{ auto_asset('/lib/bootstrap-4.6.2-dist/css/bootstrap.min.css') }}" rel="stylesheet">
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
            border-radius: 16px;
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.05);
            padding: 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .dashboardbox:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
            border-color: rgba(0, 123, 255, 0.2);
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

        .nav-header {
            width: 100%;
        }

        .dashb {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 1.5rem;
            font-variant: normal;
        }

        .dashb_num {
            font-weight: bold;
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }

        .dropdownb{
            position: relative;
            border: 3px solid #73AD21;
        }

        .zoom:hover {
            transition: transform .5s;
            transform: scale(1.5);
        }

        /* Modern dashboard card styles for backward compatibility */
        .dashboard-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .dashboard-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
            border-color: rgba(0, 123, 255, 0.2);
        }
        
        .icon-wrapper {
            position: relative;
            display: inline-block;
        }
        
        .dashboard-icon {
            width: 64px;
            height: 64px;
            object-fit: contain;
            transition: transform 0.3s ease;
            transform-origin: center;
        }
        
        .dashboard-card:hover .dashboard-icon {
            transform: scale(1.1);
        }
        
        .user-count, .book-count, .rental-count, .reserve-count, .favorite-count, .eshelf-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            box-shadow: 0 2px 8px rgba(255, 107, 107, 0.3);
            z-index: 10;
            transform: scale(1);
            transition: transform 0.3s ease;
        }
        
        .dashboard-card:hover .user-count,
        .dashboard-card:hover .book-count,
        .dashboard-card:hover .rental-count,
        .dashboard-card:hover .reserve-count,
        .dashboard-card:hover .favorite-count,
        .dashboard-card:hover .eshelf-count {
            transform: scale(1.1);
        }
        
        .card-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .card-text {
            font-size: 13px;
            line-height: 1.4;
        }
        
        .dashboard-card .card-body {
            padding: 2rem 1.5rem;
        }
        
        @media (max-width: 768px) {
            .dashboard-card .card-body {
                padding: 1.5rem 1rem;
            }
            
            .dashboard-icon {
                width: 48px;
                height: 48px;
            }
        }
    </style>
</head>
<body>
    <div>
        <div class="sticky-top border-bottom bg-white">
            <div class="container" style="margin-top: 0px;">
                @include('include.navbar')        
            </div>
        </div>

        <div class="container" style='width:100%; margin-top: 0px;'>
            @include('include.messages')
        </div>

        @yield('content')

        <div class="container text-center mt-0">
            <?PHP
                if(isset($about_hf->footer)) echo $about_hf->footer;
            ?>
        </div>

        <div class="container text-center my-4">
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