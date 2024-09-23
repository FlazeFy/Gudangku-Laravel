<form action="/report/detail/{{$id}}/toogleEdit" method="POST" class="d-inline">
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
        <button class="btn btn-danger mb-3 me-2" type="submit" id="toogle_edit"><i class="fa-solid fa-xmark" style="font-size:var(--textXLG);"></i> Close Edit</button>
    @elseif($selected == 'false')
        <button class="btn btn-primary mb-3 me-2" type="submit" id="toogle_edit"><i class="fa-solid fa-pen-to-square" style="font-size:var(--textXLG);"></i> Open Edit</button>
    @endif
</form>