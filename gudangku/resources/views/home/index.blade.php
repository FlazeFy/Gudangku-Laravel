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

    @php($selected = session()->get('toogle_view_inventory'))
    @if($selected == 'table')
        <script>
            let search_key = `<?= $search_key ?>`
            let filter_category = `<?= $filter_category ?>`
            let sorting = `<?= $sorting ?>`
            let page = 1
        </script>
    @endif

    <div class="content">
        <div class="d-flex justify-content-between align-items-center">
            <h1>My Inventory</h1>
            <div>
                @include('components.profile')
                @include('components.notification')
            </div>
        </div>
        <hr>
        @include('home.home_toolbar')
        @if($selected == 'table')
            @include('home.filter')
        @endif
        @include('home.lend_inventory')
        @if($selected == 'table')
            @include('home.table')
        @elseif($selected == 'catalog')
            @include('home.catalog')
        @endif
    </div>
@endsection