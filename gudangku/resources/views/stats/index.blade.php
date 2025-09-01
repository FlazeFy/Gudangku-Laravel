@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  

@section('content')
    <script src="{{ asset('/control_panel_v1.0.js')}}"></script>
    <script>
        const year_sess = <?= session()->get('toogle_select_year') ?>;
        const year = year_sess ?? new Date().getFullYear()
    </script>
    <div class="content">
        @include('others.profile')
        @include('others.notification')
        <h2 class="text-white fw-bold mb-4" style="font-size:<?php if(!$isMobile){ echo "calc(var(--textXJumbo)*1.75)"; } else { echo "var(--textXJumbo)"; } ?>">Stats</h2>
        <div class="d-flex justify-content-start">
            <a class="btn btn-danger mb-3 me-2" href="/"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Back @endif</a>
        </div>
        @include('stats.toogle_total')
        <div class="row">
            @if(session()->get('toogle_view_stats') == 'top chart')
                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 mx-auto">
                    @include('stats.get_total_inventory_by_category')
                </div>
                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 mx-auto">
                    @include('stats.get_total_inventory_by_room')
                </div>
                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 mx-auto">
                    @include('stats.get_total_inventory_by_fav')
                </div>
                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 mx-auto">
                    @include('stats.get_total_inventory_by_merk')
                </div>
            @elseif(session()->get('toogle_view_stats') == 'periodic chart')
                <div class="col-lg-6 col-md-12 mx-auto">
                    @include('stats.get_total_inventory_created_per_month')
                </div>
                <div class="col-lg-6 col-md-12 mx-auto">
                    @include('stats.get_total_report_created_per_month')
                </div>
                <div class="col-lg-6 col-md-12 mx-auto">
                    @include('stats.get_total_report_spending_per_month')
                </div>
                <div class="col-lg-6 col-md-12 mx-auto">
                    @include('stats.get_total_report_used_per_month')
                </div>
            @elseif(session()->get('toogle_view_stats') == 'most expensive')
                @include('stats.get_most_expensive_inventory_per_context')
            @elseif(session()->get('toogle_view_stats') == 'tree distribution map')
                @include('stats.tree_distribution_map')
            @elseif(session()->get('toogle_view_stats') == 'used percentage')
                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 mx-auto">
                    @include('stats.get_favorite_inventory_comparison')
                </div>
            @endif
        </div>
    </div>
@endsection
