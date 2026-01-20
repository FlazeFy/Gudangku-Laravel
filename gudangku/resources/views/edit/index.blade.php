@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  

@section('content')
    <!-- JS Collection -->
    <script src="{{ asset('/usecases/inventory_v1.0.js')}}"></script>
    <script src="{{ asset('/usecases/reminder_v1.0.js')}}"></script>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center">
            <h1>Edit Inventory</h1>
            <div>
                @include('components.profile')
                @include('components.notification')
            </div>
        </div>
        <hr>
        <div class="mb-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex flex-wrap gap-2">
                @include('components.back_button', ['route' => '/inventory'])
                <span id='btn-toogle-fav-holder'></span>
                <a class="btn btn-primary" href="/analyze/inventory/{{$id}}" id="analyze-button"><i class="fa-solid fa-chart-simple"></i> Analyze</a>
                <a class="btn btn-primary" href="/doc/inventory/{{$id}}"><i class="fa-solid fa-print"></i> Print</a>
                <a class="btn btn-primary" href="/doc/inventory/{{$id}}/custom"><i class="fa-solid fa-pen-to-square"></i> Custom Print</a>
            </div>
            <div class='text-start text-md-end'>
                <p class='date-text mb-0'>Created At : <span id='created_at'></span></p>
                <p class='date-text mb-0'>Last Updated : <span id='updated_at'></span></p>
            </div>
        </div>
        @include('edit.add_report')
        @include('edit.add_reminder')
        @include('edit.form')
    </div>
@endsection
