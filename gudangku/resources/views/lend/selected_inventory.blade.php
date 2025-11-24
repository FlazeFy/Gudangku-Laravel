<a class="btn btn-primary mb-3 me-2" id="selected-inventory-modal-button" data-bs-target="#modalBorrowInventory" data-bs-toggle="modal"><i class="fa-solid fa-cart-shopping" style="font-size:var(--textXLG);"></i> <b id="total-item-selected">0</b> @if(!$isMobile) Item Selected @endif</a>
<div class="modal fade" id="modalBorrowInventory" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="exampleModalLabel">Borrow From <span class="inventory-owner"></span></h5>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div id="selected-inventory-holder"></div><hr>
                <label>Borrower Name</label>
                <input class="form-control" id="borrower_name">
                <a class="btn btn-success mt-4 w-100" id="save-borrow-button"><i class="fa-solid fa-floppy-disk"></i> Save</a>
                <div id="reset-button-holder"></div>
            </div>
        </div>
    </div>
</div>

<script>
    const post_borrow = (lend_id) => {
        const selected = get_selected_items()
        const borrower_name = $('#borrower_name').val()

        if (selected.length == 0) {
            Swal.fire({
                title: "Warning!",
                text: 'Please select at least one inventory item.',
                icon: "warning",
            })
            return
        }
        if (borrower_name == "") {
            Swal.fire({
                title: "Failed!",
                text: 'Please provide borrower name',
                icon: "error",
            })
            return
        }

        const inventory_list_id = selected.map(el => el.id)

        $.ajax({
            url: `/api/v1/lend/inventory/${lend_id}`,
            type: 'POST',
            data: {
                lend_id : lend_id,
                inventory_list : inventory_list_id,
                borrower_name : borrower_name,
            },
            dataType: 'json',
            beforeSend: function (xhr) {
                Swal.showLoading()
                xhr.setRequestHeader("Accept", "application/json");
                xhr.setRequestHeader("Authorization", `Bearer ${token}`);    
            },
            success: function(response) {
                Swal.close()
                Swal.fire({
                    title: "Success!",
                    text: response.message,
                    icon: "success"
                }).then((result) => {
                    if (result.isConfirmed && response.data) {
                        window.open(response.data, '_blank')

                        const link = document.createElement('a')
                        link.href = response.data
                        link.download = ''
                        document.body.appendChild(link)
                        link.click()
                        document.body.removeChild(link)
                    }

                    window.location.href = '/'
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()

                if (response.status === 422 || (response.status === 400 && response.responseJSON && response.responseJSON.is_expired)) {
                    Swal.fire({
                        title: "Failed!",
                        text: response.responseJSON.message,
                        icon: "error"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '/'
                        }
                    });
                } else {
                    generate_api_error(response, true);
                }
            }
        });
    }

    const get_selected_inventory_modal = () => {
        const selected = get_selected_items()
        $('#selected-inventory-holder').empty()

        if(selected.length > 0){
            $('#reset-button-holder').html(`
                <a class="btn btn-danger mt-3 w-100" id="reset-borrow-button"><i class="fa-solid fa-rotate-left"></i> Reset All</a>
            `)
            selected.forEach(el => {
                $('#selected-inventory-holder').append(`
                    <button class="btn-feature mb-2 text-start" style="padding:var(--spaceXMD) !important;">
                        <h2 style="font-size:var(--textXLG);">${el.inventory_name}</h2>
                        <div class="mt-2 d-flex text-start props-box">
                            <span style="background: var(--successBG);" class="py-1 px-2 me-1 rounded d-inline-flex align-items-center">${el.inventory_category}</span>
                            <span style="background: var(--primaryColor);" class="py-1 px-2 me-1 rounded d-inline-flex align-items-center">${el.inventory_room}</span>
                        </div>
                    </button>
                `)
            });        
        } else {
            $('#selected-inventory-holder').html(`<p class="text-white text-center">- No Items Selected -</p>`)
        }
    }

    $(document).on('click','#save-borrow-button', function() {
        post_borrow(lend_id)
    })
    $(document).on('click','#selected-inventory-modal-button',function(){
        get_selected_inventory_modal()
    })
    $(document).on('click','#reset-borrow-button',function(){
        localStorage.removeItem(SELECTED_STORAGE_KEY)
        $('#reset-button-holder').empty()
        $('.inventory-item').css('border-color', 'var(--primaryColor)')
        get_selected_inventory_modal()
        get_cart_button()
    })
</script>