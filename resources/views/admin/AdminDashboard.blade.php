<?PHP
	if(!isset($numUsers)) {
		$numUsers=\Auth::user()::num('EXCEPT_ADMIN');
	}

	if(!isset($numBooks)) {
		$numBooks=App\Book::where('inst',$_SESSION['lib_inst'])->get()->count();
	}

	if(!isset($numCopies)) {
		$numCopies=App\Book_copy::where('inst',$_SESSION['lib_inst'])->get()->count();
	}
	
	if(!isset($numRentals)) {
		$numRentals=App\Rental::num('RENTED');
	}

	$fpath=config('app.root')."/storage/app/ebook/{$_SESSION['lib_inst']}";
	if(file_exists($fpath)) {
		$fsize="(".\wlibrary\code\format_fsize(\wlibrary\code\dirSize($fpath),'MB',0)."MB)";
	}
	else $fsize=null;

?>
@extends('layouts.root')
@section('content')
    <div class="container">
        <div class="row">
			<!-- Users start -->
			<div class="col-md-3">					  
				<div class="dashboardbox">				  
					<div class="dashb font-weight-bold">
					  <div class="d-inline-flex">
						<a href="{{config('app.url','/wlibrary')}}/users/list">
							<span class="dashb_num">{{$numUsers}}</span>
						</a>
					  </div >
					  <div>
                        <img class="d-block w-100" src="{{config('app.url','/wlibrary')}}/image/user.png?nocache=1"  width='100%' height='100%'>
					  </div>
					</div>
					<div class="dashb font-weight-bold">{{__("Users")}}</div>
				</div>
			</div>
            <!-- Users end -->
			<!-- Book start -->
			<div class="col-md-3">					  
				<div class="dashboardbox">
					<div class="dashb">
					  <div class="d-inline-flex">
						<a href="{{config('app.url','/wlibrary')}}/book">  
							<span class="dashb_num">{{$numBooks}}</span>
						</a><span style='color:purple;line-height:40px;'><?=$fsize?></span>
						<a href="#"><span class="dashb_num"> &nbsp;|&nbsp; </span></a>
						<a href="{{config('app.url','/wlibrary')}}/book_copy">  
							<span class="dashb_num">{{$numCopies}}</span>
						</a>

					  </div >
					  <div>
                        <img class="d-block w-100" src="{{config('app.url','/wlibrary')}}/image/book.png?nocache=1"  width='100%' height='100%'>
					  </div>
					</div>
					<div class="dashb font-weight-bold" style='font'>{{__("Resources | Copies")}}</div>
				</div>
			</div>
			<!-- Book end -->
			
            <!-- Rentals start -->
			<div class="col-md-3">					  
				<div class="dashboardbox">
					<div class="dashb">
					  <div class="d-inline-flex">
						<span class="dashb_num">
							<a href="{{config('app.url','/wlibrary')}}/rental/?mode=RENTED">
								<span class="dashb_num">{{$numRentals}}</span>
							</a>
						</span>
					  </div >
					  <div>
						<img class="d-block w-100" src="{{config('app.url','/wlibrary')}}/image/rental3.png?nocache=1"  width='100%' height='100%'>
					  </div>
					</div>
					<div class="dashb font-weight-bold">{{__("Rentals")}}</div>
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
						<img class="d-block w-100" src="{{config('app.url','/wlibrary')}}/image/reserve1.png?nocache=1"  width='100%' height='100%'>
					  </div>
					</div>
					<div class="dashb font-weight-bold">{{__("Reserves")}}</div>
				</div>
			</div>
            <!-- Reserves end -->
        </div>
	</div> 
	<div class='mt-3 mb-0'>
		@include('announcement.small_list')
	</div>
	<div class='mt-0'>
		@include('root.carousel')
	</div>	
@endsection('content')

