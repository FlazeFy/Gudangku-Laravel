@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  

@section('content')
    <div class="content" style="width:1280px;">
        <h2 class="text-white fw-bold mb-4" style="font-size:<?php if(!$isMobile){ echo "calc(var(--textXJumbo)*1.75)"; } else { echo "var(--textXJumbo)"; } ?>">Stats</h2>
        <div class="d-flex justify-content-start">
            <a class="btn btn-danger mb-3 me-2" href="/"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Back @endif</a>
            @include('stats.toogle_total')
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-12">
                @include('stats.get_total_inventory_by_category')
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                @include('stats.get_total_inventory_by_room')
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                @include('stats.get_total_inventory_by_fav')
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12">
                @include('stats.get_total_report_created_per_month')
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12">
                @include('stats.get_total_report_spending_per_month')
            </div>
        </div>
    </div>
@endsection
