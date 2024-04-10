<form action="/inventory/edit/{{$inventory->id}}/editInventory" method="POST">
    @csrf
    <h6 class="fw-bold mt-3" style="font-size:var(--textXLG);">Inventory Detail</h6>
    <div class="row">
        <div class="col-lg-6 py-2">
            <label>Name</label>
            <input type="text" name="inventory_name" value="{{$inventory->inventory_name}}" class="form-control my-2"/>

            <label>Category</label>
            <select class="form-select mt-2" name="inventory_category" aria-label="Default select example">
                @foreach($dct_cat as $dct)
                    <option value="{{$dct['dictionary_name']}}" <?php if($dct['dictionary_name'] == $inventory->inventory_category){ echo 'selected'; } ?>>{{$dct['dictionary_name']}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-6 py-2">
            @include('edit.image_picker')
        </div>
        <div class="col-lg-12 py-2">
            <label>Description</label>
            <textarea name="inventory_desc" class="form-control mt-2">{{$inventory->inventory_desc}}</textarea>
        </div>
        <div class="col-lg-6 py-2">
            <label>Merk</label>
            <input type="text" name="inventory_merk" value="{{$inventory->inventory_merk}}" class="form-control mt-2"/>
        </div>
        <div class="col-lg-6 py-2">
            <label>Price</label>
            <input type="number" name="inventory_price" value="{{$inventory->inventory_price}}" class="form-control mt-2"/>
        </div>
    </div><hr>

    <h6 class="fw-bold mt-3" style="font-size:var(--textXLG);">Standard Capacity</h6>
    <div class="row">
        <div class="col-lg-6 py-2">
            <label>Volume</label>
            <input type="number" name="inventory_vol" value="{{$inventory->inventory_vol}}" class="form-control mt-2"/>
        </div>
        <div class="col-lg-6 py-2">
            <label>Unit</label>
            <select class="form-select mt-2" name="inventory_unit" aria-label="Default select example">
                @foreach($dct_unit as $dct)
                    <option value="{{$dct['dictionary_name']}}" <?php if($dct['dictionary_name'] == $inventory->inventory_unit){ echo 'selected'; } ?>>{{$dct['dictionary_name']}}</option>
                @endforeach
            </select>
        </div>
    </div><hr>

    <h6 class="fw-bold mt-3" style="font-size:var(--textXLG);">Remaining Capacity</h6>
    <div class="row">
        <div class="col-lg-6 py-2">
            <label>Volume</label>
            <input type="number" name="inventory_capacity_vol" value="{{$inventory->inventory_capacity_vol}}" class="form-control mt-2"/>
        </div>
        <div class="col-lg-6 py-2">
            <label>Unit</label>
            <select class="form-select mt-2" name="inventory_capacity_unit" aria-label="Default select example">
                <option value="percentage">Percentage</option>
                @if($inventory->inventory_capacity_vol == null)
                    <option value='-' selected>-</option>
                @endif
                @foreach($dct_unit as $dct)
                    <option value="{{$dct['dictionary_name']}}" <?php if($dct['dictionary_name'] == $inventory->inventory_capacity_unit){ echo 'selected'; } ?>>{{$dct['dictionary_name']}}</option>
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
                    <option value="{{$dct['dictionary_name']}}" <?php if($dct['dictionary_name'] == $inventory->inventory_room){ echo 'selected'; } ?>>{{$dct['dictionary_name']}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-6 py-2">
            <label>Storage</label>
            <input type="text" name="inventory_storage" value="{{$inventory->inventory_storage}}" class="form-control mt-2"/>
        </div>
        <div class="col-lg-6 py-2">
            <label>Rack</label>
            <input type="text" name="inventory_rack" value="{{$inventory->inventory_rack}}" class="form-control mt-2"/>
        </div>
    </div><hr>

    <h6 class="fw-bold mt-3" style="font-size:var(--textXLG);">Reminder</h6>

    @if($inventory->reminder_id)
        <div class="row">
            <div class="col-lg-6 py-2">
                <label>Type</label>
                <select class="form-select mt-2" name="reminder_type" aria-label="Default select example">
                    @foreach($dct_reminder_type as $dct)
                        <option value="{{$dct['dictionary_name']}}" <?php if($dct['dictionary_name'] == $inventory->reminder_type){ echo 'selected'; } ?>>{{$dct['dictionary_name']}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-6 py-2">
                <label>Context</label>
                <select class="form-select mt-2" name="reminder_context" aria-label="Default select example">
                    @foreach($dct_reminder_context as $dct)
                        <option value="{{$dct['dictionary_name']}}" <?php if($dct['dictionary_name'] == $inventory->reminder_context){ echo 'selected'; } ?>>{{$dct['dictionary_name']}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-6 py-2">
                <label>Description</label>
                <textarea name="reminder_desc" class="form-control mt-2">{{$inventory->reminder_desc}}</textarea>
            </div>
        </div>
    @else 
        <div class="container p-3" style="background-color:rgba(59, 131, 246, 0.2);">
            <div class="d-flex justify-content-start">
                <div class="me-3">
                    <h1 style="font-size: 70px;"><i class="fa-regular fa-clock"></i></h1>
                </div>
                <div>
                    <h4>This item doesn't have reminder</h4>
                    <a class="btn btn-primary mt-3"><i class="fa-solid fa-plus"></i> Add New Reminder</a>
                </div>
            </div>
        </div>
    @endif

    <button type="submit" class="btn btn-success mt-3 w-100 border-0" style="background:var(--successBG) !important;"><i class="fa-solid fa-floppy-disk"></i> Submit</button>
</form>