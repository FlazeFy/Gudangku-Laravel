@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  

@section('content')
    <div class="content">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Custom Print</h1>
                <h3 class="mb-0">{{ucfirst($type)}}</h3>
            </div>
            <div>
                @include('components.profile')
                @include('components.notification')
            </div>
        </div>
        <hr>
        <div class="mb-3 d-flex flex-wrap gap-2">
            @include('components.back_button', ['route' => '/'])
        </div>
        
        @include('custom.worksheet')
    </div>
@endsection
