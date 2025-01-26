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
        <h2 class="text-white fw-bold mb-4" style="font-size:36px;">My Inventory</h2>
        <a class="btn btn-danger mb-3 me-2" href="/inventory"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> Back</a>
        <a class="btn btn-primary mb-3 me-2" href="/inventory/add"><i class="fa-solid fa-plus" style="font-size:var(--textXLG);"></i> Add Inventory</a>
        <a class="btn btn-primary mb-3 me-2" href="/stats"><i class="fa-solid fa-chart-pie" style="font-size:var(--textXLG);"></i> Stats</a>
        @include('home.catalog.list')
    </div>
@endsection