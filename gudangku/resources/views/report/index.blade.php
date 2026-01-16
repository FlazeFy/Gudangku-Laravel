@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  
@php($role = session()->get('role_key'))

<script>
    let warehouse = []
</script>

@section('content')
    <script src="{{ asset('/control_panel_v1.0.js')}}"></script>
    <script>
        let page = 1
        let search_key = `<?= $search_key ?>`
        let filter_category = `<?= $filter_category ?>`
        let sorting = `<?= $sorting ?>`
    </script>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="main-page-title">Report</h1>
            <div>
                @include('components.profile')
                @include('components.notification')
            </div>
        </div>
        <hr>
        <div class="mb-3 d-flex flex-wrap gap-2">
            @include('components.back_button', ['route' => '/'])
            @if($role == 0)
                <a class="btn btn-primary" id="add_report-btn" href="/report/add"><i class="fa-solid fa-plus"></i> Report</a>
            @endif
        </div>

        @include('report.filter')
        @include('report.report')
    </div>
@endsection
