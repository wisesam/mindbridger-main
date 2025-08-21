<?php
    \vwmldbm\code\manage_lang(app()->getLocale(),'ccode'); // if changed, apply
    if(!isset($search_word)) $search_word=null; // search
    if(!isset($search_target)) $search_target=null; // search
    $mode=Request::segment(2); 
?>
<style>
    @keyframes shake {
        0% { transform: translate(1px, 1px) rotate(0deg); }
        10% { transform: translate(-1px, -2px) rotate(-1deg); }
        20% { transform: translate(-3px, 0px) rotate(1deg); }
        30% { transform: translate(3px, 2px) rotate(0deg); }
        40% { transform: translate(1px, -1px) rotate(1deg); }
        50% { transform: translate(-1px, 2px) rotate(-1deg); }
        60% { transform: translate(-3px, 1px) rotate(0deg); }
        70% { transform: translate(3px, 1px) rotate(-1deg); }
        80% { transform: translate(-1px, -1px) rotate(1deg); }
        90% { transform: translate(1px, 2px) rotate(0deg); }
        100% { transform: translate(1px, -2px) rotate(-1deg); }
    }

    .shake {
        animation: shake 3s infinite; /* slowed down */
    }
</style>
@if($mode!='choose_list')     
    <!-- <div class="container text-center" id='wlibrary_back_btt' style='display:none;'>
        <button type='button' class='btn btn-success'  onClick="window.history.back();">{{__("Go back")}}</button>
    </div>  -->
    <nav class="navbar navbar-light flex-direction: column" id='wlibrary_navbar'>
        <script>
            $(document).ready(function() {                
             // This is a trick to hide the navigation bar from inner iframes
              if(window.parent.location != window.location) { 
                  document.getElementById('wlibrary_navbar').style.display='none'; 
                  document.getElementById('wlibrary_back_btt').style.display='';                
              }             
            });
        </script>
        
        <div class="d-flex justify-content-between align-items-center w-100">
            <div class="d-flex align-items-center logo-section">
                <div style="width:150px;height:70px;">
                    <a href="{{config('app.url','/mindbridger')}}">
                        <img class="d-block" src="{{config('app.url','/mindbridger')}}/image/logo2.png?nocache=7"  width='100%' height='100%'>
                    </a>
                </div>
                
                <!-- Search and AI inline on larger screens -->
                <div class="d-none d-md-flex align-items-center ml-4">
                    <form class="form-inline" action='{{config('app.url')}}/book' name='form1'>        
                        <div class="input-group">    
                            <input class="form-control" type="search" name='search_word' placeholder="{{__("Search Resource")}}" value='{{$search_word}}' aria-label="Search">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="search" id="button-addon2">
                                    <img class="d-block" src="{{config('app.url','/mindbridger')}}/image/search_black.png?nocache=7"  width='100%' height='100%' />
                                </button>
                                <button class="btn btn-outline-dark ml-1" style="white-space: nowrap;color:purple;" onClick="window.location.href='{{ route('book.asearch') }}'" type="button">
                                    {{__("Advanced")}}
                                </button>
                              </div>
                        </div>
                    </form>
                    <button class="btn btn-primary ml-2" onClick="window.location.href='{{config('app.url','/mindbridger')}}/recommend'" type="button" style="width: 38px; height: 38px; padding: 0; background: linear-gradient(135deg, #007bff, #0056b3); border: none; box-shadow: 0 2px 8px rgba(0,123,255,0.3);">
                        <img class="d-block mx-auto" src="{{config('app.url','/mindbridger')}}/image/ai.png?nocache=7" width="30" height="30" alt="AI" style="filter: invert(1);" />
                    </button>
                </div>
            </div>

            <!-- Right side elements grouped together -->
            <div class="d-flex align-items-center">
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar" style="margin-left: 1rem;">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </div>

        <!-- Search and AI section - responsive layout -->
        <div class="search-ai-section mt-3 w-100">
            <div class="d-flex justify-content-end align-items-center w-100">
                <form class="form-inline" action='{{config('app.url')}}/book' name='form1'>        
                    <div class="input-group">    
                        <input class="form-control" type="search" name='search_word' placeholder="{{__("Search Resource")}}" value='{{$search_word}}' aria-label="Search">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="search" id="button-addon2">
                                <img class="d-block" src="{{config('app.url','/mindbridger')}}/image/search_black.png?nocache=7"  width='100%' height='100%' />
                            </button>
                          </div>
                    </div>
                </form>
                {{-- Detailed Button --}}
                {{-- <button class="btn btn-outline-dark ml-1" onClick="window.location.href='{{config('app.url')}}/asearch/'" type="button">{{__("Advanced")}}</button> --}}
                <button class="btn btn-primary ml-2" onClick="window.location.href='{{config('app.url','/mindbridger')}}/recommend'" type="button" style="width: 38px; height: 38px; padding: 0; background: linear-gradient(135deg, #007bff, #0056b3); border: none; box-shadow: 0 2px 8px rgba(0,123,255,0.3);">
                    <img class="d-block mx-auto" src="{{config('app.url','/mindbridger')}}/image/ai.png?nocache=7" width="30" height="30" alt="AI" style="filter: invert(1);" />
                </button>
            </div>
        </div>
        
        <div class="collapse navbar-collapse" id="collapsibleNavbar">
            <!-- Links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="{{config('app.url','/mindbridger')}}/">{{__('Home')}}</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="{{config('app.url','/mindbridger')}}/book">{{__('Resources')}}</a>
                </li>

               @if(Auth::check() && !Auth::user()->isAdmin())
                <li class="nav-item">
                    <a class="nav-link" href="{{config('app.url','/mindbridger')}}/rental">{{__('Rentals')}}</a>
                </li>
                @if(false)
                <li class="nav-item">
                    <a class="nav-link disabled" href="#">{{__('Favorites')}}</a>
                </li>
                @endif
               @endif

                <!-- Dropdown -->              
                <li class="nav-item dropdown"> 
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAbout" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{__("Library / Videos")}} 
                    </a>                  
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownAbout">
                        <a class="dropdown-item" href="{{config('app.url','/mindbridger')}}/about">{{__('About Library')}}</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item disabled " target='_blank' style='color:grey'>{{__("Intro Videos")}}</a>
                        @auth
                            @if(false || Auth::user()->isAdmin())
                                <a class="dropdown-item disabled " target='_blank' style='color:grey'>{{__("Admin Tutorials")}}</a> 
                            @endif    
                        @endauth
                    </div>
                </li>

                <!-- <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{__("Announcement / BBS")}} 
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="{{config('app.url','/mindbridger')}}/announcement">{{__("Announcement")}}</a>                        
                    </div>
                </li> -->
                @auth
                    @if(Auth::user()->isAdmin())
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAdmin" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {{__('Admin')}} 
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownAdmin">
                            <a class="dropdown-item" href="{{config('app.url','/mindbridger')}}/users/list">{{__("Users")}} </a>
                            <a class="dropdown-item" href="{{config('app.url','/mindbridger')}}/book">{{__("Resources")}}</a>                    
                            <a class="dropdown-item" href="{{config('app.url','/mindbridger')}}/book_copy">{{__("Resource Copies")}}</a>                    
                            <a class="dropdown-item" href="{{config('app.url','/mindbridger')}}/rental">{{__("Rentals")}}</a>                    
                            <a class="dropdown-item" href="{{config('app.url','/mindbridger')}}/vwmldbm" target="_blank">VWMLDBM</a>                    
                            <a class="dropdown-item" href="{{config('app.url','/mindbridger')}}/vwmldbm/batch" target="_blank">{{__("Batch Data")}}</a>                    
                        </div>
                    </li>
                    @endif
                @endauth 

                <!-- Login/Auth Section -->
                <li class="nav-item">
                    <hr class="my-2">
                </li>
                
                @guest                   
                    @if(config('app.multi_inst',''))
                        @if(session()->has('lib_inst'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{route('login')}}">{{__('Login')}}</a>
                            </li>
                        @endif
                        <li class="nav-item">
                            <a class="nav-link" href="{{route('login_clear')}}">{{__('Inst')}}</a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{route('login')}}">{{__('Login')}}</a>
                        </li>
                    @endif         
                @else 
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown2" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {{ Auth::user()->name }}
                        </a>

                        <div class="dropdown-menu" aria-labelledby="navbarDropdown2">
                            <a class="dropdown-item" href="{{ auto_url(route('users.edit', Auth::user()->id),[], false) }}">
                                {{ __('My Profile') }}
                            </a>

                            <a class="dropdown-item" href="{{ auto_url('logout') }}"
                                onclick="event.preventDefault();
                                            document.getElementById('logout-form').submit();">
                                {{ __('Logout') }}
                            </a>

                            <form id="logout-form" action="{{ auto_url('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endauth

                <!-- Language Selector -->
                <li class="nav-item">
                    <hr class="my-2">
                    <div class="px-3">
                        <label class="text-muted small mb-2">{{ __('Language') }}</label>
                        <div class="d-flex">
                            <?PHP            
                                echo \wlibrary\code\print_lang(null,app()->getLocale()," class='form-control form-control-sm' style='width:80px; font-size: 0.9rem;'  onChange=\"window.location='".config('app.url','/mindbridger')."/locale/'+this.value;\"");
                            ?>
                        </div>
                    </div>
                </li>
            </ul>       
        </div>        
    </nav>
    
    <style>
        /* Responsive navbar layout */
        .search-ai-section {
            display: block;
            width: 100%;
        }

        /* On medium devices and larger (768px and up) */
        @media (min-width: 768px) {
            .search-ai-section {
                display: none;
            }
        }

        /* On small devices (below 768px) */
        @media (max-width: 767.98px) {
            .search-ai-section {
                display: block;
                margin-top: 1rem;
                width: 100%;
            }
            
            .search-ai-section .d-flex {
                width: 100%;
                justify-content: flex-end;
            }
            
            .search-ai-section .form-inline {
                margin-right: 0;
            }
            
            .search-ai-section .input-group {
                min-width: 250px;
            }
        }

        /* Simple navbar improvements */
        .navbar-nav .nav-link {
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
            border-radius: 6px;
            margin: 0.25rem 0.5rem;
        }

        .navbar-nav .nav-link:hover {
            color: #007bff;
            background-color: #f8f9fa;
            transform: translateX(3px);
        }

        /* Login button emphasis */
        .navbar-nav .nav-link[href*="login"],
        .navbar-nav .nav-link[href*="login_clear"] {
            color: #ff6b35 !important;
            font-weight: 600;
        }

        .navbar-nav .nav-link[href*="login"]:hover,
        .navbar-nav .nav-link[href*="login_clear"]:hover {
            color: #e55a2b !important;
            transform: translateX(3px);
        }

        .navbar-nav hr {
            border-color: #e9ecef;
            margin: 0.5rem 1rem;
        }

        .navbar-nav .dropdown-menu {
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        .navbar-nav .dropdown-item {
            padding: 0.5rem 1rem;
            transition: background-color 0.2s ease;
        }

        .navbar-nav .dropdown-item:hover {
            background-color: #f8f9fa;
        }
    </style>
    
@endif
