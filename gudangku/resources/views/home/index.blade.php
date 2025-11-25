@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  
@php($role = session()->get('role_key'))

@section('content')
    <!-- JS Collection -->
    <script src="{{ asset('/usecases/inventory_v1.0.js')}}"></script>
    <script src="{{ asset('/usecases/reminder_v1.0.js')}}"></script>
    <script src="{{ asset('/control_panel_v1.0.js')}}"></script>

    <script>
        let search_key = `<?= $search_key ?>`
        let filter_category = `<?= $filter_category ?>`
        let sorting = `<?= $sorting ?>`
        let page = 1
    </script>
    <div class="content">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="main-page-title">My Inventory</h1>
            <div>
                @include('others.profile')
                @include('others.notification')
            </div>
        </div>
        <hr>
        <div class="mb-3 d-flex flex-wrap gap-2">
            @include('components.back_button', ['route' => '/'])
            @include('home.toogle_view')
            @if($role == 0)
                <a class="btn btn-primary" href="/inventory/add" id="add_inventory-button"><i class="fa-solid fa-plus" style="font-size:var(--textXLG);"></i> Inventory</a>
            @endif
            <a class="btn btn-primary" href="/stats"><i class="fa-solid fa-chart-pie" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Stats @endif</a>
            @if($role == 0)
                <a class="btn btn-primary" href="/calendar"><i class="fa-solid fa-calendar" style="font-size:var(--textXLG);"></i><span class="d-none d-md-inline"> Calendar</span></a>
                <a class="btn btn-primary" href="/room/2d"><i class="fa-solid fa-layer-group" style="font-size:var(--textXLG);"></i> 2D Room</a>
                <a class="btn btn-primary" href="/room/3d"><i class="fa-solid fa-cube" style="font-size:var(--textXLG);"></i> 3D Room</a>
            @endif
            <span id="toolbar-button-section"></span>
        </div>

        @php($selected = session()->get('toogle_view_inventory'))
        @include('home.filter')
        @include('home.lend_inventory')
        @if($selected == 'table')
            @include('home.table')
        @elseif($selected == 'catalog')
            @include('home.catalog')
        @endif
    </div>
@endsection