@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  
<script>
    let warehouse = []
</script>

@section('content')
    <script>
        let page = 1
        let search_key = `<?= $search_key ?>`
        let filter_category = `<?= $filter_category ?>`
        let sorting = `<?= $sorting ?>`
    </script>
    <div class="content">
        <h2 class="main-page-title">Report</h2>
        <div class="d-flex justify-content-<?php if(!$isMobile){ echo "start"; } else { echo "end"; } ?>">
            <a class="btn btn-danger btn-main top" href="/"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i>@if(!$isMobile)  Back @endif</a>
            <a class="btn btn-primary mb-3 me-2 btn-main bottom" data-bs-toggle="modal" data-bs-target="#modalAddReport" ><i class="fa-solid fa-plus" style="font-size:var(--textXLG);"></i>@if(!$isMobile)  Add Report @endif</a>
            @include('report.add')  
        </div>
        @include('report.filter')
        @include('report.report')
    </div>
    <script>
        is_process = false
        is_submit = false
        window.addEventListener('beforeunload', function(event) {
            is_process = check_filling_status(['report_title','report_desc'])
            console.log(is_process)
            if(is_process == true && !is_submit){
                event.preventDefault()
                event.returnValue = ''
            }
        });
    </script>
@endsection
