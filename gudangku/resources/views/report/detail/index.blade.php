@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  

@section('content')
    <div class="content" style="width:1280px;">
        <h2 class="text-white fw-bold mb-4" style="font-size:<?php if(!$isMobile){ echo "calc(var(--textXJumbo)*1.75)"; } else { echo "var(--textXJumbo)"; } ?>">Report Detail</h2>
        <div class="d-flex justify-content-between">
            <div class="d-flex justify-content-start">
                <a class="btn btn-danger mb-3 me-2" href="/report"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Back @endif</a>
                <div id="btn-doc-preview-holder"></div>
                @include('report.detail.toogle_edit')  
                @include('report.detail.delete')  
            </div>
            <div>
                <h6 class='date-text'>Created At : <span id='created_at'></span></h6>
                <h6 class='date-text'>Last Updated : <span id='updated_at'></span></h6>
            </div>
        </div>
        @include('report.detail.info')  
    </div>
@endsection
