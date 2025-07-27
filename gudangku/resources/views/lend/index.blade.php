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
        let token = '<?= session()->get("token_key"); ?>'
    </script>
    <div class="content">
        <h2 class="text-white fw-bold mb-4" style="font-size:<?php if(!$isMobile){ echo "calc(var(--textXJumbo)*1.75)"; } else { echo "var(--textXJumbo)"; } ?>">@if($role == 0) <span>...</span> @endif Inventory</h2>
        <a class="btn btn-danger mb-3 me-2" href="/profile"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Back @endif</a>
        <a class="btn btn-danger mb-3 me-2" href="/"><i class="fa-solid fa-home" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Home Page @endif</a>
    </div>
@endsection