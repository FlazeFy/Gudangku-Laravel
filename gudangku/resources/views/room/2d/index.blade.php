@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  

@section('content')
    <link rel="stylesheet" href="{{ asset('/room_v1.0.css') }}"/>

    <div class="content">
        @include('others.profile')
        @include('others.notification')
        <h1 class="main-page-title">2D Room</h1>
        <div class="d-flex justify-content-start">
            <a class="btn btn-danger mb-3 me-2" href="/inventory"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Back @endif</a>
            @include('room.select_room')
        </div>
        @include('room.2d.room')
    </div>
@endsection
