@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  

@section('content')
    <div class="content">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="main-page-title">Calendar</h1>
            <div>
                @include('others.profile')
                @include('others.notification')
            </div>
        </div>
        <hr>
        <div class="mb-3">
            @include('components.back_button', ['route' => '/'])
        </div>
        
        @include('calendar.calendar')
    </div>
@endsection