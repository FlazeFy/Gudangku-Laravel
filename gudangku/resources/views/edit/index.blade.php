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
        @include('others.profile')
        @include('others.notification')
        <h1 class="main-page-title">Edit Inventory</h1>
        <div class='d-flex justify-content-between align-items-center'>
            <div id="edit_toolbar-section">
                <a class="btn btn-danger mb-3 me-2" href="/inventory"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> Back</a>
                <span id='btn-toogle-fav-holder'></span>
                <a class="btn btn-primary mb-3 me-2" href="/doc/inventory/{{$id}}"><i class="fa-solid fa-print" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Print @endif</a>
                <a class="btn btn-primary mb-3 me-2" href="/doc/inventory/{{$id}}/custom"><i class="fa-solid fa-pen-to-square" style="font-size:var(--textXLG);"></i> Print Custom</a>
                <a class="btn btn-primary mb-3 me-2" href="/analyze/inventory/{{$id}}" id="analyze-button"><i class="fa-solid fa-chart-simple" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Analyze @endif</a>
            </div>
            @if(!$isMobile)
            <div class='text-end'>
                <p class='date-text mb-0'>Created At : <span id='created_at'></span></p>
                <p class='date-text mb-0'>Last Updated : <span id='updated_at'></span></p>
            </div>
            @endif
        </div>
        @if($isMobile)
        <div class='text-start'>
            <p class='date-text mb-0'>Created At : <span id='created_at'></span></p>
            <p class='date-text mb-0'>Last Updated : <span id='updated_at'></span></p>
        </div>
        @endif
        @include('edit.add_report')
        @include('edit.add_reminder')
        @include('edit.form')
    </div>
@endsection
