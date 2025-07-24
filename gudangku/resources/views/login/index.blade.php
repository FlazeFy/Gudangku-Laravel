@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice()) 

@section('content')
    <script>
        is_process = false
        is_submit = false
        window.addEventListener('beforeunload', function(event) {
            is_process = check_filling_status(['username','password'])
            if(is_process == true && !is_submit){
                event.preventDefault()
                event.returnValue = ''
            }
        });
    </script>
    <div class="content" style="width:100vw; max-width:1480px;">
        <div class="row">
            <div class="col-xl-8 col-lg-7 col-md-6">
                @if($isMobile)
                    <br><br>
                    @include('login.form')
                    <hr><br>
                    @include('login.landing')
                @else
                    <br><br>
                    @include('features.list')
                    <hr><br>
                @endif
                @include('components.about')
                <br><br>
            </div>
            <div class="col-xl-4 col-lg-5 col-md-6">
                @if(!$isMobile)
                <div class="position-sticky" style="top:25vh;">
                    @include('login.form')
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
