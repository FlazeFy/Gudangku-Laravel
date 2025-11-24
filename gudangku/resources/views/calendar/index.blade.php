@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  

@section('content')
    <div class="content">
        @include('others.profile')
        @include('others.notification')
        <h1 class="main-page-title">Calendar</h1>
        <div class="d-flex justify-content-start">
            <a class="btn btn-danger mb-3 me-2" href="/inventory"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Back @endif</a>
        </div>
        @include('calendar.calendar')
    </div>
@endsection