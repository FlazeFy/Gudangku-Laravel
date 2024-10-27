<form id="form_edit_inventory" method="POST">
    @csrf
    <h6 class="fw-bold mt-3" style="font-size:var(--textXLG);">Inventory Detail</h6>
    <div class="row">
        <div class="col-lg-6 py-2">
            <label>Name</label>
            <input type="text" name="inventory_name" id="inventory_name" class="form-control my-2"/>
            <label>Category</label>
            <select class="form-select my-2" name="inventory_category" id="inventory_category" aria-label="Default select example"></select>
            <div id='inventory_color_holder'></div>
        </div>
        <div class="col-lg-6 py-2">
            @include('edit.image_picker')
        </div>
        <div class="col-lg-12 py-2">
            <label>Description</label>
            <textarea name="inventory_desc" id="inventory_desc" class="form-control mt-2"></textarea>
        </div>
        <div class="col-lg-6 py-2">
            <label>Merk</label>
            <input type="text" name="inventory_merk" id="inventory_merk" class="form-control mt-2"/>
        </div>
        <div class="col-lg-6 py-2">
            <label>Price</label>
            <input type="number" name="inventory_price" id="inventory_price" class="form-control mt-2"/>
        </div>
    </div><hr>

    <h6 class="fw-bold mt-3" style="font-size:var(--textXLG);">Standard Capacity</h6>
    <div class="row">
        <div class="col-lg-6 py-2">
            <label>Volume</label>
            <input type="number" name="inventory_vol" id="inventory_vol" class="form-control mt-2"/>
        </div>
        <div class="col-lg-6 py-2">
            <label>Unit</label>
            <select class="form-select mt-2" name="inventory_unit" id="inventory_unit" aria-label="Default select example"></select>
        </div>
    </div><hr>

    <h6 class="fw-bold mt-3" style="font-size:var(--textXLG);">Remaining Capacity</h6>
    <div class="row">
        <div class="col-lg-6 py-2">
            <label>Volume</label>
            <input type="number" name="inventory_capacity_vol" id="inventory_capacity_vol"  class="form-control mt-2"/>
        </div>
        <div class="col-lg-6 py-2">
            <label>Unit</label>
            <select class="form-select mt-2" name="inventory_capacity_unit" id="inventory_capacity_unit" aria-label="Default select example"></select>
        </div>
    </div><hr>

    <h6 class="fw-bold mt-3" style="font-size:var(--textXLG);">Placement</h6>
    <div class="row">
        <div class="col-lg-6 py-2">
            <label>Room</label>
            <select class="form-select mt-2" name="inventory_room" id="inventory_room" aria-label="Default select example"></select>
        </div>
        <div class="col-lg-6 py-2">
            <label>Storage</label>
            <input type="text" name="inventory_storage" id="inventory_storage" class="form-control mt-2"/>
        </div>
        <div class="col-lg-6 py-2">
            <label>Rack</label>
            <input type="text" name="inventory_rack" id="inventory_rack" class="form-control mt-2"/>
        </div>
    </div><hr>

    <h6 class="fw-bold mt-3" style="font-size:var(--textXLG);">Reminder</h6>
    <div id='reminder_holder'></div>

    <button type="submit" class="btn btn-success mt-3 w-100 border-0" style="background:var(--successBG) !important;"><i class="fa-solid fa-floppy-disk"></i> Submit</button>
</form>

<script>
    const get_detail_inventory = (id) => {
        const item_holder = 'report_holder'
        Swal.showLoading()
        $.ajax({
            url: `/api/v1/inventory/detail/${id}`,
            type: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json");
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>");    
            },
            success: function(response) {
                Swal.close()
                const data = response.data

                if(data.inventory_image){
                    $('#inventory_color_holder').html(`
                        <label>Color</label>
                        <input type="text" name="inventory_color" id="inventory_color" value='${data.inventory_color}' class="form-control my-2" readonly/>
                    `)
                    $('#img_holder').empty().prepend(`
                        <div class='no-image-picker' title='Change Image' id='no-image-picker'>
                            <label for='file-input'>
                                <img id='frame' class='m-2 inventory-image' title='Change Image' src='${data.inventory_image}'/>
                            </label>
                            <input id='file-input' type='file' accept='image/*' style='display: none;' onchange='setValueInventoryImage()'/>
                        </div>
                    `)
                    $('#reset_img_btn_handler').html(`<a class="btn btn-danger px-2 shadow" title="Reset to default image" onclick="clearImage('${data.id}')"><i class="fa-solid fa-trash-can"></i> Reset Image</a>`)
                } else {
                    $('#img_holder').empty().prepend(`
                        <div class='no-image-picker' title='Change Image' id='no-image-picker'>
                            <label for='file-input'>
                                <img id='frame' class='m-2' title='Change Image' style='width: var(--spaceXLG);' src='<?= asset('images/change_image.png') ?>' />
                                <a>No image has been selected</a>
                            </label>
                            <input id='file-input' type='file' accept='image/*' style='display: none;' onchange='setValueInventoryImage()'/>
                        </div>
                    `)
                }

                if(data.reminder){
                    $('#reminder_holder').html(`
                        <div class="row">
                            <div class="col-lg-6 py-2">
                                <label>Type</label>
                                <select class="form-select mt-2" name="reminder_type" id="reminder_type" aria-label="Default select example"></select>
                            </div>
                            <div class="col-lg-6 py-2">
                                <label>Context</label>
                                <select class="form-select mt-2" name="reminder_context" id="reminder_context" aria-label="Default select example"></select>
                            </div>
                            <div class="col-lg-6 py-2">
                                <label>Description</label>
                                <textarea name="reminder_desc" class="form-control mt-2"></textarea>
                            </div>
                        </div>
                    `)
                } else {
                    $('#reminder_holder').html(`
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
                    `)
                }
                
                
                $('#inventory_name').val(data.inventory_name)
                $('#inventory_desc').text(data.inventory_desc)
                $('#inventory_storage').val(data.inventory_storage)
                $('#inventory_rack').val(data.inventory_rack)
                $('#inventory_merk').val(data.inventory_merk)
                $('#inventory_price').val(data.inventory_price)
                $('#inventory_vol').val(data.inventory_vol)
                $('#inventory_capacity_vol').val(data.inventory_capacity_vol)
                $('#inventory_room').val(data.inventory_room)
                $('#inventory_unit').val(data.inventory_unit)
                $('#inventory_capacity_unit').val(data.inventory_capacity_unit)
                $('#inventory_category').val(data.inventory_category)
                $('#reminder_type').val(data.reminder_type)
                $('#reminder_context').val(data.reminder_context)
                $('#reminder_desc').text(data.reminder_desc)
                
                $('#inventory_name_add_report').val(data.inventory_name)
                $('#inventory_name_title_add_report').val(data.inventory_name)
                $('#inventory_id_add_report').val(data.id)
                $('#form_add_report').attr('action', `/inventory/edit/${data.id}/editInventory/addReport`)
                $('#form_edit_inventory').attr('action', `/inventory/edit/${data.id}/editInventory`)
                get_my_report_all(page,data.inventory_name,data.id)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                if(response.status != 404){
                    Swal.fire({
                        title: "Oops!",
                        text: "Something wrong. Please contact admin",
                        icon: "error"
                    });
                }
            }
        });
    }
    const get_dictionary = () => {
        const type = 'inventory_room,inventory_unit,inventory_category,reminder_type,reminder_context,report_category'
        Swal.showLoading()
        $.ajax({
            url: `/api/v1/dictionary/type/${type}`,
            type: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
            },
            success: function(response) {
                Swal.close()
                const data = response.data
                
                data.forEach(dt => {
                    if(dt.dictionary_type == 'inventory_unit'){
                        $('#inventory_unit').append(`<option value='${dt.dictionary_name}'>${dt.dictionary_name}</option>`)
                        $('#inventory_capacity_unit').append(`<option value='${dt.dictionary_name}'>${dt.dictionary_name}</option>`)
                    } else {
                        $(`#${dt.dictionary_type}`).append(`<option value='${dt.dictionary_name}'>${dt.dictionary_name}</option>`)
                    }
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                if(response.status != 404){
                    Swal.fire({
                        title: "Oops!",
                        text: "Something wrong. Please contact admin",
                        icon: "error"
                    });
                }
            }
        });
    }

    get_dictionary()
    get_detail_inventory("<?= $id ?>")
</script>