@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice()) 

@section('content')
    <style>
        @media (max-width: 767px) {
            .translate-middle-y {
                transform: none !important;
            }
        }
    </style>
    <script>
        is_process = false
        is_submit = false
        window.addEventListener('beforeunload', function(event) {
            is_process = checkFillingStatus(['username','password'])
            if(is_process == true && !is_submit){
                event.preventDefault()
                event.returnValue = ''
            }
        })
    </script>

    <div class="content" style="width:100vw; max-width:1480px;">
        <div class="row pt-5 pt-sm-0">
            <div class="col-xl-8 col-lg-7 col-md-6 order-2 order-md-1">
                <br><br>
                <div class="d-md-none">
                    @include('login.landing')
                    <hr><br>
                </div>
                <div class="d-none d-md-block">
                    @include('features.list')
                    <hr><br>
                </div>
                @include('components.about')
                <br><br>
            </div>
            <div class="col-xl-4 col-lg-5 col-md-6 order-1 order-md-2">
                <div class="container-form position-sticky position-md-relative top-50 translate-middle-y">
                    @include('login.form')
                </div>
            </div>
        </div>
    </div>
@endsection
