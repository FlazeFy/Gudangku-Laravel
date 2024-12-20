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
        <h2 class="main-page-title">Report Detail</h2>
        <div class="d-flex justify-content-between">
            <div class="d-flex justify-content-<?php if(!$isMobile){ echo "start"; } else { echo "end"; } ?>">
                <a class="btn btn-danger btn-main top" href="/report"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Back @endif</a>
                <div id="btn-doc-preview-holder"></div>
                @include('report.detail.toogle_edit')  
                @include('report.detail.delete')  
            </div>
            @if(!$isMobile)
                <div>
                    <h6 class='date-text'>Created At : <span id='created_at'></span></h6>
                    <h6 class='date-text'>Last Updated : <span id='updated_at'></span></h6>
                </div>
            @endif
        </div>
        @include('report.detail.info')  
    </div>
@endsection
