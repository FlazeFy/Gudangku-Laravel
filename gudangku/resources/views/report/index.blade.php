@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  

@section('content')
    <div class="content" style="max-width:1440px;">
        <h2 class="text-white fw-bold mb-4" style="font-size:<?php if(!$isMobile){ echo "calc(var(--textXJumbo)*1.75)"; } else { echo "var(--textXJumbo)"; } ?>">Report</h2>
        <div class="d-flex justify-content-start">
            <a class="btn btn-danger mb-3 me-2" href="/"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Back @endif</a>
            <a class="btn btn-primary mb-3 me-2" data-bs-toggle="modal" data-bs-target="#modalAddReport" ><i class="fa-solid fa-plus" style="font-size:var(--textXLG);"></i> Add Report</a>
            @include('report.add')  
        </div>
        @include('report.report')
    </div>
@endsection
