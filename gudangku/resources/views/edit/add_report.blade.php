<style>
    .autocomplete {
        position: relative;
        display: inline-block;
    }
    .autocomplete-items {
        position: absolute;
        border: 2px solid white;
        z-index: 99;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--darkColor);
        border-radius: var(--roundedLG);
    }
    .autocomplete-items div {
        padding: var(--spaceMD);
        cursor: pointer;
        background: transparent;
        color: var(--whiteColor);
    }
    .autocomplete-items div:hover {
        background: var(--primaryColor);
    }
    .autocomplete-active {
        color: #ffffff;
    }
    .item_qty_selected {
        width: 80px;
    }
    .item_name_selected {
        font-weight: 500;
        font-size: var(--textJumbo);
        color: var(--whiteColor);
    }
</style>

<div class="modal fade" id="modalAddReport" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="exampleModalLabel">Add Report using item : <span id='inventory_name_title_add_report'></span></h5>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <input hidden id='inventory_id_add_report' name="inventory_id">
                <input hidden id='inventory_name_add_report' name="item_name">

                <label>Title</label>
                <input name="report_title" class="form-control" type="text" id="report_title" required>

                <label>Description</label>
                <textarea name="report_desc" id="report_desc" class="form-control"></textarea>

                <label>Category</label>
                <select class="form-select" name="report_category" id="report_category" aria-label="Default select example"></select>
                <hr>
                <label>Item Notes</label>
                <textarea class="form-control" name="item_desc" id="item_desc" style="height: 100px"></textarea>
                <div class="row">
                    <div class="col-sm-3 col-4">
                        <label>Qty</label>
                        <input class="item_qty_selected form-control w-100" name="item_qty" id="item_qty" type="number" min="1" value="1">
                    </div>
                    <div class="col-sm-9 col-8" id="item-extra-form">
                        <div id="item-price-holder">
                            <label>Price (optional)</label>
                            <input class="item_qty_selected form-control w-100" name="item_price" id="item_price" type="number" min="1">
                        </div>
                    </div>
                </div>
                <a class="btn btn-success mt-4 w-100" onclick="add_report()"><i class="fa-solid fa-floppy-disk"></i> Save</a>
            </div>
        </div>
    </div>
</div>

<script>
    $( document ).ready(function() {
        $('#report_category').on('change', function() {
            if($(this).val() == "Shopping Cart" || $(this).val() == "Wishlist"){
                priceInput = `
                    <label>Price (optional)</label>
                    <input type="number" class="form-control w-100" min="1" name="item_price" value="0">
                `
                $('#item-price-holder').html(priceInput)
            } else {
                $('#item-price-holder').empty()
            }
        })
    })

    const add_report = () => {
        const formData = new FormData()
        formData.append('report_title', $('#report_title').val())
        formData.append('report_desc', $('#report_desc').val())
        formData.append('report_category', $('#report_category').val())
        formData.append('is_reminder', 0)
        formData.append('file', null) 
        formData.append('report_item', JSON.stringify([
            {
                inventory_id: inventory_id,
                item_name: $('#inventory_name').val(),
                item_desc: $('#inventory_desc').val(),
                item_qty: $('#item_qty').val(),
                item_price: $('#item_price').val() ?? null
            },
        ]))

        $.ajax({
            url: `/api/v1/report`,
            type: 'POST',
            beforeSend: function (xhr) {
                Swal.showLoading()
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
            },
            data: formData,
            processData: false,
            contentType: false,
            dataType:'json',
            success: function(response) {
                $(`#modalAddReport`).modal('hide')
                Swal.fire({
                    title: "Success!",
                    text: response.message,
                    icon: "success",
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.close()
                        getDetailInventoryByID(inventory_id)
                    }
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                generateAPIError(response, true)
            }
        });
    }
</script>
