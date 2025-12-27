<form action="/report/detail/{{$id}}/toogle_edit" method="POST" class="d-inline">
    @csrf
    @php($selected = session()->get('toogle_edit_report'))
    <input hidden value="<?php 
        if($selected == 'true'){
            echo 'false';
        } elseif($selected == 'false'){
            echo 'true';
        }
    ?>" name="toogle_edit"/>
    @if($selected == 'true')
        <button class="btn btn-danger" type="submit" id="toogle_edit"><i class="fa-solid fa-xmark"></i> Close Edit</button>
    @elseif($selected == 'false')
        <button class="btn btn-primary" type="submit" id="toogle_edit"><i class="fa-solid fa-pen-to-square"></i> Open Edit</button>
    @endif
</form>