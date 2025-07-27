<div class="row">
    <div class="col-lg-6 col-md-12">
        <div id="qr-lend-holder"></div>
    </div>
    <div class="col-lg-6 col-md-12">
        <h5 class="fw-bold my-3" style="font-size:var(--textLG);">QR Code History</h5>
        <div id="qr-code-history"></div>
    </div>
</div>

<script>
    const get_my_qr = () => {
        Swal.showLoading()
        $.ajax({
            url: `/api/v1/lend/qr`,
            type: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
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
                if (response.status === 404) {
                    $('#qr-lend-holder').html(`
                        <div class="alert alert-success w-100 mt-4">
                            <i class="fa-solid fa-circle-info"></i> There's <b>no active</b> QR, and people can't see your inventory
                        </div>
                        <a class="btn btn-success mt-3" onclick="generate_qr()"><i class="fa-solid fa-qrcode"></i> Generate QR Code</a>
                    `)
                } else if (response.status === 400) {
                    $('#qr-lend-holder').html(`
                        <div class="alert alert-danger w-100 mt-4">
                            <i class="fa-solid fa-circle-info"></i> The last QR Code is already <b>expired</b>. Generate a new one?
                        </div>
                        <a class="btn btn-success mt-3" onclick="generate_qr()"><i class="fa-solid fa-qrcode"></i> Generate QR Code</a>
                    `)
                } else {
                    generate_api_error(response, true)
                }
            }
        });
    }
    get_my_qr()

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
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
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
                        get_my_qr()
                        get_qr_history()
                    }
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                generate_api_error(response, true)
            }
        });
    }

    const get_qr_history = () => {
        Swal.showLoading()
        $.ajax({
            url: `/api/v1/lend/qr/history`,
            type: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
            },
            success: function(response) {
                Swal.close()
                const data = response.data.data
                const current_page = response.data.current_page
                const total_page = response.data.last_page
                const total_item = response.data.total
                
                $('#qr-code-history').empty()
                data.forEach(el => {
                    $('#qr-code-history').append(`
                        <button class="report-box mt-1" onclick="window.location.href='/lend/detail/${el.id}'">
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <h2 style="font-weight:500; font-size:var(--textJumbo);">${ucFirst(el.lend_status)}</h2>
                                </div>
                                <div>
                                    <span class="bg-success text-white rounded-pill px-3 py-2 report-category">For ${el.qr_period} hours</span>
                                </div>
                            </div>
                            <p>${el.lend_desc ?? '<span class="fst-italic">- No Description Provided -</span>'}</p>
                            <h6 class='date-text mt-2'>Created At : ${getDateToContext(el.created_at,'calendar')}</h6>
                            ${ el.lend_status == 'used' ? `<hr class="mb-2"><b>Borrowed Item</b><p>${el.list_inventory}</p>` : ""}
                        </button>
                    `)
                });

                generate_pagination(item_holder, get_qr_history, total_page, current_page)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                if (response.status === 404) {
                    const json = JSON.parse(response.responseText);
                    const message = json.message
                    $('#qr-code-history').html(`<span class="fst-italic text-white">- ${ucFirst(message)} -</span>`);
                } else {
                    generate_api_error(response, true)
                }
            }
        });
    }
    get_qr_history()
</script>