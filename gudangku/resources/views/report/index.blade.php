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
    @if($role == 1)
        <script src="{{ asset('/usecases/report_v1.0.js')}}"></script>
    @endif
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
                @include('others.profile')
                @include('others.notification')
            </div>
        </div>
        <hr>
        <div class="mb-3">
            @include('components.back_button', ['route' => '/'])
            @if($role == 0)
                <a class="btn btn-primary" id="add_report-btn" data-bs-toggle="modal" data-bs-target="#modalAddReport" >
                    <i class="fa-solid fa-plus" style="font-size:var(--textXLG);"></i> Report
                </a>
                @include('report.add')  
            @endif
        </div>

        @include('report.filter')
        @include('report.report')
    </div>
@endsection
