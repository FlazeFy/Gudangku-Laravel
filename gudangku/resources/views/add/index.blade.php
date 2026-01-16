@extends('components.layout')

@section('content')
    <script>
        formValidation('Inventory')
    </script>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="main-page-title">Add Inventory</h1>
            <div>
                @include('components.profile')
                @include('components.notification')
            </div>
        </div>
        <hr>  
        <div class="mb-3 d-flex flex-wrap gap-2">      
            @include('components.back_button', ['route' => '/inventory'])
        </div>       
        @include('add.form')
    </div>

    <script>
        is_process = false
        is_submit = false
        window.addEventListener('beforeunload', function(event) {
            is_process = checkFillingStatus([
                'inventory_name','inventory_desc','inventory_color','inventory_merk','inventory_price','inventory_vol','inventory_capacity_vol','inventory_rack','inventory_storage'
            ])
            if(is_process == true && !is_submit){
                event.preventDefault()
                event.returnValue = ''
            }
        });
    </script>
@endsection