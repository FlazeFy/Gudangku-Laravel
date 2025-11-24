@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  

@section('content')
    <div class="content">
        @include('others.notification')
        <h1 class="main-page-title">My Profile</h1>
        <div class="d-flex justify-content-<?php if(!$isMobile){ echo "start"; } else { echo "end"; } ?>">
            <a class="btn btn-danger btn-main top" href="/"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Back @endif</a>
            @include('profile.sign_out')
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
            <div class="col-lg-12 col-sm-12 d-flex">
                <div class="container-form" id="telegram-section">
                    <h2 class="fw-bold my-3">Lend Your Inventory</h2>
                    @include('profile.qr_lend')
                </div>
            </div>
        </div>
    </div>
@endsection
