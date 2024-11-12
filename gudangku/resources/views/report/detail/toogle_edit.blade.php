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
        <button class="btn btn-danger btn-main bottom" type="submit" id="toogle_edit"><i class="fa-solid fa-xmark" style="font-size:var(--textXLG);"></i>@if(!$isMobile)  Close Edit @endif</button>
    @elseif($selected == 'false')
        <button class="btn btn-primary btn-main bottom" type="submit" id="toogle_edit"><i class="fa-solid fa-pen-to-square" style="font-size:var(--textXLG);"></i>@if(!$isMobile)  Open Edit @endif</button>
    @endif
</form>