@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  

@section('content')
    <div class="content">
        <div class="d-flex justify-content-between align-items-center">
            <h1>Features</h1>
            @if(session()->get("token_key"))
                <div>
                    @include('components.profile')
                    @include('components.notification')
                </div>
            @endif
        </div>
        <hr>  
        <div class="mb-3 d-flex flex-wrap gap-2">      
            @include('components.back_button', ['route' => '/'])
        </div>
        @include('features.list')
    </div>
@endsection
