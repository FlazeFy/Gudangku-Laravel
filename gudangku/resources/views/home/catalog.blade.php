<h2 class="text-white fw-bold mb-4" style="font-size:var(--textXJumbo);">By Room</h2>
<div class="row"> 
@foreach($room as $r)
    <div class="col-lg-4 col-md-6 col-sm-12">
        <button class="btn-feature mb-4" onclick="location.href='/inventory/by/room/{{$r->dictionary_name}}';">
            <i class="fa-solid fa-house" style="font-size:90px;"></i>
            <h2 class="mt-3 mb-2" style="font-size:var(--textJumbo);">{{$r->dictionary_name}}</h2>
            <span style="background: var(--infoBG);" class="py-1 px-2 rounded">{{$r->total}} Item</span>
        </button>
    </div>
@endforeach
</div>

<h2 class="text-white fw-bold mb-4 mt-3" style="font-size:var(--textXJumbo);">By Category</h2>
<div class="row"> 
@foreach($category as $c)
    <div class="col-lg-4 col-md-6 col-sm-12">
        <button class="btn-feature mb-4" onclick="location.href='/inventory/by/category/{{$c->dictionary_name}}';">
            <i class="fa-solid fa-toolbox" style="font-size:90px;"></i>
            <h2 class="mt-3 mb-2" style="font-size:var(--textJumbo);">{{$c->dictionary_name}}</h2>
            <span style="background: var(--infoBG);" class="py-1 px-2 rounded">{{$c->total}} Item</span>
        </button>
    </div>
@endforeach
</div>

<h2 class="text-white fw-bold mb-4 mt-3" style="font-size:var(--textXJumbo);">By Storage</h2>
<div class="row"> 
@foreach($storage as $s)
    <div class="col-lg-4 col-md-6 col-sm-12">
        <button class="btn-feature mb-4" onclick="location.href='/inventory/by/storage/{{$s->inventory_storage}}';">
            <i class="fa-solid fa-box-archive" style="font-size:90px;"></i>
            <h2 class="mt-3 mb-2" style="font-size:var(--textJumbo);">{{$s->inventory_storage}}</h2>
            <span style="background: var(--infoBG);" class="py-1 px-2 rounded">{{$s->total}} Item</span>
        </button>
    </div>
@endforeach
</div>