@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  

@section('content')
    <div class="content">
        @if(session()->get("token_key"))
            @include('components.profile')
            @include('components.notification')
        @endif
        <h1 class="main-page-title">Features</h1>
        <div class="d-flex justify-content-start">
            <a class="btn btn-danger btn-main top" href="/"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Back @endif</a>
        </div>
        @include('features.list')
    </div>
@endsection
