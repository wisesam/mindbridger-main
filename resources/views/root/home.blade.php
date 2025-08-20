<?PHP
if(!isset($numRentals)) {
    $numRentals=App\Models\Rental::num('RENTED',Auth::user()->id);
}
?>

@extends('layouts.root')
@section('content')
<img class="d-block" style="width: 100vw; height: 400px; margin-top: 0px; object-fit: cover;" src="image/wallpaper.png?nocache=4"/>    
    <div class="container mt-5">
        <div class="row">
            <!-- Resources start -->
            <div class="col-md-3 mb-4">					  
                <div class="dashboard-card h-100">
                    <div class="card-body text-center p-4">
                        <div class="icon-wrapper mb-3">
                            <a href="{{config('app.url','/wlibrary')}}/book" class="text-decoration-none">
                                <img class="dashboard-icon" src="image/book.png?nocache=4" alt="Resources">
                            </a>
                        </div>
                        <h5 class="card-title mb-2">{{__('Resources')}}</h5>
                        <p class="card-text text-muted small">{{__('Browse our collection')}}</p>
                    </div>
                </div>
            </div>
            <!-- Resources end -->
            
            <!-- Rentals start -->
            <div class="col-md-3 mb-4">					  
                <div class="dashboard-card h-100">
                    <div class="card-body text-center p-4">
                        <div class="icon-wrapper mb-3">
                            <a href="{{config('app.url','/wlibrary')}}/rental" class="text-decoration-none">
                                <div class="rental-count">{{$numRentals}}</div>
                                <img class="dashboard-icon" src="image/rental3.png?nocache=1" alt="Rentals">
                            </a>
                        </div>
                        <h5 class="card-title mb-2">{{__('Rentals')}}</h5>
                        <p class="card-text text-muted small">{{__('Your borrowed items')}}</p>
                    </div>
                </div>
            </div>
            <!-- Rentals end -->
            
            <!-- Favorites start -->
            <div class="col-md-3 mb-4">					  
                <div class="dashboard-card h-100">
                    <div class="card-body text-center p-4">
                        <div class="icon-wrapper mb-3">
                            <a href="{{config('app.url','/wlibrary')}}/favorite" class="text-decoration-none">
                                <div class="favorite-count">{{$numFavorites}}</div>
                                <img class="dashboard-icon" src="image/favorite1.png?nocache=1" alt="Favorites">
                            </a>
                        </div>
                        <h5 class="card-title mb-2">{{__('Favorites')}}</h5>
                        <p class="card-text text-muted small">{{__('Your saved items')}}</p>
                    </div>
                </div>
            </div>
            <!-- Favorites end -->
            
            <!-- e-Shelf start -->
            <div class="col-md-3 mb-4">					  
                <div class="dashboard-card h-100">
                    <div class="card-body text-center p-4">
                        <div class="icon-wrapper mb-3">
                            <a href="{{config('app.url','/wlibrary')}}/eshelf" class="text-decoration-none">
                                <div class="eshelf-count">{{$numEshelf}}</div>
                                <img class="dashboard-icon" src="image/shelf.png?nocache=1" alt="e-Shelf">
                            </a>
                        </div>
                        <h5 class="card-title mb-2">{{__('e-Shelf')}}</h5>
                        <p class="card-text text-muted small">{{__('Your personal shelf')}}</p>
                    </div>
                </div>
            </div>
            <!-- e-Shelf end -->
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
        }
        
        .dashboard-card:hover .dashboard-icon {
            transform: scale(1.1);
        }
        
        .rental-count, .favorite-count, .eshelf-count {
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
    <div class='mt-3'>
        @include('root.carousel')
    </div>
@endsection('content')
