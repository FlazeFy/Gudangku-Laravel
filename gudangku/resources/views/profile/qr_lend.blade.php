<div class="row">
    <div class="col-lg-6 col-md-12">
        <div id="qr-lend-holder"></div>
    </div>
    <div class="col-lg-6 col-md-12">
        <h5 class="fw-bold my-3">QR Code History</h5>
        <div id="qr-code-history"></div>
    </div>
</div>

<script>
    const getAllQR = () => {
        Swal.showLoading()
        $.ajax({
            url: `/api/v1/lend/qr`,
            type: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
            },
            success: function(response) {
                Swal.close()
                const data = response.data
                
                $('#qr-lend-holder').html(`
                    <div class="alert alert-success w-100 mt-4">
                        <img alt="${data.lend_qr_url}" title="Lend QR Code" class="img img-fluid rounded shadow mx-auto d-block my-4" src="${data.lend_qr_url}">
                        <i class="fa-solid fa-circle-info"></i> QR is <b>Active!</b> for <b>${data.qr_period} hours</b> from now, people with this QR can <b>see your inventory</b> list and ask for <b>permission to borrow</b> until <b>${data.lend_expired_datetime}</b>.<br>Be carefull to lend your items to strangers!
                    </div>
                `)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                if (response.status === 404 || response.status === 400) {
                    $('#qr-lend-holder').html(`
                        <div class="alert alert-success w-100 mt-4">
                            <i class="fa-solid fa-circle-info"></i> ${response.status === 404 ? `There's <b>no active</b> QR, and people can't see your inventory`:`The last QR Code is already <b>expired</b>. Generate a new one?`}
                        </div>
                        <div class="d-grid d-md-inline-block">
                            <a class="btn btn-success mt-3 w-100 w-md-auto" onclick="generate_qr()"><i class="fa-solid fa-qrcode"></i> Generate QR Code</a>
                        </div>
                    `)
                } else if (response.status === 422) {
                    const json = JSON.parse(response.responseText)
                    const message = json.message

                    $('#qr-lend-holder').html(`
                        <div class="alert alert-danger w-100 mt-4">
                            <i class="fa-solid fa-circle-info"></i> ${message}
                        </div>
                    `)
                } else {
                    generateAPIError(response, true)
                }
            }
        })
    }
    getAllQR()

    const generate_qr = () => {
        $.ajax({
            url: '/api/v1/lend/qr',
            type: 'POST',
            data: {
                qr_period : 6
            },
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
                Swal.showLoading()
            },
            success: function(response) {
                const data = response.data

                Swal.fire({
                    title: "Success!",
                    text: response.message,
                    icon: "success",
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.close()
                        getAllQR()
                        get_qr_history()
                    }
                })
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                generateAPIError(response, true)
            }
        })
    }

    const get_qr_history = () => {
        Swal.showLoading()
        $.ajax({
            url: `/api/v1/lend/qr/history`,
            type: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
            },
            success: function(response) {
                Swal.close()
                const data = response.data.data
                const current_page = response.data.current_page
                const total_page = response.data.last_page
                const total_item = response.data.total
                
                $('#qr-code-history').empty()
                data.forEach(el => {
                    let inventory_holder = ''

                    if(el.lend_status == 'used'){
                        el.list_inventory_detail.forEach(inv => {
                            inventory_holder += `
                                <div class="form-check">
                                    <input type="hidden" name="inventory_id" value="${inv.id}">
                                    <input class="form-check-input" type="checkbox" name="is_returned" id="inv-${inv.id}" ${inv.returned_at ? "checked" : ""}>
                                    <label class="form-check-label" for="inv-${inv.id}">${inv.inventory_name} (${inv.inventory_category})</label>
                                </div>
                            `
                        })
                    }

                    $('#qr-code-history').append(`
                        <div class="report-box mt-1">
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <h2>${ucFirst(el.lend_status)}</h2>
                                </div>
                                <div>
                                    <span class="bg-success text-white rounded-pill px-3 py-2 report-category">For ${el.qr_period} hours</span>
                                </div>
                            </div>
                            <p>${el.lend_desc ?? '<span class="no-data-message">- No Description Provided -</span>'}</p>
                            <p class='date-text mt-2 mb-0'>Created At : ${getDateToContext(el.created_at,'calendar')}</p>
                            ${ 
                                el.lend_status == 'used' ? 
                                `<hr class="mb-2"><b>Borrowed Item</b><p class="mb-2">${el.list_inventory}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <p><b>Borrower by</b> ${data[0].borrower_name}</p>
                                    <a class="btn btn-success" style="padding: var(--spaceXXSM) var(--spaceMD) !important" data-bs-target="#modalDetail-${el.id}" data-bs-toggle="modal">Return Confirmation</a>
                                    <div class="modal fade" id="modalDetail-${el.id}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold" id="exampleModalLabel">Return Confirmation</h5>
                                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form id="form-${el.id}">
                                                        ${inventory_holder}
                                                        <a class='btn btn-success mt-4 w-100 update-returned-btn' data-lend-id="${el.id}" data-form-id="form-${el.id}"><i class="fa-solid fa-floppy-disk"></i> Update Returned Status</a>
                                                        <a class='btn btn-success mt-2 w-100'><i class="fa-solid fa-floppy-disk"></i> All Returned</a>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>` 
                                : ""
                            }
                        </div>
                    `)
                })

                generatePagination(item_holder, get_qr_history, total_page, current_page)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                if (response.status === 404) {
                    const json = JSON.parse(response.responseText)
                    const message = json.message
                    $('#qr-code-history').html(`<span class="fst-italic text-white">- ${ucFirst(message)} -</span>`)
                } else {
                    generateAPIError(response, true)
                }
            }
        })
    }
    get_qr_history()

    $(document).on('click', '.update-returned-btn', function () {
        const lend_id = $(this).data('lend-id')
        const form_id = $(this).data('form-id')
        const list_inventory = []

        $(`#${form_id} .form-check`).each(function () {
            const id = $(this).find("input[type=hidden][name=inventory_id]").val()
            const is_returned = $(this).find("input[type=checkbox][name=is_returned]").is(":checked")
            list_inventory.push({ id, is_returned })
        })
        update_returned_status(lend_id, list_inventory)
    })

    const update_returned_status = (lend_id, list_inventory) => {
        $.ajax({
            url: `/api/v1/lend/update_status/${lend_id}`,
            type: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify({ list_inventory }),
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
                Swal.showLoading()
            },
            success: function(response) {
                const data = response.data

                Swal.fire({
                    title: "Success!",
                    text: response.message,
                    icon: "success",
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.close()
                        getAllQR()
                        get_qr_history()
                    }
                })
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                generateAPIError(response, true)
            }
        })
    }
</script>