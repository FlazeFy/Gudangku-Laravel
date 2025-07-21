@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  

@section('content')
    <!-- JS Collection -->
    <script src="{{ asset('/usecases/fav_toogle_inventory_by_id_v1.0.0.js')}}"></script>
    <script src="{{ asset('/usecases/destroy_reminder_by_id_v1.0.js')}}"></script>

    <div class="content">
        @include('others.profile')
        @include('others.notification')
        <h2 class="text-white fw-bold mb-4" style="font-size:36px;">Edit Inventory</h2>
        <div class='d-flex justify-content-between'>
            <div id="edit_toolbar-section">
                <a class="btn btn-danger mb-3 me-2" href="/inventory"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> Back</a>
                <span id='btn-toogle-fav-holder'></span>
                <a class="btn btn-primary mb-3 me-2" href="/doc/inventory/{{$id}}"><i class="fa-solid fa-print" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Print @endif</a>
                <a class="btn btn-primary mb-3 me-2" href="/doc/inventory/{{$id}}/custom"><i class="fa-solid fa-pen-to-square" style="font-size:var(--textXLG);"></i> Print Custom</a>
                <a class="btn btn-primary mb-3 me-2" href="/analyze/inventory/{{$id}}" id="analyze-button"><i class="fa-solid fa-chart-simple" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Analyze @endif</a>
            </div>
            @if(!$isMobile)
            <div class='text-end'>
                <h6 class='date-text'>Created At : <span id='created_at'></span></h6>
                <h6 class='date-text'>Last Updated : <span id='updated_at'></span></h6>
            </div>
            @endif
        </div>
        @if($isMobile)
        <div class='text-start'>
            <h6 class='date-text'>Created At : <span id='created_at'></span></h6>
            <h6 class='date-text'>Last Updated : <span id='updated_at'></span></h6>
        </div>
        @endif
        @include('edit.add_report')
        @include('edit.add_reminder')
        @include('edit.form')
    </div>
    <script>
        is_process = false
        is_submit = false
        window.addEventListener('beforeunload', function(event) {
            is_process = check_filling_status(['report_title','report_desc','item_desc','reminder_desc'])
            console.log(is_process)
            if(is_process == true && !is_submit){
                event.preventDefault()
                event.returnValue = ''
            }
        });
    </script>
@endsection
