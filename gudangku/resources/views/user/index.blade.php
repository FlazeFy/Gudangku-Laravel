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
        <div class="d-flex justify-content-between align-items-center">
            <h1>User</h1>
            <div>
                @include('components.profile')
                @include('components.notification')
            </div>
        </div>
        <hr>
        <div class="mb-3 d-flex flex-wrap gap-2">
            @include('components.back_button', ['route' => '/'])
            <form action='/user/save_as_csv' method='POST' class='d-inline'>
                @csrf
                <button class="btn btn-primary"><i class="fa-solid fa-print"></i> Print</button>
            </form>
        </div>

        <div class="row">
            <div class="col-lg-4 col-md-5 col-sm-12">
                <div class="container bordered" id="last_login-section">
                    <h2 class="my-3">Last Login</h2>
                    @include('user.last_login')
                </div>      
            </div>
            <div class="col-lg-8 col-md-7 col-sm-12">
                <div class="container bordered" id="last_login-section">
                    <h2 class="my-3">Leaderboard</h2>
                    @include('user.leaderboard')
                </div>      
            </div>
        </div>
        <h2 class="my-3">All User</h2>
        @include('user.table')
    </div>
@endsection