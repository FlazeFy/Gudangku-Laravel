@extends('components.layout')

@section('content')
    <div class="content" style="width:1280px;">
        <h2 class="text-white fw-bold mb-4" style="font-size:36px;">Edit Inventory</h2>
        <div class='d-flex justify-content-between'>
            <div>
                <a class="btn btn-danger mb-3 me-2" href="/inventory"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> Back</a>
                <a class="btn btn-primary mb-3 me-2" data-bs-toggle="modal" data-bs-target="#modalAddReport" ><i class="fa-solid fa-plus" style="font-size:var(--textXLG);"></i> Add Report</a>
                <a class="btn btn-primary mb-3 me-2" data-bs-toggle="modal" data-bs-target="#modalAddReminder" ><i class="fa-solid fa-plus" style="font-size:var(--textXLG);"></i> Add Reminder</a>
            </div>
            <div class='text-end'>
                <h6 class='date-text'>Created At : <span id='created_at'></span></h6>
                <h6 class='date-text'>Last Updated : <span id='updated_at'></span></h6>
            </div>
        </div>
        @include('edit.add_report')
        @include('edit.add_reminder')
        @include('edit.form')
        @include('edit.report')
    </div>
    <script>
        is_process = false
        is_submit = false
        window.addEventListener('beforeunload', function(event) {
            is_process = check_filling_status(['report_title','report_desc','item_desc','reminder_desc'])
            console.log(is_process)
            if(is_process == true && !is_submit){
                event.preventDefault()
                event.returnValue = ''
            }
        });
    </script>
@endsection
