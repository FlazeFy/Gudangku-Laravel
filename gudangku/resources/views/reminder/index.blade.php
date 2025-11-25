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
            <h1 class="main-page-title">Reminder Mark</h1>
            <div>
                @include('others.profile')
                @include('others.notification')
            </div>
        </div>
        <hr>
        <div class="mb-3">
            @include('components.back_button', ['route' => '/'])
            <form class="d-inline" action="/reminder/save_as_csv" method="POST">
                @csrf
                <button class="btn btn-primary" type="submit" id="save_as_csv_btn"><i class="fa-solid fa-print" style="font-size:var(--textXLG);"></i> Print</button>
            </form>
        </div>

        @include('reminder.table')
    </div>
@endsection
