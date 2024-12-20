@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  
@php($role = session()->get('role_key'))

@section('content')
    <script>
        let token = '<?= session()->get("token_key"); ?>'
    </script>
    <script src="{{ asset('/usecases/destroy_history_by_id_v1.0.js')}}"></script>

    <div class="content">
        @include('others.profile')
        @include('others.notification')
        <h2 class="main-page-title">History</h2>
        <div class="d-flex justify-content-<?php if(!$isMobile){ echo "start"; } else { echo "end"; } ?>">
            <a class="btn btn-danger btn-main top" href="/"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Back @endif</a>
            <form class="d-inline" action="/history/saveAsCsv" method="POST">
                @csrf
                <button class="btn btn-primary mb-3 me-2" type="submit" id="save_as_csv_btn"><i class="fa-solid fa-print" style="font-size:var(--textXLG);"></i> Save as CSV</button>
            </form>
        </div>
        @if($role == 0)
            @include('history.list')
        @else   
            @include('history.table')
        @endif
    </div>
@endsection
