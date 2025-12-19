@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  

@section('content')
    <div class="content">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="main-page-title">Report Detail</h1>
            <div>
                @include('components.profile')
                @include('components.notification')
            </div>
        </div>
        <hr>
        <div class="mb-3 d-flex flex-wrap justify-content-between gap-2">
            <div class="d-flex flex-wrap gap-2">
                @include('components.back_button', ['route' => '/'])
                @include('report.detail.toogle_edit')  
                @include('report.detail.delete')
                @include('report.detail.add')    
                <div id="btn-doc-preview-holder"></div>
            </div>
            <div class='text-start text-md-end'>
                <p class='date-text mb-0'>Created At : <span id='created_at'></span></p>
                <p class='date-text mb-0'>Last Updated : <span id='updated_at'></span></p>
            </div>
        </div>

        @include('report.detail.info')  
    </div>
@endsection
