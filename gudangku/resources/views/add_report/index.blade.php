@extends('components.layout')

@section('content')
    <div class="content">
        <div class="d-flex justify-content-between align-items-center">
            <h1>Add Report</h1>
            <div>
                @include('components.profile')
                @include('components.notification')
            </div>
        </div>
        <hr>  
        <div class="mb-3 d-flex flex-wrap gap-2">      
            @include('components.back_button', ['route' => '/report'])
        </div>       
        @include('add_report.form')
    </div>
@endsection