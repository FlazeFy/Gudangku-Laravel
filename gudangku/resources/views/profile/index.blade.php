@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  
@php($role = session()->get('role_key'))

@section('content')
    <div class="content">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="main-page-title">My Profile</h1>
            <div>
                @include('profile.sign_out')
                @include('others.notification')
            </div>
        </div>
        <hr>
        <div class="mb-3 d-flex flex-wrap gap-2">
            @include('components.back_button', ['route' => '/'])
            <span id="change-pass-button-holder"></span>
        </div>

        <div class="row d-flex align-items-stretch">
            <div class="col-lg-6 col-sm-12 d-flex">
                <div class="container-form" id="profile-section">
                    <h2 class="fw-bold my-3">Profile</h2>
                    @include('profile.profile')
                </div>
            </div>
            <div class="col-lg-6 col-sm-12 d-flex">
                <div class="container-form" id="telegram-section">
                    <h2 class="fw-bold my-3">Telegram Account</h2>
                    @include('profile.telegram')
                </div>
            </div>
            @if($role == 0)
            <div class="col-lg-12 col-sm-12 d-flex">
                <div class="container-form" id="telegram-section">
                    <h2 class="fw-bold my-3">Lend Your Inventory</h2>
                    @include('profile.qr_lend')
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection
