<div class="row">
    <div class="col">
        <div id="qr-lend-holder">
            <div class="alert alert-success w-100 mt-4">
                <i class="fa-solid fa-circle-info"></i> There's <b>no active</b> QR, and people can't see your inventory
            </div>
            <a class="btn btn-success mt-3" onclick="generate_qr()"><i class="fa-solid fa-qrcode"></i> Generate QR Code</a>
        </div>
    </div>
    <div class="col">
        
    </div>
</div>

<script>
    const generate_qr = () => {
        $.ajax({
            url: '/api/v1/lend/qr',
            type: 'POST',
            data: {
                qr_period : 2
            },
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
                Swal.showLoading()
            },
            success: function(response) {
                const data = response.data

                $('#qr-lend-holder').html(`
                    <div class="alert alert-danger w-100 mt-4">
                        <img alt="${data.qr_code}" title="Lend QR Code" class="img img-fluid rounded shadow mx-auto d-block my-4" src="${data.qr_code}">
                        <i class="fa-solid fa-circle-info"></i> QR is <b>Active!</b> for <b>${data.qr_period} hours</b> from now, people with this QR can <b>see your inventory</b> list and ask for <b>permission to borrow</b> until <b>${data.lend_expired_datetime}</b>.<br>Be carefull to lend your items to strangers!
                    </div>
                `)
                Swal.close()
                Swal.fire({
                    title: 'Success!',
                    text: response.message,
                    icon: "success"
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                generate_api_error(response, true)
            }
        });
    }
</script>