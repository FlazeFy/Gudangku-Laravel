@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  
@php($role = session()->get('role_key'))

@section('content')
    <script>
        const view = `<?= $view ?>`
        const catalog = `<?= $context ?>`
    </script>
    
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
        @include('home.catalog.list')
        @include('home.catalog.others')
    </div>
@endsection