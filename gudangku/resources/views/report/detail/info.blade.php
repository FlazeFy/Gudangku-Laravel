<div id="report_holder"></div>
<div id="report_item_holder">
    <table class="table mt-3" id="report_item_tb">
        <thead></thead>
        <tbody></tbody>
    </table>
</div>

<script>
    const is_edit_mode = <?= session()->get('toogle_edit_report') ?>;

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
            let select_cat_el = ''

            $('#created_at').text(getDateToContext(data.created_at,'calendar'))
            $('#updated_at').text(data.updated_at ? getDateToContext(data.updated_at,'calendar') : '-')
            list_cat.forEach(el => {
                select_cat_el += `<option value='${el}' ${el == data.report_category && 'selected'}>${el}</option>`
            });

            if(data_item.length > 0){
                $(`#btn-doc-preview-holder`).html(`<a class="btn btn-primary mb-3 me-2" href="/doc/report/${data.id}"><i class="fa-solid fa-print" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Preview Document @endif</a>`)
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
                        <th scope="col">Delete</th>
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
                        <th scope="col">Delete</th>
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
                                <input class="form-check-input" type="checkbox" value="">
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
</script>