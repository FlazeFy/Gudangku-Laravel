@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  

@section('content')
    <div class="content">
        @include('others.notification')
        <h2 class="main-page-title">My Profile</h2>
        <div class="d-flex justify-content-<?php if(!$isMobile){ echo "start"; } else { echo "end"; } ?>">
            <a class="btn btn-danger btn-main top" href="/"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Back @endif</a>
            @include('profile.sign_out')
            <a class="btn btn-primary mb-3" href="/forgot"><i class="fa-solid fa-key" style="font-size:var(--textXLG);"></i> Change Password</a>
        </div>
        <h1 class="fw-bold my-3" style="font-size:calc(2*var(--textLG));">Profile</h1>
        @include('profile.profile')
        <hr class="mt-5"><h1 class="fw-bold my-3" style="font-size:calc(2*var(--textLG));">Telegram Account</h1>
        @include('profile.telegram')
    </div>
@endsection
