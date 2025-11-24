<h2 class="mb-4">By Room</h2>
<div class="row"> 
@foreach($room as $r)
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12">
        <button class="btn-feature mb-4" onclick="location.href='/inventory/by/room/{{$r->dictionary_name}}';">
            <i class="fa-solid fa-house" style="font-size:90px;"></i>
            <h2 class="mt-3 mb-2" style="font-size:var(--textJumbo);">{{$r->dictionary_name}}</h2>
            <span style="background: var(--infoBG);" class="py-1 px-2 rounded props-box">{{$r->total}} Item</span>
        </button>
    </div>
@endforeach
</div>

<h2 class="mb-4 mt-3">By Category</h2>
<div class="row"> 
@foreach($category as $c)
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12">
        <button class="btn-feature mb-4" onclick="location.href='/inventory/by/category/{{$c->dictionary_name}}';">
            <i class="fa-solid fa-toolbox" style="font-size:90px;"></i>
            <h2 class="mt-3 mb-2" style="font-size:var(--textJumbo);">{{$c->dictionary_name}}</h2>
            <span style="background: var(--infoBG);" class="py-1 px-2 rounded props-box">{{$c->total}} Item</span>
        </button>
    </div>
@endforeach
</div>

<h2 class="mb-4 mt-3">By Storage</h2>
<div class="row"> 
@foreach($storage as $s)
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12">
        <button class="btn-feature mb-4" onclick="location.href='/inventory/by/storage/{{$s->inventory_storage}}';">
            <i class="fa-solid fa-box-archive" style="font-size:90px;"></i>
            <h2 class="mt-3 mb-2" style="font-size:var(--textJumbo);">{{$s->inventory_storage}}</h2>
            <span style="background: var(--infoBG);" class="py-1 px-2 rounded props-box">{{$s->total}} Item</span>
        </button>
    </div>
@endforeach
</div>