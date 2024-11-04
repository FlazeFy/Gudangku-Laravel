<div id="report_holder"></div>
<div id="report_check_action"></div>
<div id="report_item_holder">
    <table class="table mt-3" id="report_item_tb">
        <thead></thead>
        <tbody></tbody>
    </table>
</div>

<script>
    const is_edit_mode = <?= session()->get('toogle_edit_report') ?>;
    let report_title

    const get_detail_report = async (id) => {
        try {
            Swal.showLoading()
            const list_cat = await get_dct_by_type('report_category')
            const response = await $.ajax({
                url: `/api/v1/report/detail/item/${id}`,
                type: 'GET',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader("Accept", "application/json")
                    xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
                }
            })
            Swal.close()

            const item_holder = 'report_holder'
            const data = response.data
            const data_item = response.data_item
            report_title = data.report_title
            let select_cat_el = ''

            $('#created_at').text(getDateToContext(data.created_at,'calendar'))
            $('#updated_at').text(data.updated_at ? getDateToContext(data.updated_at,'calendar') : '-')
            list_cat.forEach(el => {
                select_cat_el += `<option value='${el}' ${el == data.report_category && 'selected'}>${el}</option>`
            });

            if(data_item.length > 0){
                $(`#btn-doc-preview-holder`).html(`
                    <a class="btn btn-primary mb-3 me-2" href="/doc/report/${data.id}"><i class="fa-solid fa-print" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Print @endif</a>
                    <a class="btn btn-primary mb-3 me-2" href="/doc/report/${data.id}/custom"><i class="fa-solid fa-pen-to-square" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Custom Print @endif</a>
                `)
            }
            if(data.report_category === 'Shopping Cart' || data.report_category === 'Wishlist'){
                $('#report_item_tb thead').html(`
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Item Name</th>
                        <th scope="col">Description</th>
                        <th scope="col">Qty</th>
                        <th scope="col">Price</th>
                        <th scope="col">Created At</th>
                        <th scope="col">Edit</th>
                        <th scope="col">Remove</th>
                    </tr>
                `)
            } else {
                $('#report_item_tb thead').html(`
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Item Name</th>
                        <th scope="col">Description</th>
                        <th scope="col">Qty</th>
                        <th scope="col">Created At</th>
                        <th scope="col">Edit</th>
                        <th scope="col">Remove</th>
                    </tr>
                `)
            }

            $(`#${item_holder}`).html(`
                <div class="d-flex justify-content-between mb-2">
                    <div>
                        ${is_edit_mode ? 
                            `<label>Title</label>
                            <input class='form-control' id='report_title' style='min-width:480px;' value='${data.report_title}'>`
                            :
                            `<h3 style='font-weight:500; font-size:var(--textXJumbo);'>${data.report_title}</h3>`
                        }
                    </div>
                    <div>
                        ${is_edit_mode ? 
                            `<label>Category</label>
                            <select class='form-select' id='report_category' value='${data.report_category}'>${select_cat_el}</select>`
                            :
                            `<span class="bg-success text-white rounded-pill px-3 py-2">${data.report_category}</span>`
                        }
                    </div>
                </div>
                ${is_edit_mode ? 
                    `<label>Description</label>
                    <textarea class='form-control' id='report_desc'>${data.report_desc}</textarea>
                    <a class="btn btn-success my-3 me-2" id='save-edit-modal-btn' data-bs-toggle='modal' data-bs-target='#update-validation-modal'><i class="fa-solid fa-floppy-disk" style="font-size:var(--textXLG);"></i> Save Changes</a>
                    <div class="modal fade" id="update-validation-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h2 class="modal-title fw-bold" id="exampleModalLabel">Update</h2>
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                                </div>
                                <div class="modal-body">
                                    <h2>Are you sure want to <span class="text-warning">update</span> this report? The generated document will affected too</h2>
                                    <button class="btn btn-success mt-4" id="submit-update-report-btn" onclick="update_report('{{"$id"}}')" >Yes, Update</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    `
                    :
                    `${data.report_desc ? `<p class="mt-2">${data.report_desc}</p>` : `<p class="text-secondary fst-italic mt-2">- No Description Provided -</p>`}`
                }
                <br>
                ${(data.report_category === 'Shopping Cart' || data.report_category === 'Wishlist') && `
                    <div class="d-flex justify-content-between mt-3">
                        <div>
                            <h3 class="fw-bold" style="font-size:var(--textJumbo);">Total Price: Rp. ${number_format(data.total_price, 0, ',', '.')}</h3>
                        </div>
                        <div>
                            <h3 class="fw-bold" style="font-size:var(--textJumbo);">Total Item: ${data.total_item}</h3>
                        </div>
                    </div>
                `}
            `)

            $('#report_item_tb tbody').empty()
            data_item.forEach(dt => {
                $('#report_item_tb tbody').append(`
                    <tr>
                        <td>
                            <div class="form-check">
                                <input class="form-check-input check-inventory" type="checkbox" value="${dt.id}_${dt.item_name}">
                            </div>
                        </td>
                        <td>${dt.item_name}</td>
                        <td>${dt.item_desc ?? '<span class="fst-italic text-secondary">- No Description Provided -</span>'}</td>
                        <td>${dt.item_qty}</td>
                        ${data.report_category === 'Shopping Cart' || data.report_category === 'Wishlist' ? `<td>Rp. ${number_format(dt.item_price, 0, ',', '.')}</td>` : ''}
                        <td>${getDateToContext(dt.created_at,'calendar')}</td>
                        <td><button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit_${dt.id}"><i class="fa-solid fa-pen-to-square" style="font-size:var(--textXLG);"></i></button>
                            <div class="modal fade" id="modalEdit_${dt.id}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h2 class="modal-title fw-bold" id="exampleModalLabel">Update Report Item : ${dt.item_name}</h2>
                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                                        </div>
                                        <div class="modal-body">
                                            <form id='edit-report-item-${dt.id}'>
                                                <label>Name</label>
                                                <input class="form-control" type="text" name="item_name" value="${dt.item_name}">
                                                <label>Description</label>
                                                <textarea class="form-control mt-2" name="item_desc">${dt.item_desc ?? ''}</textarea>
                                                <label>Qty</label>
                                                <input class="form-control" type="number" name="item_qty" value="${dt.item_qty}" min="1">
                                                ${
                                                    data.report_category.includes('Shopping Cart','Wishlist') ? `
                                                    <label>Price</label>
                                                    <input class="form-control" name="item_price" type="number" value="${dt.item_price}" min="1">` :''
                                                }
                                                <a class="btn btn-success mt-3 w-100 border-0" onclick="update_report_item('${dt.id}')" style="background:var(--successBG) !important;"><i class="fa-solid fa-floppy-disk"></i> Save Changes</a>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalDelete_${dt.id}"><i class="fa-solid fa-fire" style="font-size:var(--textXLG);"></i></button>
                            <div class="modal fade" id="modalDelete_${dt.id}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h2 class="modal-title fw-bold" id="exampleModalLabel">Delete</h2>
                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                                        </div>
                                        <div class="modal-body">
                                            <h2>Remove this item "${dt.item_name}" from report "${data.report_title}"?</h2>
                                            <a class="btn btn-danger mt-4" onclick="delete_item('${dt.id}')">Yes, Delete</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                `)
            });
        } catch (error) {
            Swal.close();
            Swal.fire({
                title: "Oops!",
                text: "Failed to get the report",
                icon: "error"
            });
        }
    };
    get_detail_report('{{$id}}')

    const update_report_item = (id) => {
        Swal.showLoading()
        $.ajax({
            url: `/api/v1/report/update/report_item/${id}`,
            type: 'PUT',
            data: $(`#edit-report-item-${id}`).serialize(),
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
            },
            success: function(response) {
                Swal.close()
                Swal.fire({
                    title: "Success!",
                    text: response.message,
                    icon: "success"
                }).then((result) => {
                    if (result.isConfirmed) {
                        get_detail_report('{{$id}}')
                    }
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.hideLoading()
                Swal.fire({
                    title: "Oops!",
                    text: "Failed to update the item",
                    icon: "error"
                });
            }
        });
    }

    const delete_item = (id) => {
        Swal.showLoading()
        $.ajax({
            url: `/api/v1/report/delete/item/${id}`,
            type: 'DELETE',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
            },
            success: function(response) {
                Swal.close()
                Swal.fire({
                    title: "Success!",
                    text: response.message,
                    icon: "success"
                }).then((result) => {
                    if (result.isConfirmed) {
                        get_detail_report('{{$id}}')
                    }
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.hideLoading()
                Swal.fire({
                    title: "Oops!",
                    text: "Failed to delete the item",
                    icon: "error"
                });
            }
        });
    }

    const update_report = (id) => {
        Swal.showLoading()
        $.ajax({
            url: `/api/v1/report/update/report/${id}`,
            type: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify({
                report_title: $('#report_title').val(),
                report_desc: $('#report_desc').val(),
                report_category: $('#report_category').val(),
            }),
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
            },
            success: function(response) {
                Swal.close()
                Swal.fire({
                    title: "Success!",
                    text: response.message,
                    icon: "success"
                }).then((result) => {
                    if (result.isConfirmed) {
                        get_detail_report(id)
                    }
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.hideLoading()
                Swal.fire({
                    title: "Oops!",
                    text: "Failed to update the report",
                    icon: "error"
                });
            }
        });
    }

    const get_dictionary = () => {
        const type = 'report_category'
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
                    $( document ).ready(function() {
                        $(`#${dt.dictionary_type}_split`).append(`<option value='${dt.dictionary_name}'>${dt.dictionary_name}</option>`)
                    });
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

    const split_report = (id,item_id) => {
        Swal.showLoading()
        $.ajax({
            url: `/api/v1/report/update/report_split/${id}`,
            type: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify({
                report_title: $('#report_title_split').val(),
                report_desc: $('#report_desc_split').val(),
                report_category: $('#report_category_split').val(),
                is_reminder:0,
                list_id:item_id
            }),
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
            },
            success: function(response) {
                Swal.close()
                Swal.fire({
                    title: "Success!",
                    text: response.message,
                    icon: "success"
                }).then((result) => {
                    if (result.isConfirmed) {
                        get_detail_report(id)
                    }
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.hideLoading()
                Swal.fire({
                    title: "Oops!",
                    text: "Failed to update the report",
                    icon: "error"
                });
            }
        });
    }

    $(document).on('change','.check-inventory', function(){
        const report_action_holder = '#report_check_action'
        let checkedItems = []

        $('.check-inventory').each(function() {
            if ($(this).is(':checked')) {
                const item_split = $(this).val().split('_')
                checkedItems.push({
                    id: item_split[0],
                    item_name: item_split[1]
                })
            }
        });
        
        if(checkedItems.length > 0){
            let selected_item_name = ''
            let selected_item_id = ''
            checkedItems.forEach((el,idx) => {
                selected_item_name += `<a class='fst-italic fw-bold bg-primary rounded px-2 py-1 mx-1 mb-1'>${el.item_name}</a>`
                if(idx < checkedItems.length - 1){
                    selected_item_id += `${el.id},`
                } else {
                    selected_item_id += `${el.id}`
                }
            });
            $(report_action_holder).html(`
                <div class='container bordered row'>
                    <div class='col'>
                        <h2 class='text-primary fw-bold' style='font-size:calc(var(--textXLG)*2);'>${checkedItems.length} Items</h2>
                        <h5 class='fw-bold' style='font-size:var(--textXLG);'>Selected</h5>
                        <hr class='mt-3 mb-2'>
                        ${selected_item_name}
                    </div>
                    <div class='col text-end'>
                        <h5 class='fw-bold my-4' style='font-size:var(--textXLG);'>What you want to do?</h5>
                        <a class='btn btn-primary me-2' href="/doc/report/{{$id}}/custom?filter_in=${selected_item_id}"><i class="fa-solid fa-pen-to-square" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Custom Print @endif</a>
                        <a class='btn btn-primary me-2' data-bs-toggle="modal" data-bs-target="#modalAddReport" onclick="get_dictionary()"><i class="fa-solid fa-arrows-split-up-and-left" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Split Report @endif</a>
                        <div class="modal fade" id="modalAddReport" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h2 class="modal-title fw-bold" id="exampleModalLabel">Add Report</h2>
                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                                    </div>
                                    <div class="modal-body">
                                        <form id='add-report-form'>
                                            @csrf
                                            <div class="row text-start">
                                                <div class="col">
                                                    <label>Title</label>
                                                    <input name="report_title" class="form-control" type="text" id="report_title_split" required>

                                                    <label>Description</label>
                                                    <textarea name="report_desc" id="report_desc_split" class="form-control"></textarea>

                                                    <label>Category</label>
                                                    <select class="form-select" name="report_category"  id="report_category_split" aria-label="Default select example"></select>
                                                    <a class='btn btn-success w-100 mt-4' onclick="split_report('<?= $id ?>','${selected_item_id}')"><i class="fa-solid fa-floppy-disk"></i> Save Changes</a>
                                                </div>
                                                <div class="col">
                                                    <h5 class='mb-3'>List Selected Item</h5>
                                                    ${selected_item_name}
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <a class='btn btn-primary me-2'><i class="fa-solid fa-print" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Print Detail @endif</a>
                        <a class='btn btn-primary me-2'><i class="fa-solid fa-chart-simple" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Analyze @endif</a>
                        <a class='btn btn-danger mt-2' data-bs-toggle="modal" data-bs-target="#modalDeleteManyItem"><i class="fa-solid fa-trash" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Remove @endif</a>
                        <div class="modal fade" id="modalDeleteManyItem" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h2 class="modal-title fw-bold" id="exampleModalLabel">Delete</h2>
                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                                    </div>
                                    <div class="modal-body">
                                        <h2>Remove this item ${selected_item_name} from report "${report_title}"?</h2>
                                        <a class="btn btn-danger mt-4" onclick="delete_item('${selected_item_id}')">Yes, Delete</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `)
        } else {
            $(report_action_holder).empty()
        }
    })
</script>