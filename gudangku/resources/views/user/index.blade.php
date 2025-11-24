@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  

@section('content')
    <script>
        let page = 1
    </script>
    <div class="content">
        @include('others.profile')
        @include('others.notification')
        <h1 class="mb-4">User</h1>
        <a class="btn btn-danger mb-3 me-2" href="/"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Back @endif</a>
        <form action='/user/save_as_csv' method='POST' class='d-inline'>
            @csrf
            <button class="btn btn-primary mb-3 me-2" href=""><i class="fa-solid fa-print" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Print @endif</button>
        </form>
        <div class="row">
            <div class="col-lg-4 col-md-5 col-sm-12">
                <div class="container bordered" id="last_login-section">
                    <h2 class="fw-bold my-3">Last Login</h2>
                    @include('user.last_login')
                </div>      
            </div>
            <div class="col-lg-8 col-md-7 col-sm-12">
                <div class="container bordered" id="last_login-section">
                    <h2 class="fw-bold my-3">Leaderboard</h2>
                    @include('user.leaderboard')
                </div>      
            </div>
        </div>
        <h2 class="fw-bold my-3">All User</h2>
        @include('user.table')
    </div>
@endsection