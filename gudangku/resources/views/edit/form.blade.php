<form class="container-form" id="form_edit_inventory">
    <h2>Inventory Detail</h2>
    <div class="row">
        <div class="col-lg-6 col-md-6">
            <label>Name</label>
            <input type="text" name="inventory_name" id="inventory_name" class="form-control"/>
            <label>Category</label>
            <select class="form-select" name="inventory_category" id="inventory_category_holder" aria-label="Default select example"></select>
            <div id='inventory_color_holder'></div>
        </div>
        <div class="col-lg-6 col-md-6">
            @include('edit.image_picker')
        </div>
        <div class="col-lg-12">
            <label>Description</label>
            <textarea name="inventory_desc" id="inventory_desc" class="form-control"></textarea>
        </div>
        <div class="col-xl-4 col-lg-6 col-md-4 col-sm-4 col-12">
            <label>Merk</label>
            <input type="text" name="inventory_merk" id="inventory_merk" class="form-control"/>
        </div>
        <div class="col-xl-4 col-lg-6 col-md-4 col-sm-4 col-5">
            <label>Price</label>
            <input type="number" name="inventory_price" id="inventory_price" class="form-control"/>
        </div>
        <div class="col-xl-4 col-md-4 col-sm-4 col-7">
            <label>Created At</label>
            <input class='form-control' type='datetime-local' id='created_at_edit' name='created_at'>
        </div>
    </div><hr>

    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12">
            <h6 class="fw-bold mt-3">Standard Capacity</h6>
            <div class="row">
                <div class="col-lg-3 col-md-4 col-sm-4 col-4">
                    <label>Volume</label>
                    <input type="number" name="inventory_vol" id="inventory_vol" class="form-control"/>
                </div>
                <div class="col-lg-9 col-md-8 col-sm-8 col-8">
                    <label>Unit</label>
                    <select class="form-select" name="inventory_unit" id="inventory_unit_holder" aria-label="Default select example"></select>
                </div>
            </div><hr>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12">
            <h6 class="fw-bold mt-3">Remaining Capacity</h6>
            <div class="row">
                <div class="col-lg-3 col-md-4 col-sm-4 col-4">
                    <label>Volume</label>
                    <input type="number" name="inventory_capacity_vol" id="inventory_capacity_vol"  class="form-control"/>
                </div>
                <div class="col-lg-9 col-md-8 col-sm-8 col-8">
                    <label>Unit</label>
                    <select class="form-select" name="inventory_capacity_unit" id="inventory_capacity_unit_holder" aria-label="Default select example"></select>
                </div>
            </div><hr>   
        </div>
    </div>

    <h6 class="fw-bold mt-3">Placement</h6>
    <div class="row">
        <div class="col-md-4 col-sm-6 col-6">
            <label>Room</label>
            <select class="form-select" name="inventory_room" id="inventory_room_holder" aria-label="Default select example"></select>
        </div>
        <div class="col-md-4 col-sm-6 col-6">
            <label>Storage</label>
            <input type="text" name="inventory_storage" id="inventory_storage" class="form-control"/>
        </div>
        <div class="col-md-4 col-sm-12 col-12">
            <label>Rack</label>
            <input type="text" name="inventory_rack" id="inventory_rack" class="form-control"/>
        </div>
    </div>
    <div class="d-grid d-md-inline-block mt-3">
        <a id="save_changes" class="btn btn-success w-100 w-md-auto"><i class="fa-solid fa-floppy-disk"></i> Save Changes</a>
    </div>
</form>

<div class="container-form">
    @include('edit.reminder')
</div>

<div class="container-form">
    @include('edit.report')
</div>

<script>
    $(document).on('click','#save_changes',function(){
        save_update()
    })

    const inventory_id = '<?= $id ?>'

    const getDetailInventoryByID = (id) => {
        const item_holder = 'report_holder'
        $.ajax({
            url: `/api/v1/inventory/detail/${id}`,
            type: 'GET',
            beforeSend: function (xhr) {
                Swal.showLoading()
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
            },
            success: function(response) {
                Swal.close()
                const data = response.data
                const reminder = response.reminder

                if(data.inventory_image){
                    $('#inventory_color_holder').html(`
                        <label>Color</label>
                        <input type="text" name="inventory_color" id="inventory_color" value='${data.inventory_color ?? ''}' class="form-control" readonly/>
                    `)
                    $('#img_holder').empty().prepend(`
                        <div class='no-image-picker' title='Change Image' id='image-picker'>
                            <label for='file-input'>
                                <img id='frame' class='img-responsive img-zoomable-modal d-block mx-auto' title='Change Image' src='${data.inventory_image}' data-bs-toggle='modal' data-bs-target='#zoom_image'/>
                            </label>
                            <input id='file-input' name='file' type='file' accept='image/*' class='d-none'/>
                        </div>
                        <div class='no-image-picker d-none' title='Change Image' id='no-image-picker'></div>
                    `)
                    $('#reset_img_btn_handler').html(`<a class="btn btn-danger px-2 shadow" id='reset-image-btn' title="Reset to default image"><i class="fa-solid fa-trash-can"></i> Reset Image</a>`)
                } else {
                    $('#img_holder').empty().prepend(`
                        <div class='no-image-picker' title='Change Image' id='image-picker'>
                            <label for='file-input'>
                                <img id='frame' class='m-2' title='Change Image' style='width: var(--spaceXLG)' src='<?= asset('images/change_image.png') ?>' />
                                <a class="bg-transparent">No image has been selected</a>
                            </label>
                            <input id='file-input' name='file' type='file' accept='image/*' class='d-none'/>
                        </div>
                        <div class='no-image-picker d-none' title='Change Image' id='no-image-picker'></div>
                    `)
                }

                $('#btn-toogle-fav-holder').html(`
                    <a class="btn btn-danger btn-toggle-favorite" onclick="favToogleInventoryByID('${id}', ${data.is_favorite == 0 ? '1' : '0'}, '<?= session()->get("token_key"); ?>', 
                        ()=>getDetailInventoryByID('${id}'))" style="${data.is_favorite ? 'background:var(--dangerBG); border:none;' : ''}">
                        <i class="fa-solid fa-heart mx-2"></i>
                    </a>
                `)

                getReminderLayout(reminder,inventory_id)
                
                $('#inventory_name').val(data.inventory_name)
                $('#inventory_desc').text(data.inventory_desc)
                $('#inventory_storage').val(data.inventory_storage)
                $('#inventory_rack').val(data.inventory_rack)
                $('#inventory_merk').val(data.inventory_merk)
                $('#inventory_price').val(data.inventory_price)
                $('#item_price').val(data.inventory_price)
                $('#inventory_vol').val(data.inventory_vol)
                $('#inventory_capacity_vol').val(data.inventory_capacity_vol)
                $('#inventory_room_holder').val(data.inventory_room)
                $('#inventory_unit_holder').val(data.inventory_unit)
                $('#inventory_capacity_unit_holder').val(data.inventory_capacity_unit)
                $('#inventory_category_holder').val(data.inventory_category)
                $('#reminder_type').val(data.reminder_type)
                $('#reminder_context').val(data.reminder_context)
                $('#reminder_desc').text(data.reminder_desc)
                $('#created_at').text(getDateToContext(data.created_at,'calendar',false))
                $('#created_at_edit').val(getDateToContext(data.created_at,'calendar',false))
                $('#updated_at').text(data.updated_at ? getDateToContext(data.updated_at,'calendar') : '-')
                $('#inventory_name_add_report').val(data.inventory_name)
                $('#inventory_name_title_add_report').text(data.inventory_name)
                $('#inventory_id_add_report').val(data.id)
                $('#form_add_report').attr('action', `/inventory/edit/${data.id}/editInventory/addReport`)
                $('#form_edit_inventory').attr('action', `/inventory/edit/${data.id}/editInventory`)
                $('#inventory_name_add_reminder').val(data.inventory_name)
                $('#inventory_name_title_add_reminder').text(data.inventory_name)
                $('#inventory_id_add_reminder').val(data.id)

                getAllReport(page,data.inventory_name,data.id)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                if(response.status != 404){
                    generateAPIError(response, true)
                } else {
                    Swal.fire({
                        title: "Failed!",
                        text: response.responseJSON.message,
                        icon: "error",
                        allowOutsideClick: false, 
                        allowEscapeKey: false, 
                        confirmButtonText: "Back to Inventory", 
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '/inventory'
                        }
                    })
                }
            }
        })
    }
    
    $(async function () {
        await getDictionaryByContext('inventory_category,inventory_room,inventory_capacity_unit,inventory_unit',token)
        getDetailInventoryByID(inventory_id)
    })

    const save_update = () => {
        const id = `<?= $id ?>`
        $.ajax({
            url: `/api/v1/inventory/edit/${id}`,
            type: 'PUT',
            data: $('#form_edit_inventory').serialize().replace(
                /created_at=[^&]+/,
                "created_at=" + tidyUpDateTimeFormat($('#created_at_edit').val())
            ),
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)
                Swal.showLoading()
            },
            success: function(response) {
                Swal.hideLoading()
                Swal.fire({
                    title: "Success!",
                    text: response.message,
                    icon: "success",
                    allowOutsideClick: false,
                    confirmButtonText: "Ok"
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        await getDictionaryByContext('inventory_category,inventory_room,inventory_capacity_unit,inventory_unit',token)
                        getDetailInventoryByID(id)
                    }
                })
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                generateAPIError(response, true)
            }
        })
    }
</script>