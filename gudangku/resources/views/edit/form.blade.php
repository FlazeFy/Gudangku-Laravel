<form class="container-form" id="form_edit_inventory">
    <h6 class="fw-bold mt-3" style="font-size:var(--textXLG);">Inventory Detail</h6>
    <div class="row">
        <div class="col-lg-6 col-md-6 py-2">
            <label>Name</label>
            <input type="text" name="inventory_name" id="inventory_name" class="form-control my-2"/>
            <label>Category</label>
            <select class="form-select my-2" name="inventory_category" id="inventory_category" aria-label="Default select example"></select>
            <div id='inventory_color_holder'></div>
        </div>
        <div class="col-lg-6 col-md-6 py-2">
            @include('edit.image_picker')
        </div>
        <div class="col-lg-12 py-2">
            <label>Description</label>
            <textarea name="inventory_desc" id="inventory_desc" class="form-control"></textarea>
        </div>
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 col-12 py-2">
            <label>Merk</label>
            <input type="text" name="inventory_merk" id="inventory_merk" class="form-control"/>
        </div>
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 col-12 py-2">
            <label>Price</label>
            <input type="number" name="inventory_price" id="inventory_price" class="form-control"/>
        </div>
        <div class="col-xl-4 col-12 py-2">
            <label>Created At</label>
            <input class='form-control' type='datetime-local' id='created_at_edit' name='created_at'>
        </div>
    </div><hr>

    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12">
            <h6 class="fw-bold mt-3" style="font-size:var(--textXLG);">Standard Capacity</h6>
            <div class="row">
                <div class="col-lg-3 col-md-4 col-sm-4 col-4 py-2">
                    <label>Volume</label>
                    <input type="number" name="inventory_vol" id="inventory_vol" class="form-control"/>
                </div>
                <div class="col-lg-9 col-md-8 col-sm-8 col-8 py-2">
                    <label>Unit</label>
                    <select class="form-select" name="inventory_unit" id="inventory_unit" aria-label="Default select example"></select>
                </div>
            </div><hr>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12">
            <h6 class="fw-bold mt-3" style="font-size:var(--textXLG);">Remaining Capacity</h6>
            <div class="row">
                <div class="col-lg-3 col-md-4 col-sm-4 col-4 py-2">
                    <label>Volume</label>
                    <input type="number" name="inventory_capacity_vol" id="inventory_capacity_vol"  class="form-control"/>
                </div>
                <div class="col-lg-9 col-md-8 col-sm-8 col-8 py-2">
                    <label>Unit</label>
                    <select class="form-select" name="inventory_capacity_unit" id="inventory_capacity_unit" aria-label="Default select example"></select>
                </div>
            </div><hr>   
        </div>
    </div>

    <h6 class="fw-bold mt-3" style="font-size:var(--textXLG);">Placement</h6>
    <div class="row">
        <div class="col-md-4 col-sm-6 col-6 py-2">
            <label>Room</label>
            <select class="form-select" name="inventory_room" id="inventory_room" aria-label="Default select example"></select>
        </div>
        <div class="col-md-4 col-sm-6 col-6 py-2">
            <label>Storage</label>
            <input type="text" name="inventory_storage" id="inventory_storage" class="form-control"/>
        </div>
        <div class="col-md-4 col-sm-12 col-12 py-2">
            <label>Rack</label>
            <input type="text" name="inventory_rack" id="inventory_rack" class="form-control"/>
        </div>
    </div>
    <div class="d-flex justify-content-end">
        <a id="save_changes" class="btn btn-success mt-3 border-0" style="background:var(--successBG) !important; min-width:160px;"><i class="fa-solid fa-floppy-disk"></i> Submit</a>
    </div>
</form>

<div class="container-form">
    <div class="d-flex justify-content-between">
        <h6 class="fw-bold mt-3" style="font-size:var(--textXLG);">Reminder</h6>
        <span id="add_reminder-holder"></span>
    </div>
    <div id='reminder_holder'></div>
</div>

<div class="container-form">
    @include('edit.report')
</div>

<script>
    $(document).on('click','#save_changes',function(){
        save_update()
    })

    const inventory_id = '<?= $id ?>'

    const get_detail_inventory = (id) => {
        const item_holder = 'report_holder'
        $.ajax({
            url: `/api/v1/inventory/detail/${id}`,
            type: 'GET',
            beforeSend: function (xhr) {
                Swal.showLoading()
                xhr.setRequestHeader("Accept", "application/json");
                xhr.setRequestHeader("Authorization", `Bearer ${token}`);    
            },
            success: function(response) {
                Swal.close()
                const data = response.data
                const reminder = response.reminder

                if(data.inventory_image){
                    $('#inventory_color_holder').html(`
                        <label>Color</label>
                        <input type="text" name="inventory_color" id="inventory_color" value='${data.inventory_color ?? ''}' class="form-control my-2" readonly/>
                    `)
                    $('#img_holder').empty().prepend(`
                        <form id='edit-image'>
                            <div class='no-image-picker' title='Change Image' id='image-picker'>
                                <label for='file-input'>
                                    <img id='frame' class='img-responsive img-zoomable-modal d-block mx-auto' title='Change Image' src='${data.inventory_image}' data-bs-toggle='modal' data-bs-target='#zoom_image'/>
                                </label>
                                <input id='file-input' name='file' type='file' accept='image/*' class='d-none'/>
                            </div>
                            <div class='no-image-picker d-none' title='Change Image' id='no-image-picker'></div>
                        </form>
                    `)
                    $('#reset_img_btn_handler').html(`<a class="btn btn-danger px-2 shadow" id='reset-image-btn' title="Reset to default image"><i class="fa-solid fa-trash-can"></i> Reset Image</a>`)
                } else {
                    $('#img_holder').empty().prepend(`
                        <form id='edit-image'>
                            <div class='no-image-picker' title='Change Image' id='image-picker'>
                                <label for='file-input'>
                                    <img id='frame' class='m-2' title='Change Image' style='width: var(--spaceXLG);' src='<?= asset('images/change_image.png') ?>' />
                                    <a>No image has been selected</a>
                                </label>
                                <input id='file-input' name='file' type='file' accept='image/*' class='d-none'/>
                            </div>
                            <div class='no-image-picker d-none' title='Change Image' id='no-image-picker'></div>
                        </form>
                    `)
                }

                $('#btn-toogle-fav-holder').html(`
                    <a class="btn btn-danger mb-3 me-2 btn-toggle-favorite" onclick="fav_toogle_inventory_by_id('${id}', ${data.is_favorite == 0 ? '1' : '0'}, '<?= session()->get("token_key"); ?>', 
                        ()=>get_detail_inventory('${id}'))" style="${data.is_favorite ? 'background:var(--dangerBG); border:none;' : ''}">
                        <i class="fa-solid fa-heart mx-2" style="font-size:var(--textXLG);"></i>
                    </a>
                `)

                if(reminder){
                    $('#reminder_holder').empty()
                    reminder.forEach(dt => {
                        $('#reminder_holder').append(`
                            <div class="btn btn-primary w-100 text-start mt-3 mb-1" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReminder${dt.id}" aria-expanded="false" aria-controls="collapseExample">
                                <div class='d-flex justify-content-between'>
                                    <a>Reminder : ${dt.reminder_desc}</a>
                                    <span class='rounded-pill bg-success px-2 py-1' style='font-size:var(--textMD); font-weight:600;'><i class="fa-solid fa-bell"></i> ${dt.reminder_type} at ${dt.reminder_context}</span>
                                </div>
                            </div>
                            <div class="collapse" id="collapseReminder${dt.id}">
                                <div class="container py-0 ps-4 ms-5 w-auto" style='border-left: var(--spaceMini) solid var(--primaryColor); border-radius:0;'>
                                    <div class='d-flex justify-content-between'>
                                        <div>
                                            <p class='date-text mb-0'>Created At : ${getDateToContext(dt.created_at,'calendar')}</p>
                                            <p class='date-text mb-0'>Last Updated : ${dt.updated_at ? getDateToContext(dt.updated_at,'calendar') : '-'}</p>
                                        </div>
                                        <a class='btn btn-danger' data-bs-toggle="modal" data-bs-target="#modalDeleteReminder_${dt.id}"><i class="fa-solid fa-trash"></i> Delete Reminder</a>
                                        <div class="modal fade" tabindex="-1" aria-labelledby="exampleModalLabel" id="modalDeleteReminder_${dt.id}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold" id="exampleModalLabel">Delete Reminder</h5>
                                                        <a class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></a>
                                                    </div>
                                                    <div class="modal-body">
                                                        <h2><span class="text-danger">Permentally Delete</span> this reminder "${dt.reminder_desc}"?</h2>
                                                        <a class="btn btn-danger mt-4"
                                                            onclick="delete_reminder_by_id('${dt.id}', '${token}', () => get_detail_inventory('${inventory_id}'))"> Yes, Delete
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6 py-2">
                                            <label>Type</label>
                                            <select class="form-select" name="reminder_type" aria-label="Default select example"></select>
                                        </div>
                                        <div class="col-lg-6 py-2">
                                            <label>Context</label>
                                            <select class="form-select" name="reminder_context" aria-label="Default select example"></select>
                                        </div>
                                        <div class="col-lg-6 py-2">
                                            <label>Description</label>
                                            <textarea name="reminder_desc" class="form-control">${dt.reminder_desc}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `)
                    });
                    $('#add_reminder-holder').html(`
                        <a class='btn btn-success' data-bs-toggle="modal" data-bs-target="#modalAddReminder"><i class="fa-solid fa-plus" style="font-size:var(--textXLG);"></i> Add Reminder</a>
                    `)
                } else {
                    $('#reminder_holder').html(`
                        <div class="container-fluid p-3" style="background-color:rgba(59, 131, 246, 0.2);">
                            <h6><i class="fa-regular fa-clock"></i> This inventory doesn't have reminder</h6>
                            <a class="btn btn-primary mt-3" data-bs-toggle='modal' data-bs-target='#modalAddReminder'><i class="fa-solid fa-plus"></i> Add New Reminder</a>
                        </div>
                    `)
                }
                
                $('#inventory_name').val(data.inventory_name)
                $('#inventory_desc').text(data.inventory_desc)
                $('#inventory_storage').val(data.inventory_storage)
                $('#inventory_rack').val(data.inventory_rack)
                $('#inventory_merk').val(data.inventory_merk)
                $('#inventory_price').val(data.inventory_price)
                $('#item_price').val(data.inventory_price)
                $('#inventory_vol').val(data.inventory_vol)
                $('#inventory_capacity_vol').val(data.inventory_capacity_vol)
                $('#inventory_room').val(data.inventory_room)
                $('#inventory_unit').val(data.inventory_unit)
                $('#inventory_capacity_unit').val(data.inventory_capacity_unit)
                $('#inventory_category').val(data.inventory_category)
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
                get_my_report_all(page,data.inventory_name,data.id)

                $('#inventory_name_add_reminder').val(data.inventory_name)
                $('#inventory_name_title_add_reminder').text(data.inventory_name)
                $('#inventory_id_add_reminder').val(data.id)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                if(response.status != 404){
                    generate_api_error(response, true)
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
                    });
                }
            }
        });
    }
    
    const get_dictionary = () => {
        const type = 'inventory_room,inventory_unit,inventory_category,reminder_type,reminder_context,report_category'
        $.ajax({
            url: `/api/v1/dictionary/type/${type}`,
            type: 'GET',
            beforeSend: function (xhr) {
                Swal.showLoading()
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
            },
            success: function(response) {
                Swal.close()
                const data = response.data
                
                $('#inventory_unit').empty()
                $('#inventory_capacity_unit').empty()
                $('#inventory_capacity_unit').append('<option>-</option>')
                data.forEach(dt => {
                    $( document ).ready(function() {
                        if(dt.dictionary_type == 'inventory_unit'){
                            $('#inventory_unit').append(`<option value='${dt.dictionary_name}'>${dt.dictionary_name}</option>`)
                            $('#inventory_capacity_unit').append(`<option value='${dt.dictionary_name}'>${dt.dictionary_name}</option>`)
                        } else if(dt.dictionary_type == 'reminder_type' || dt.dictionary_type == 'reminder_context'){
                            $(`select[name="${dt.dictionary_type}"]`).each(function() {
                                const isFirst = $(this).children('option').length === 0
                                const selectedAttr = isFirst ? 'selected' : ''
                                $(this).append(`<option value='${dt.dictionary_name}' ${selectedAttr}>${dt.dictionary_name}</option>`)
                            })
                        } else {
                            $(`#${dt.dictionary_type}`).append(`<option value='${dt.dictionary_name}'>${dt.dictionary_name}</option>`)
                        }
                    });
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                generate_api_error(response, true)
            }
        });
    }

    get_dictionary()
    get_detail_inventory(inventory_id)

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
                }).then((result) => {
                    if (result.isConfirmed) {
                        get_dictionary();
                        get_detail_inventory(id);
                    }
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                generate_api_error(response, true)
            }
        });
    }
</script>