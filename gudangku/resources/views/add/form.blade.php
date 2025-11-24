<div class="container-form">
    <form id="add_inventory">
        <h2 class="mt-3">Inventory Detail</h2>
        <div class="row">
            <div class="col-lg-6 col-md-6 py-2">
                <input type="text" name="inventory_name" id='inventory_name' class="form-control form-validated mb-2" maxlength="75"/>

                <label>Category</label>
                <select class="form-select mb-2" name="inventory_category" aria-label="Default select example">
                    @foreach($dct_cat as $dct)
                        <option value="{{$dct['dictionary_name']}}">{{$dct['dictionary_name']}}</option>
                    @endforeach
                </select>

                <label>Color</label>
                <input type="text" name="inventory_color" id="inventory_color" class="form-control"/>
            </div>
            <div class="col-lg-6 col-md-6 d-flex align-items-center py-2">
                @include('add.image_picker')
            </div>
            <div class="col-lg-12 py-2">
                <textarea name="inventory_desc" id="inventory_desc" class="form-control form-validated" maxlength="255"></textarea>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6 col-12 py-2">
                <input type="text" name="inventory_merk" id="inventory_merk" class="form-control form-validated" maxlength="75"/>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6 col-12 py-2">
                <input type="number" name="inventory_price" id="inventory_price" class="form-control form-validated"/>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6 col-12 py-2">
                <input type="datetime-local" name="created_at" id="created_at" class="form-control form-validated"/>
            </div>
        </div>
        <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" name="is_favorite" id="is_favorite">
            <label class="form-check-label" for="flexCheckDefault">Set as Favorited Item</label>
        </div>
        <hr>

        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12">
                <h2 class="mt-3">Standard Capacity</h2>
                <div class="row">
                    <div class="col-lg-3 col-md-4 col-sm-4 col-4 py-2">
                        <input type="number" name="inventory_vol" id="inventory_vol" class="form-control form-validated" max="9999" min="1" value="1"/>
                    </div>
                    <div class="col-lg-9 col-md-8 col-sm-8 col-8 py-2">
                        <label>Unit</label>
                        <select class="form-select" name="inventory_unit" aria-label="Default select example">
                            @foreach($dct_unit as $dct)
                                <option value="{{$dct['dictionary_name']}}">{{$dct['dictionary_name']}}</option>
                            @endforeach
                        </select>
                    </div>
                </div><hr>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12">
                <h2 class="mt-3">Remaining Capacity</h2>
                <div class="row">
                    <div class="col-lg-3 col-md-4 col-sm-4 col-4 py-2">
                        <input type="number" name="inventory_capacity_vol" id='inventory_capacity_vol' class="form-control form-validated"/>
                    </div>
                    <div class="col-lg-9 col-md-8 col-sm-8 col-8 py-2">
                        <label>Unit</label>
                        <select class="form-select" name="inventory_capacity_unit" aria-label="Default select example">
                            <option value='-' selected>-</option>
                            <option value="percentage">Percentage</option>
                            @foreach($dct_unit as $dct)
                                <option value="{{$dct['dictionary_name']}}">{{$dct['dictionary_name']}}</option>
                            @endforeach
                        </select>
                    </div>
                </div><hr>
            </div>
        </div>

        <h2 class="mt-3">Placement</h2>
        <div class="row">
            <div class="col-md-4 col-sm-6 col-6 py-2">
                <label>Room</label>
                <select class="form-select" name="inventory_room" aria-label="Default select example">
                    @foreach($dct_room as $dct)
                        <option value="{{$dct['dictionary_name']}}">{{$dct['dictionary_name']}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 col-sm-6 col-6 py-2">
                <input type="text" name="inventory_storage" id="inventory_storage" class="form-control form-validated" maxlength="36"/>
            </div>
            <div class="col-md-4 col-sm-12 col-12 py-2">
                <input type="text" name="inventory_rack" id="inventory_rack" class="form-control form-validated" maxlength="36"/>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <a class="btn btn-success mt-3 border-0" onclick="submit_add()" style="background:var(--successBG) !important; min-width:160px;"><i class="fa-solid fa-floppy-disk"></i> Submit</a>
        </div>
    </form>
</div>

<script>
    const url = window.location.href
    const urlParams = new URL(url).searchParams
    const inventory_name = urlParams.get("inventory_name")
    $('#inventory_name').val(inventory_name)
    setCurrentLocalDateTime('created_at')

    const submit_add = () => {
        const form = $('#add_inventory')[0]
        const formData = new FormData(form)
        formData.set('created_at', tidyUpDateTimeFormat(formData.get('created_at')))

        $.ajax({
            url: '/api/v1/inventory',
            type: 'POST',
            data: formData,
            processData: false, 
            contentType: false,
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
                Swal.showLoading()
            },
            success: function(response) {
                Swal.close()
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
                Swal.close()
                generate_api_error(response, true)
            }
        });
    }
</script>