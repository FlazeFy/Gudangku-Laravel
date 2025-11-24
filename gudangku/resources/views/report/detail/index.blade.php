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
        <h1 class="main-page-title">Report Detail</h1>
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex justify-content-<?php if(!$isMobile){ echo "start"; } else { echo "end"; } ?>">
                <a class="btn btn-danger btn-main top" href="/report"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Back @endif</a>
                <div id="btn-doc-preview-holder"></div>
                @include('report.detail.toogle_edit')  
                @include('report.detail.delete')
                @include('report.detail.add')    
            </div>
            @if(!$isMobile)
                <div>
                    <p class='date-text mb-0'>Created At : <span id='created_at'></span></p>
                    <p class='date-text'>Last Updated : <span id='updated_at'></span></p>
                </div>
            @endif
        </div>
        @if($isMobile)
            <div>
                <p class='date-text mb-0'>Created At : <span id='created_at'></span></p>
                <p class='date-text'>Last Updated : <span id='updated_at'></span></p>
            </div>
        @endif
        @include('report.detail.info')  
    </div>
@endsection
