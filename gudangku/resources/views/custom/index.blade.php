@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  

@section('content')
    <!-- Richtext -->
    <link rel="stylesheet" href="{{ asset('/richtexteditor/rte_theme_default.css')}}" />
    <script type="text/javascript" src="{{ asset('/richtexteditor/rte.js')}}"></script>
    <script type="text/javascript" src="{{ asset('/richtexteditor/rte-upload.js')}}"></script>
    <script type="text/javascript" src="{{ asset('/richtexteditor/plugins/all_plugins.js')}}"></script>
    <div class="content" style="width:1280px;">
        <h2 class="text-white fw-bold mb-4" style="font-size:<?php if(!$isMobile){ echo "calc(var(--textXJumbo)*1.75)"; } else { echo "var(--textXJumbo)"; } ?>">Custom Print : {{ucfirst($type)}}</h2>
        <div class="d-flex justify-content-start">
            <a class="btn btn-danger mb-3 me-2" href="/"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Back @endif</a>
        </div>
        @include('custom.worksheet')
    </div>
@endsection
