@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  

@section('content')
    <div class="content">
        @include('others.profile')
        @include('others.notification')
        <h2 class="main-page-title">Help</h2>
        <div class="d-flex justify-content-<?php if(!$isMobile){ echo "start"; } else { echo "end"; } ?>">
            <a class="btn btn-danger btn-main top" href="/"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Back @endif</a>
        </div>
        @include('help.list')
    </div>
    <div id="nav_scroll-holder" style="position:fixed; right:20px; bottom:20px; z-index:1000; width: 11vw;"></div>
@endsection
