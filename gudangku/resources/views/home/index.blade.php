@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  
@php($role = session()->get('role_key'))

@section('content')
    <!-- JS Collection -->
    @if($role == "user")
        <script src="{{ asset('/usecases/fav_toogle_inventory_by_id_v1.0.0.js')}}"></script>
    @endif
    <script src="{{ asset('/usecases/delete_inventory_by_id_v1.0.0.js')}}"></script>
    <script src="{{ asset('/usecases/recover_inventory_by_id_v1.0.0.js')}}"></script>
    <script src="{{ asset('/control_panel_v1.0.js')}}"></script>

    <script>
        let search_key = `<?= $search_key ?>`
        let filter_category = `<?= $filter_category ?>`
        let sorting = `<?= $sorting ?>`
        let page = 1
        let token = '<?= session()->get("token_key"); ?>'
    </script>
    <div class="content">
        @include('others.profile')
        <h2 class="text-white fw-bold mb-4" style="font-size:<?php if(!$isMobile){ echo "calc(var(--textXJumbo)*1.75)"; } else { echo "var(--textXJumbo)"; } ?>">@if($role == "user") My @endif Inventory</h2>
        <a class="btn btn-danger mb-3 me-2" href="/"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Back @endif</a>
        @include('home.toogle_view')
        @if($role == "user")
            <a class="btn btn-primary btn-main bottom" href="/inventory/add"><i class="fa-solid fa-plus" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Add Inventory @endif</a>
        @endif
        <a class="btn btn-primary btn-main bottom" style="bottom:calc(1.9*var(--spaceJumbo));" href="/stats"><i class="fa-solid fa-chart-pie" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Stats @endif</a>
        @if($role == "user")
            <a class="btn btn-primary btn-main bottom" style="bottom:calc(3.6*var(--spaceJumbo));" href="/calendar"><i class="fa-solid fa-calendar" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Calendar @endif</a>
            <a class="btn btn-primary mb-3 me-2" href="/room/2d"><i class="fa-solid fa-layer-group" style="font-size:var(--textXLG);"></i> @if(!$isMobile) 2D Room @endif</a>
            <a class="btn btn-primary mb-3 me-2" href="/room/3d"><i class="fa-solid fa-cube" style="font-size:var(--textXLG);"></i> @if(!$isMobile) 3D Room @endif</a>
        @endif
        <form class="d-inline" action="/inventory/saveAsCsv" method="POST">
            @csrf
            <button class="btn btn-primary mb-3 me-2" id="save_as_csv_btn" type="submit"><i class="fa-solid fa-print" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Save as CSV @endif</button>
        </form>
        @php($selected = session()->get('toogle_view_inventory'))
        @include('home.filter')
        @if($selected == 'table')
            @include('home.table')
        @elseif($selected == 'catalog')
            @include('home.catalog')
        @endif
    </div>
@endsection