@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  

@section('content')
    <div class="content" style="max-width:1440px;">
        <h2 class="text-white fw-bold mb-4" style="font-size:<?php if(!$isMobile){ echo "calc(var(--textXJumbo)*1.75)"; } else { echo "var(--textXJumbo)"; } ?>">My Profile</h2>
        <div class="d-flex justify-content-start">
            <a class="btn btn-danger mb-3 me-2" href="/"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Back @endif</a>
            @include('profile.sign_out')
            <a class="btn btn-primary mb-3 me-2" href="/forgot"><i class="fa-solid fa-key" style="font-size:var(--textXLG);"></i> Change Password</a>
        </div>
        @include('profile.profile')
    </div>
@endsection
