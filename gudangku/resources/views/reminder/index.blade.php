@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  
@php($role = session()->get('role_key'))

@section('content')
    <div class="content">
        @include('others.profile')
        @include('others.notification')
        <h1 class="main-page-title">Reminder Mark</h1>
        <div class="d-flex justify-content-<?php if(!$isMobile){ echo "start"; } else { echo "end"; } ?>">
            <a class="btn btn-danger btn-main top" href="/"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Back @endif</a>
            <form class="d-inline" action="/reminder/save_as_csv" method="POST">
                @csrf
                <button class="btn btn-primary mb-3 me-2" type="submit" id="save_as_csv_btn"><i class="fa-solid fa-print" style="font-size:var(--textXLG);"></i> Save as CSV</button>
            </form>
        </div>
        @include('reminder.table')
    </div>
@endsection
