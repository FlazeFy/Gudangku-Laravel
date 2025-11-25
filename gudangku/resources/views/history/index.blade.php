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
                <span id="export-section"></span>
                @include('others.profile')
                @include('others.notification')
            </div>
        </div>
        <hr>  
        <div class="mb-3">      
            @include('components.back_button', ['route' => '/'])
        </div>
        
        @if($role == 0)
            @include('history.list')
        @else   
            @include('history.table')
        @endif
    </div>
@endsection
