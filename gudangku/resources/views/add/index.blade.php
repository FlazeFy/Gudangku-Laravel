@extends('components.layout')

@section('content')
    <script>
        formValidation('Inventory')
    </script>

    <div class="content">
        @include('others.profile')
        @include('others.notification')
        <h1 class="main-page-title">Add Inventory</h1>
        <a class="btn btn-danger mb-3" href="/inventory"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> Back</a>
        @include('add.form')
    </div>

    <script>
        is_process = false
        is_submit = false
        window.addEventListener('beforeunload', function(event) {
            is_process = check_filling_status([
                'inventory_name','inventory_desc','inventory_color','inventory_merk','inventory_price','inventory_vol','inventory_capacity_vol','inventory_rack','inventory_storage'
            ])
            if(is_process == true && !is_submit){
                event.preventDefault()
                event.returnValue = ''
            }
        });
    </script>
@endsection