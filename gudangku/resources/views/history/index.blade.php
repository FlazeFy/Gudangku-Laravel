@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  
@php($role = session()->get('role_key'))

@section('content')
    <script src="{{ asset('/usecases/history_v1.0.js')}}"></script>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="main-page-title">History</h1>
            <div>
                @include('others.profile')
                @include('others.notification')
            </div>
        </div>
        <hr>  
        <div class="mb-3 d-flex flex-wrap gap-2">      
            @include('components.back_button', ['route' => '/'])
            <span id="export-section"></span>
        </div>
        
        @if($role == 0)
            @include('history.list')
        @else   
            @include('history.table')
        @endif
    </div>
@endsection
