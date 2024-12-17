@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  
@php($role = session()->get('role_key'))

@section('content')
    <div class="content" style="max-width:1080px;">
        @if($role == "user")
            @include('landing.dashboard')
        @endif
        <div class="row">
            @if(session()->get('role_key') == "user")
            <div class="col-lg-4 col-md-6 col-12 mx-auto" id="col-analyze">
                @include('landing.analyze')
            </div>
            @endif
            <div class="col-lg-4 col-md-6 col-12 mx-auto">
                <button class="btn-feature mb-3" onclick="location.href='/inventory';" id="nav_inventory_btn">
                    @if($isMobile)
                        <h2 style="font-size:var(--textJumbo);"><i class="fa-solid fa-warehouse me-2"></i> @if($role == "user") My @endif Inventory</h2>
                    @else
                        <i class="fa-solid fa-warehouse" style="font-size:100px"></i>
                        <h2 class="mt-3" style="font-size:var(--textJumbo);">@if($role == "user") My @endif Inventory</h2>
                    @endif
                </button>
            </div>
            <div class="col-lg-4 col-md-6 col-12 mx-auto">
                <button class="btn-feature mb-3" onclick="location.href='/stats';" id="nav_stats_btn">
                    @if($isMobile)
                        <h2 style="font-size:var(--textJumbo);"><i class="fa-solid fa-pie-chart me-2"></i> Stats</h2>
                    @else
                        <i class="fa-solid fa-pie-chart" style="font-size:100px"></i>
                        <h2 class="mt-3" style="font-size:var(--textJumbo);">Stats</h2>
                    @endif
                </button>
            </div>
            <div class="col-lg-4 col-md-6 col-12 mx-auto">
                <button class="btn-feature mb-3" onclick="location.href='/<?php if($role == 'user'){ echo 'calendar'; } else { echo 'user'; } ?>';">
                    @if($isMobile)
                        <h2 style="font-size:var(--textJumbo);"><i class="fa-solid fa-<?php if($role == "user"){ echo "calendar"; } else { echo "user"; } ?> me-2"></i> @if($role == "user") Calendar @else User @endif</h2>
                    @else
                        <i class="fa-solid fa-<?php if($role == "user"){ echo "calendar"; } else { echo "user"; } ?>" style="font-size:100px"></i>
                        <h2 class="mt-3" style="font-size:var(--textJumbo);">@if($role == "user") Calendar @else User @endif</h2>
                    @endif
                </button>
            </div>
            <div class="col-lg-4 col-md-6 col-12 mx-auto">
                <button class="btn-feature mb-3" onclick="location.href='/report';">
                    @if($isMobile)
                        <h2 style="font-size:var(--textJumbo);"><i class="fa-solid fa-scroll me-2"></i> Report</h2>
                    @else
                        <i class="fa-solid fa-scroll" style="font-size:100px"></i>
                        <h2 class="mt-3" style="font-size:var(--textJumbo);">Report</h2>
                    @endif
                </button>
            </div>
            <div class="col-lg-4 col-md-6 col-12 mx-auto">
                <button class="btn-feature mb-3" onclick="location.href='/history';" id="nav_history_btn">
                    @if($isMobile)
                        <h2 style="font-size:var(--textJumbo);"><i class="fa-solid fa-clock-rotate-left me-2"></i> History</h2>
                    @else
                        <i class="fa-solid fa-clock-rotate-left" style="font-size:100px"></i>
                        <h2 class="mt-3" style="font-size:var(--textJumbo);">History</h2>
                    @endif
                </button>
            </div>
            <div class="col-lg-4 col-md-6 col-12 mx-auto">
                <button class="btn-feature mb-3" onclick="location.href='/profile';" id="nav_profile_btn">
                    @if($isMobile)
                        <h2 style="font-size:var(--textJumbo);"><i class="fa-solid fa-user me-2"></i> My Profile</h2>
                    @else
                        <i class="fa-solid fa-user" style="font-size:100px"></i>
                        <h2 class="mt-3" style="font-size:var(--textJumbo);">My Profile</h2>
                    @endif
                </button>
            </div>
            @if(session()->get('role_key') != "user")
            <div class="col-lg-4 col-md-6 col-12 mx-auto">
                <button class="btn-feature mb-3" onclick="location.href='/error';" id="nav_error_history_btn">
                    @if($isMobile)
                        <h2 style="font-size:var(--textJumbo);"><i class="fa-solid fa-triangle-exclamation me-2"></i> Error History</h2>
                    @else
                        <i class="fa-solid fa-triangle-exclamation" style="font-size:100px"></i>
                        <h2 class="mt-3" style="font-size:var(--textJumbo);">Error History</h2>
                    @endif
                </button>
            </div>
            <div class="col-lg-4 col-md-6 col-12 mx-auto">
                <button class="btn-feature mb-3" onclick="location.href='/reminder';" id="nav_error_history_btn">
                    @if($isMobile)
                        <h2 style="font-size:var(--textJumbo);"><i class="fa-solid fa-bell me-2"></i> Reminder Mark</h2>
                    @else
                        <i class="fa-solid fa-bell" style="font-size:100px"></i>
                        <h2 class="mt-3" style="font-size:var(--textJumbo);">Reminder Mark</h2>
                    @endif
                </button>
            </div>
            @endif
        </div> 
    </div>
@endsection
