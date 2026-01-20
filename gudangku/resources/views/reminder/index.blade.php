@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  
@php($role = session()->get('role_key'))

@section('content')
    <div class="content">
        <div class="d-flex justify-content-between align-items-center">
            <h1>Reminder Mark</h1>
            <div>
                @include('components.profile')
                @include('components.notification')
            </div>
        </div>
        <hr>
        <div class="mb-3 d-flex flex-wrap gap-2">
            @include('components.back_button', ['route' => '/'])
            <form class="d-inline" action="/reminder/save_as_csv" method="POST">
                @csrf
                <button class="btn btn-primary" type="submit" id="save_as_csv_btn"><i class="fa-solid fa-print"></i> Print</button>
            </form>
        </div>

        @include('reminder.table')
    </div>
@endsection
