<form id="add_inventory">
    <h6 class="fw-bold mt-3" style="font-size:var(--textXLG);">Inventory Detail</h6>
    <div class="row">
        <div class="col-lg-6 py-2">
            <input type="text" name="inventory_name" id='inventory_name' class="form-control form-validated my-2" maxlength="75"/>

            <label>Category</label>
            <select class="form-select my-2" name="inventory_category" aria-label="Default select example">
                @foreach($dct_cat as $dct)
                    <option value="{{$dct['dictionary_name']}}">{{$dct['dictionary_name']}}</option>
                @endforeach
            </select>

            <label>Color</label>
            <input type="text" name="inventory_color" id="inventory_color" class="form-control my-2" readonly/>
        </div>
        <div class="col-lg-6 py-2">
            @include('add.image_picker')
        </div>
        <div class="col-lg-12 py-2">
            <textarea name="inventory_desc" id="inventory_desc" class="form-control mt-2 form-validated" maxlength="255"></textarea>
        </div>
        <div class="col-lg-6 py-2">
            <input type="text" name="inventory_merk" id="inventory_merk" class="form-control mt-2 form-validated" maxlength="75"/>
        </div>
        <div class="col-lg-6 py-2">
            <input type="number" name="inventory_price" id="inventory_price" class="form-control mt-2 form-validated" min="0"/>
        </div>
    </div>
    <div class="form-check mt-2">
        <input class="form-check-input" type="checkbox" name="is_favorite" id="is_favorite">
        <label class="form-check-label" for="flexCheckDefault">Set as Favorited Item</label>
    </div>
    <hr>

    <h6 class="fw-bold mt-3" style="font-size:var(--textXLG);">Standard Capacity</h6>
    <div class="row">
        <div class="col-lg-6 py-2">
            <input type="number" name="inventory_vol" id="inventory_vol" class="form-control mt-2 form-validated" max="9999" min="0"/>
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
            <input type="number" name="inventory_capacity_vol" id='inventory_capacity_vol' class="form-control mt-2 form-validated"/>
        </div>
        <div class="col-lg-6 py-2">
            <label>Unit</label>
            <select class="form-select mt-2" name="inventory_capacity_unit" aria-label="Default select example">
                <option value='-' selected>-</option>
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
            <input type="text" name="inventory_storage" id="inventory_storage" class="form-control mt-2 form-validated" maxlength="36"/>
        </div>
        <div class="col-lg-6 py-2">
            <input type="text" name="inventory_rack" id="inventory_rack" class="form-control mt-2 form-validated" maxlength="36"/>
        </div>
    </div>

    <a class="btn btn-success mt-3 w-100 border-0" onclick="submit_add()" style="background:var(--successBG) !important;"><i class="fa-solid fa-floppy-disk"></i> Submit</a>
</form>

<script>
    const submit_add = () => {
        Swal.showLoading()
        const form = $('#add_inventory')[0]
        const formData = new FormData(form)
        $.ajax({
            url: '/api/v1/inventory',
            type: 'POST',
            data: formData,
            processData: false, 
            contentType: false,
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
            },
            success: function(response) {
                Swal.hideLoading()
                Swal.fire({
                    title: "Success!",
                    text: `${response.message}. Do you want to see the created item?`,
                    icon: "success",
                    allowOutsideClick: false,
                    showCancelButton: true, 
                    confirmButtonText: "Yes",
                    cancelButtonText: "Maybe later" 
                }).then((result) => {
                    if (result.isConfirmed) {
                        const data = response.data
                        is_submit = true
                        window.location.href= `/inventory/edit/${data.id}`
                    } else if (result.isDismissed) {
                        $('#add_inventory').find('input, textarea').val('')
                    }
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                generate_api_error(response, true)
            }
        });
    }
</script>