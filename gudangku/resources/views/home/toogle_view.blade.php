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
    <button class="btn btn-primary" type="submit" id="toogle_view">
        @if($selected == 'table')
            <i class="fa-solid fa-table"></i> Table
        @elseif($selected == 'catalog')
            <i class="fa-solid fa-box-archive"></i> Catalog
        @endif
    </button>
</form>