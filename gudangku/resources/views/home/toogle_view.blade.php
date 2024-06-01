<form action="/inventory/toogleView" method="POST" class="d-inline">
    @csrf
    @php($selected = session()->get('toogle_view_inventory'))
    <input hidden value="<?php 
        if($selected == 'table'){
            echo 'catalog';
        } elseif($selected == 'catalog'){
            echo 'table';
        }
    ?>" name="toogle_view"/>
    <button class="btn btn-primary mb-3 me-2" type="submit" id="toogle_view">
        @if($selected == 'table')
            <i class="fa-solid fa-table" style="font-size:var(--textXLG);"></i> Table
        @elseif($selected == 'catalog')
            <i class="fa-solid fa-box-archive" style="font-size:var(--textXLG);"></i> Catalog
        @endif
    </button>
</form>