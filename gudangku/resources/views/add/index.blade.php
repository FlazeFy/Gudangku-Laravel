@extends('components.layout')

@section('content')
    <div class="content">
        @include('others.profile')
        @include('others.notification')
        <h2 class="text-white fw-bold mb-4" style="font-size:36px;">Add Inventory</h2>
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
            console.log(is_process)
            if(is_process == true && !is_submit){
                event.preventDefault()
                event.returnValue = ''
            }
        });
    </script>
@endsection