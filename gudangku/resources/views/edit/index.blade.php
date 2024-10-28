@extends('components.layout')

@section('content')
    <div class="content" style="width:1280px;">
        <h2 class="text-white fw-bold mb-4" style="font-size:36px;">Edit Inventory</h2>
        <a class="btn btn-danger mb-3 me-2" href="/inventory"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> Back</a>
        <a class="btn btn-primary mb-3 me-2" data-bs-toggle="modal" data-bs-target="#modalAddReport" ><i class="fa-solid fa-plus" style="font-size:var(--textXLG);"></i> Add Report</a>
        <a class="btn btn-primary mb-3 me-2" data-bs-toggle="modal" data-bs-target="#modalAddReminder" ><i class="fa-solid fa-plus" style="font-size:var(--textXLG);"></i> Add Reminder</a>
        @include('edit.add_report')
        @include('edit.add_reminder')
        @include('edit.form')
        @include('edit.report')
    </div>
@endsection
