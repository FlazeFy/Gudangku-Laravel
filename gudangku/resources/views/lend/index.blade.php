@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  
@php($role = session()->get('role_key'))

@section('content')
    <!-- JS Collection -->
    <script src="{{ asset('/control_panel_v1.0.js')}}"></script>

    <script>
        let lend_id = '<?= $lend_id ?>'
        const SELECTED_STORAGE_KEY = `selected_lend_items_${lend_id}`

        const save_selected_items = (items) => {
            localStorage.setItem(SELECTED_STORAGE_KEY, JSON.stringify(items))
        }

        const get_selected_items = () => {
            const raw = localStorage.getItem(SELECTED_STORAGE_KEY)
            return raw ? JSON.parse(raw) : []
        }

        const get_cart_button = () => {
            $(document).ready(function () {
                const selected = get_selected_items()
                $('#total-item-selected').text(selected.length)
            })
        }
    </script>
    <div class="content">
        <h1 class="main-page-title">@if($role == 0) <span class="inventory-owner"></span> @endif Inventory</h1>
        <a class="btn btn-danger mb-3 me-2" href="/profile"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Back @endif</a>
        <a class="btn btn-danger mb-3 me-2" href="/"><i class="fa-solid fa-home" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Home Page @endif</a>
        @include('lend.selected_inventory')
        @include('lend.inventory')
    </div>
@endsection