<?PHP
	if(!isset($numUsers)) {
		$numUsers=\Auth::user()::num('EXCEPT_ADMIN');
	}

	if(!isset($numBooks)) {
		$numBooks=App\Models\Book::where('inst',$_SESSION['lib_inst'])->get()->count();
	}

	if(!isset($numCopies)) {
		$numCopies=App\Models\Book_copy::where('inst',$_SESSION['lib_inst'])->get()->count();
	}
	
	if(!isset($numRentals)) {
		$numRentals=App\Models\Rental::num('RENTED');
	}

	$fpath=config('app.root')."/storage/app/ebook/{$_SESSION['lib_inst']}";
	if(file_exists($fpath)) {
		$fsize="(".\wlibrary\code\format_fsize(\wlibrary\code\dirSize($fpath),'MB',0)."MB)";
	}
	else $fsize=null;

?>
@extends('layouts.root')
@section('content')
    <div class="container mt-5">
        <div class="row">
			<!-- Users start -->
			<div class="col-md-3 mb-4">					  
				<div class="dashboard-card h-100">
					<div class="card-body text-center p-4">
						<div class="icon-wrapper mb-3">
							<a href="{{config('app.url','/wlibrary')}}/users/list" class="text-decoration-none">
								<div class="user-count">{{$numUsers}}</div>
								<img class="dashboard-icon" src="{{config('app.url','/wlibrary')}}/image/user.png?nocache=1" alt="Users">
							</a>
						</div>
						<h5 class="card-title mb-2">{{__("Users")}}</h5>
						<p class="card-text text-muted small">Manage user accounts</p>
					</div>
				</div>
			</div>
            <!-- Users end -->
            
			<!-- Book start -->
			<div class="col-md-3 mb-4">					  
				<div class="dashboard-card h-100">
					<div class="card-body text-center p-4">
						<div class="icon-wrapper mb-3">
							<a href="{{config('app.url','/wlibrary')}}/book" class="text-decoration-none">
								{{-- $numBooks | $numCopies 형태로 변경--}}
								<div class="book-count">{{$numBooks}}/{{$numCopies}}</div>
								<img class="dashboard-icon" src="{{config('app.url','/wlibrary')}}/image/book.png?nocache=1" alt="Resources">
							</a>
						</div>
						<div class="resource-info mb-2">
							<a href="{{config('app.url','/wlibrary')}}/book" class="text-decoration-none">
								<span class="resource-link">{{__("Resources")}}</span>
							</a>
							<span class="separator">|</span>
							<a href="{{config('app.url','/wlibrary')}}/book_copy" class="text-decoration-none">
								<span class="resource-link">{{__("Copies")}}</span>
							</a>
						</div>
						@if($fsize)
						<div class="storage-info">
							<small class="text-muted">{{$fsize}}</small>
						</div>
						@endif
					</div>
				</div>
			</div>
			<!-- Book end -->
			
            <!-- Rentals start -->
			<div class="col-md-3 mb-4">					  
				<div class="dashboard-card h-100">
					<div class="card-body text-center p-4">
						<div class="icon-wrapper mb-3">
							<a href="{{config('app.url','/wlibrary')}}/rental/?mode=RENTED" class="text-decoration-none">
								<div class="rental-count">{{$numRentals}}</div>
								<img class="dashboard-icon" src="{{config('app.url','/wlibrary')}}/image/rental3.png?nocache=1" alt="Rentals">
							</a>
						</div>
						<h5 class="card-title mb-2">{{__("Rentals")}}</h5>
						<p class="card-text text-muted small">Currently rented items</p>
					</div>
				</div>
			</div>
            <!-- Rentals end -->

			<!-- Reserves start -->
			<div class="col-md-3 mb-4">					  
				<div class="dashboard-card h-100">
					<div class="card-body text-center p-4">
						<div class="icon-wrapper mb-3">
							<div class="reserve-count">0</div>
							<img class="dashboard-icon" src="{{config('app.url','/wlibrary')}}/image/reserve1.png?nocache=1" alt="Reserves">
						</div>
						<h5 class="card-title mb-2">{{__("Reserves")}}</h5>
						<p class="card-text text-muted small">Reserved items</p>
					</div>
				</div>
			</div>
            <!-- Reserves end -->
        </div>
	</div>
	
	<style>
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
		
		.user-count, .book-count, .rental-count, .reserve-count, .copy-count {
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
		.dashboard-card:hover .copy-count {
			transform: scale(1.1);
		}
		
		.resource-info {
			display: flex;
			justify-content: center;
			align-items: center;
			gap: 8px;
		}
		
		.resource-link {
			color: #007bff;
			font-weight: 500;
			transition: color 0.3s ease;
		}
		
		.resource-link:hover {
			color: #0056b3;
		}
		
		.separator {
			color: #6c757d;
			font-weight: bold;
		}
		
		.copy-number {
			color: #6f42c1;
			font-weight: bold;
			font-size: 18px;
		}
		
		.storage-info {
			margin-top: 8px;
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
	
	{{-- <div class='mt-3 mb-0'>
		@include('announcement.small_list')
	</div> --}}
	<div class='mt-0'>
		@include('root.carousel')
	</div>	
@endsection('content')

