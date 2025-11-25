@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  
@php($role = session()->get('role_key'))

@section('content')
    <script src="{{ asset('/usecases/error_v1.0.js')}}"></script>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="main-page-title">Error History</h1>
            <div>
                @include('others.profile')
                @include('others.notification')
            </div>
        </div>
        <hr>
        <div class="mb-3 d-flex flex-wrap gap-2">
            @include('components.back_button', ['route' => '/'])
            <form class="d-inline" action="/error/save_as_csv" method="POST">
                @csrf
                <button class="btn btn-primary" type="submit" id="save_as_csv_btn"><i class="fa-solid fa-print" style="font-size:var(--textXLG);"></i> Print</button>
            </form>
        </div>

        @include('error.table')
    </div>
@endsection
