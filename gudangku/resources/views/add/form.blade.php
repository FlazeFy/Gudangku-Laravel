<form action="/inventory/add/addInventory" method="POST">
    @csrf
    <h6 class="fw-bold mt-3" style="font-size:var(--textXLG);">Inventory Detail</h6>
    <div class="row">
        <div class="col-lg-6 py-2">
            <label>Name</label>
            <input type="text" name="inventory_name" class="form-control my-2"/>

            <label>Category</label>
            <select class="form-select mt-2" name="inventory_category" aria-label="Default select example">
                @foreach($dct_cat as $dct)
                    <option value="{{$dct['dictionary_name']}}">{{$dct['dictionary_name']}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-6 py-2">
            @include('add.image_picker')
        </div>
        <div class="col-lg-12 py-2">
            <label>Description</label>
            <textarea name="inventory_desc" class="form-control mt-2"></textarea>
        </div>
        <div class="col-lg-6 py-2">
            <label>Merk</label>
            <input type="text" name="inventory_merk" class="form-control mt-2"/>
        </div>
        <div class="col-lg-6 py-2">
            <label>Price</label>
            <input type="number" name="inventory_price" class="form-control mt-2"/>
        </div>
    </div><hr>

    <h6 class="fw-bold mt-3" style="font-size:var(--textXLG);">Standard Capacity</h6>
    <div class="row">
        <div class="col-lg-6 py-2">
            <label>Volume</label>
            <input type="number" name="inventory_vol" class="form-control mt-2"/>
        </div>
        <div class="col-lg-6 py-2">
            <label>Unit</label>
            <select class="form-select mt-2" name="inventory_unit" aria-label="Default select example">
                @foreach($dct_unit as $dct)
                    <option value="{{$dct['dictionary_name']}}">{{$dct['dictionary_name']}}</option>
                @endforeach
            </select>
        </div>
    </div><hr>

    <h6 class="fw-bold mt-3" style="font-size:var(--textXLG);">Remaining Capacity</h6>
    <div class="row">
        <div class="col-lg-6 py-2">
            <label>Volume</label>
            <input type="number" name="inventory_capacity_vol" class="form-control mt-2"/>
        </div>
        <div class="col-lg-6 py-2">
            <label>Unit</label>
            <select class="form-select mt-2" name="inventory_capacity_unit" aria-label="Default select example">
                <option value="percentage">Percentage</option>
                @foreach($dct_unit as $dct)
                    <option value="{{$dct['dictionary_name']}}">{{$dct['dictionary_name']}}</option>
                @endforeach
            </select>
        </div>
    </div><hr>

    <h6 class="fw-bold mt-3" style="font-size:var(--textXLG);">Placement</h6>
    <div class="row">
        <div class="col-lg-6 py-2">
            <label>Room</label>
            <select class="form-select mt-2" name="inventory_room" aria-label="Default select example">
                @foreach($dct_room as $dct)
                    <option value="{{$dct['dictionary_name']}}">{{$dct['dictionary_name']}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-6 py-2">
            <label>Storage</label>
            <input type="text" name="inventory_storage" class="form-control mt-2"/>
        </div>
        <div class="col-lg-6 py-2">
            <label>Rack</label>
            <input type="text" name="inventory_rack" class="form-control mt-2"/>
        </div>
    </div>

    <button type="submit" class="btn btn-success mt-3 w-100 border-0" style="background:var(--successBG) !important;"><i class="fa-solid fa-floppy-disk"></i> Submit</button>
</form>