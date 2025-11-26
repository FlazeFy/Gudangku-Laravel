@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  
@php($role = session()->get('role_key'))

@section('content')
    <div class="content">
        @if($role == 0)
            @include('landing.dashboard')
        @endif
        <div class="row g-3 align-items-stretch">
            @if($role == 0)
            <div class="col-lg-3 col-md-4 col-sm-6 col-6 mx-auto h-100" id="col-chat">
                <button class="btn-feature w-100 mb-3 d-flex flex-column justify-content-center align-items-center h-100" onclick="location.href='/chat';" id="nav_chat_btn">
                    <i class="fa-solid fa-robot d-block mx-auto fs-1 fs-sm-2 fs-md-3 fs-lg-1"></i>
                    <h2 class="mt-3 text-center" style="font-size:var(--textJumbo);">Chat</h2>
                </button>
            </div>
            @endif
            <div class="col-lg-3 col-md-4 col-sm-6 col-6 mx-auto h-100">
                <button class="btn-feature w-100 mb-3 d-flex flex-column justify-content-center align-items-center h-100" onclick="location.href='/inventory';" id="nav_inventory_btn">
                    <i class="fa-solid fa-warehouse d-block mx-auto fs-1 fs-sm-2 fs-md-3 fs-lg-1"></i>
                    <h2 class="mt-3 text-center" style="font-size:var(--textJumbo);">@if($role == 0) My @endif Inventory</h2>
                </button>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 col-6 mx-auto h-100">
                <button class="btn-feature w-100 mb-3 d-flex flex-column justify-content-center align-items-center h-100" onclick="location.href='/stats';" id="nav_stats_btn">
                    <i class="fa-solid fa-pie-chart d-block mx-auto fs-1 fs-sm-2 fs-md-3 fs-lg-1"></i>
                    <h2 class="mt-3 text-center" style="font-size:var(--textJumbo);">Stats</h2>
                </button>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 col-6 mx-auto h-100">
                <button class="btn-feature w-100 mb-3 d-flex flex-column justify-content-center align-items-center h-100" onclick="location.href='/{{ $role == 0 ? 'calendar' : 'user' }}';">
                    <i class="fa-solid fa-{{ $role == 0 ? 'calendar' : 'user' }} d-block mx-auto fs-1 fs-sm-2 fs-md-3 fs-lg-1"></i>
                    <h2 class="mt-3 text-center" style="font-size:var(--textJumbo);">{{ $role == 0 ? 'Calendar' : 'User' }}</h2>
                </button>
            </div>

            <div class="col-lg-3 col-md-4 col-sm-6 col-6 mx-auto h-100">
                <button class="btn-feature w-100 mb-3 d-flex flex-column justify-content-center align-items-center h-100" onclick="location.href='/report';" id="nav_report_btn">
                    <i class="fa-solid fa-scroll d-block mx-auto fs-1 fs-sm-2 fs-md-3 fs-lg-1"></i>
                    <h2 class="mt-3 text-center" style="font-size:var(--textJumbo);">Report</h2>
                </button>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 col-6 mx-auto h-100">
                <button class="btn-feature w-100 mb-3 d-flex flex-column justify-content-center align-items-center h-100" onclick="location.href='/history';" id="nav_history_btn">
                    <i class="fa-solid fa-clock-rotate-left d-block mx-auto fs-1 fs-sm-2 fs-md-3 fs-lg-1"></i>
                    <h2 class="mt-3 text-center" style="font-size:var(--textJumbo);">History</h2>
                </button>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 col-6 mx-auto h-100">
                <button class="btn-feature w-100 mb-3 d-flex flex-column justify-content-center align-items-center h-100" onclick="location.href='/profile';" id="nav_profile_btn">
                    <i class="fa-solid fa-user d-block mx-auto fs-1 fs-sm-2 fs-md-3 fs-lg-1"></i>
                    <h2 class="mt-3 text-center" style="font-size:var(--textJumbo);">My Profile</h2>
                </button>
            </div>
            @if($role == 1)
            <div class="col-lg-3 col-md-4 col-sm-6 col-6 mx-auto h-100">
                <button class="btn-feature w-100 mb-3 d-flex flex-column justify-content-center align-items-center h-100" onclick="location.href='/error';" id="nav_error_history_btn">
                    <i class="fa-solid fa-triangle-exclamation d-block mx-auto fs-1 fs-sm-2 fs-md-3 fs-lg-1"></i>
                    <h2 class="mt-3 text-center" style="font-size:var(--textJumbo);">Error History</h2>
                </button>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 col-6 mx-auto h-100">
                <button class="btn-feature w-100 mb-3 d-flex flex-column justify-content-center align-items-center h-100" onclick="location.href='/reminder';" id="nav_reminder_btn">
                    <i class="fa-solid fa-bell d-block mx-auto fs-1 fs-sm-2 fs-md-3 fs-lg-1"></i>
                    <h2 class="mt-3 text-center" style="font-size:var(--textJumbo);">Reminder Mark</h2>
                </button>
            </div>
            @endif
        </div>

    </div>
@endsection
