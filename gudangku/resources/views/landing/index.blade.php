@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  

@section('content')
    <div class="content" style="max-width:1080px;">
        @include('landing.dashboard')
        <div class="row">
            <div class="col-lg-4 col-md-6 col-12">
                <button class="btn-feature mb-3" onclick="location.href='/inventory';" id="nav_inventory_btn">
                    @if($isMobile)
                        <h2 style="font-size:var(--textJumbo);"><i class="fa-solid fa-warehouse me-2"></i> My Inventory</h2>
                    @else
                        <i class="fa-solid fa-warehouse" style="font-size:100px"></i>
                        <h2 class="mt-3" style="font-size:var(--textJumbo);">My Inventory</h2>
                    @endif
                </button>
            </div>
            <div class="col-lg-4 col-md-6 col-12">
                <button class="btn-feature mb-3" onclick="location.href='/stats';" id="nav_stats_btn">
                    @if($isMobile)
                        <h2 style="font-size:var(--textJumbo);"><i class="fa-solid fa-pie-chart me-2"></i> Stats</h2>
                    @else
                        <i class="fa-solid fa-pie-chart" style="font-size:100px"></i>
                        <h2 class="mt-3" style="font-size:var(--textJumbo);">Stats</h2>
                    @endif
                </button>
            </div>
            <div class="col-lg-4 col-md-6 col-12">
                <button class="btn-feature mb-3" onclick="location.href='/calendar';">
                    @if($isMobile)
                        <h2 style="font-size:var(--textJumbo);"><i class="fa-solid fa-calendar me-2"></i> Calendar</h2>
                    @else
                        <i class="fa-solid fa-calendar" style="font-size:100px"></i>
                        <h2 class="mt-3" style="font-size:var(--textJumbo);">Calendar</h2>
                    @endif
                </button>
            </div>
            <div class="col-lg-4 col-md-6 col-12">
                <button class="btn-feature mb-3" onclick="location.href='/report';">
                    @if($isMobile)
                        <h2 style="font-size:var(--textJumbo);"><i class="fa-solid fa-scroll me-2"></i> Report</h2>
                    @else
                        <i class="fa-solid fa-scroll" style="font-size:100px"></i>
                        <h2 class="mt-3" style="font-size:var(--textJumbo);">Report</h2>
                    @endif
                </button>
            </div>
            <div class="col-lg-4 col-md-6 col-12">
                <button class="btn-feature mb-3" onclick="location.href='/history';" id="nav_history_btn">
                    @if($isMobile)
                        <h2 style="font-size:var(--textJumbo);"><i class="fa-solid fa-clock-rotate-left me-2"></i> History</h2>
                    @else
                        <i class="fa-solid fa-clock-rotate-left" style="font-size:100px"></i>
                        <h2 class="mt-3" style="font-size:var(--textJumbo);">History</h2>
                    @endif
                </button>
            </div>
            <div class="col-lg-4 col-md-6 col-12">
                <button class="btn-feature mb-3" onclick="location.href='/profile';" id="nav_profile_btn">
                    @if($isMobile)
                        <h2 style="font-size:var(--textJumbo);"><i class="fa-solid fa-user me-2"></i> My Profile</h2>
                    @else
                        <i class="fa-solid fa-user" style="font-size:100px"></i>
                        <h2 class="mt-3" style="font-size:var(--textJumbo);">My Profile</h2>
                    @endif
                </button>
            </div>
        </div> 
    </div>
@endsection
