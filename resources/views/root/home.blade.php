<?PHP
if(!isset($numRentals)) {
    $numRentals=App\Rental::num('RENTED',Auth::user()->id);
}
?>

@extends('layouts.root')
@section('content')
    <div class="container">
        <div class="row">
            <!-- Rentals start -->
            <div class="col-md-3">					  
                <div class="dashboardbox">
                    <div class="dashb">
                        <div class="d-inline-flex">
                            <a href="{{config('app.url','/wlibrary')}}/rental">
								<span class="dashb_num">{{$numRentals}}</span>
							</a>
                        </div >
                        <div>
                            <img class="d-block w-100" src="image/rental3.png?nocache=1"  width='100%' height='100%'>
                        </div>
                    </div>
                    <div class="dashb">{{__('Rentals')}}</div>
                </div>
            </div>
            <!-- Rentals end -->
            <!-- Reserves start -->
            <div class="col-md-3">					  
                <div class="dashboardbox">
                    <div class="dashb">
                        <div class="d-inline-flex">
                            <span class="dashb_num">0</span>
                        </div >
                        <div>
                            <img class="d-block w-100" src="image/reserve1.png?nocache=1"  width='100%' height='100%'>
                        </div>
                    </div>
                    <div class="dashb">{{__('Reserves')}}</div>
                </div>
            </div>
            <!-- Reserves end -->
            <!-- Favorites start -->
            <div class="col-md-3">					  
                <div class="dashboardbox">
                    <div class="dashb">
                        <div class="d-inline-flex">
                            <a href="{{config('app.url','/wlibrary')}}/favorite">
								<span class="dashb_num">{{$numFavorites}}</span>
							</a>
                        </div >
                        <div>
                            <img class="d-block w-100" src="image/favorite1.png?nocache=1"  width='100%' height='100%'>
                        </div>
                    </div>
                    <div class="dashb">{{__('Favorites')}}</div>
                </div>
            </div>
            <!-- Favorites end -->
            <!-- e-Shelf start -->
            <div class="col-md-3">					  
                <div class="dashboardbox">
                    <div class="dashb">
                        <div class="d-inline-flex">
                            <span class="dashb_num">0</span>
                        </div >
                        <div>
                            <img class="d-block w-100" src="image/shelf.png?nocache=1"  width='100%' height='100%'>
                        </div>
                    </div>
                    <div class="dashb">{{__('e-Shelf')}}</div>
                </div>
            </div>
            <!-- e-Shelf end -->
        </div>
    </div>
    <div class='mt-3 mb-0'>
		@include('announcement.small_list')
	</div>
    <div class='mt-0'>
        @include('root.carousel')
    </div>
@endsection('content')
