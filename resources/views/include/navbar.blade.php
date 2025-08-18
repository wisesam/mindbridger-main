<?php
    \vwmldbm\code\manage_lang(app()->getLocale(),'ccode'); // if changed, apply
    if(!isset($search_word)) $search_word=null; // search
    if(!isset($search_target)) $search_target=null; // search
    $mode=Request::segment(2); 
?>
@if($mode!='choose_list')     
    <div class="container text-center" id='wlibrary_back_btt' style='display:none;'>
        <button type='button' class='btn btn-success'  onClick="window.history.back();">{{__("Go back")}}</button>
    </div> 
    <nav class="navbar navbar-expand-lg bg-info navbar-dark flex-direction: column" id='wlibrary_navbar'>
        <script>
            $(document).ready(function() {                
             // This is a trick to hide the navigation bar from inner iframes
              if(window.parent.location != window.location) { 
                  document.getElementById('wlibrary_navbar').style.display='none'; 
                  document.getElementById('wlibrary_back_btt').style.display='';                
              }             
            });
        </script>
        <form class="form-inline" action='{{config('app.url')}}/book' name='form1'>        
            <div class="input-group mt-3 mb-3">                
                <input class="form-control" type="search" name='search_word' placeholder="{{__("Search Resource")}}" value='{{$search_word}}' aria-label="Search">
                <button class="btn btn-outline-dark ml-1" type="submit">{{__("Search")}}</button>
                <button class="btn btn-outline-dark ml-1" style="color:yellow;" onClick="window.location.href='{{config('app.url')}}/asearch/'" type="button">{{__("Advanced")}}</button>
            </div>
        </form>
        
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="collapsibleNavbar">
            <!-- Links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="{{config('app.url','/wlibrary')}}/">{{__('Home')}}</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="{{config('app.url','/wlibrary')}}/book" style="font-size: 125%; color: orange; font-weight: bold; line-height: 1; padding-top: 0.5rem;">{{__('Resources')}}</a>
                </li>

               @if(Auth::check() && !Auth::user()->isAdmin())
                <li class="nav-item">
                    <a class="nav-link" href="{{config('app.url','/wlibrary')}}/rental">{{__('Rentals')}}</a>
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
                        <a class="dropdown-item" href="{{config('app.url','/wlibrary')}}/about">{{__('About Library')}}</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" target='_blank' href="https://www.youtube.com/playlist?list=PLuAzRszLpwWr5-EbtEnwR43bzaiMwlSuy">{{__("Intro Videos")}}</a>
                        @auth
                            @if(Auth::user()->isAdmin())
                                <a class="dropdown-item" target='_blank' href="https://www.youtube.com/playlist?list=PLuAzRszLpwWqguLTm3r03IHkvl0d3Vdp3">{{__("Admin Tutorials")}}</a> 
                            @endif    
                        @endauth
                    </div>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{__("Announcement / BBS")}} 
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="{{config('app.url','/wlibrary')}}/announcement">{{__("Announcement")}}</a>                        
                        <!--div class="dropdown-divider"></div-->
                    </div>
                </li>
                @auth
                    @if(Auth::user()->isAdmin())
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAdmin" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {{__('Admin')}} 
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownAdmin">
                            <a class="dropdown-item" href="{{config('app.url','/wlibrary')}}/users/list">{{__("Users")}} </a>
                            <a class="dropdown-item" href="{{config('app.url','/wlibrary')}}/book">{{__("Resources")}}</a>                    
                            <a class="dropdown-item" href="{{config('app.url','/wlibrary')}}/book_copy">{{__("Resource Copies")}}</a>                    
                            <a class="dropdown-item" href="{{config('app.url','/wlibrary')}}/rental">{{__("Rentals")}}</a>                    
                            <a class="dropdown-item" href="{{config('app.url','/wlibrary')}}/vwmldbm" target="_blank">VWMLDBM</a>                    
                            <a class="dropdown-item" href="{{config('app.url','/wlibrary')}}/vwmldbm/batch" target="_blank">{{__("Batch Data")}}</a>                    
                        </div>
                    </li>
                    @endif
                @endauth
            </ul>

            <ul class="navbar-nav ml-auto">
                @guest                   
                    @if(config('app.multi_inst',''))
                        @if(session()->has('lib_inst'))
                            <li class="nav-item">
                                <a class="nav-link text-nowrap font-weight-bold text-warning" href="{{route('login')}}">{{__('Login')}}</a>
                            </li>
                        @endif
                            <li class="nav-item ">
                                <a class="nav-link text-nowrap text-warning" href="{{route('login_clear')}}">{{__('Inst')}}</a>
                            </li>
                    @else
                        <li class="nav-item ">
                            <a class="nav-link text-nowrap font-weight-bold text-warning" href="{{route('login')}}">{{__('Login')}}</a>
                        </li>
                    @endif         
                @else 
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown2" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            {{ Auth::user()->name }} <span class="caret"></span>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown2">
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

                    @if(false && Auth::user()->isAdmin())
                    <li class="nav-item">
                        <a class="btn btn-primary" href="{{config('app.url','/wlibrary')}}/register">Register</a>
                    </li> 
                    @endif
                @endguest
                <li class="nav-item">        
                    <?PHP            
                        echo \wlibrary\code\print_lang(null,app()->getLocale()," class='form-control btn-info nav-item' style='width:80px;'  onChange=\"window.location='".config('app.url','/wlibrary')."/locale/'+this.value;\"");
                    ?>
                </li>
            </ul>       
        </div>        
    </nav>
    
@endif
