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
    <div class="content" style="max-width:1440px;">
        <h2 class="text-white fw-bold mb-4" style="font-size:<?php if(!$isMobile){ echo "calc(var(--textXJumbo)*1.75)"; } else { echo "var(--textXJumbo)"; } ?>">3D Room</h2>
        <div class="d-flex justify-content-start">
            <a class="btn btn-danger mb-3 me-2" href="/inventory"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Back @endif</a>
        </div>
        @include('room.room')
    </div>
@endsection
