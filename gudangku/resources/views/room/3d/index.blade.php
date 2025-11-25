@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  

<!-- ThreeJS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.rawgit.com/mrdoob/three.js/r128/examples/js/loaders/3DSLoader.js"></script>
<script src="https://cdn.jsdelivr.net/gh/mrdoob/three.js@r128/examples/js/loaders/TDSLoader.js"></script>
<script src="https://cdn.jsdelivr.net/gh/mrdoob/three.js@r128/examples/js/controls/OrbitControls.js"></script>

@section('content')
    <div class="content">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="main-page-title">3D Room</h1>
            <div>
                @include('others.profile')
                @include('others.notification')
            </div>
        </div>
        <hr>
        <div class="mb-3 d-flex flex-wrap gap-2">
            @include('components.back_button', ['route' => '/inventory'])
            @include('room.select_room')
        </div>

        @include('room.3d.room')
    </div>
@endsection
