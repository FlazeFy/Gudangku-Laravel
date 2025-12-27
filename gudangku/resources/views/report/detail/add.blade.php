<div class="modal fade" id="modalAddReport" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="exampleModalLabel">Add Report</h5>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                        <label>Item</label>
                        <select class="form-select" id="report_item" onchange="browse_item(this.value)" aria-label="Default select example"></select>
                        <div id="item_form"></div>
                        <hr>
                        <label>Upload Shopping Bills</label>
                        <input class="form-control" type="file" id="file" name="file" accept='.png, .jpg, .jpeg, .pdf, .csv'>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                        <label>List Selected Item</label>
                        <div class="table-responsive">
                            <table class="table table-report">
                                <thead>
                                    <tr>
                                        <th style="min-width: 200px;">Name & Description</th>
                                        <th style="width: 80px;">Qty</th>
                                        <th style="width: 140px;" id="price_th-holder">Price</th>
                                        <th style="width: 60px;">Delete</th>
                                    </tr>
                                </thead>
                                <tbody id="item_holder">
                                    <tr>
                                        <td colspan="4">
                                            <div class="alert alert-danger w-100"><i class="fa-solid fa-triangle-exclamation"></i> No item selected</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <a class="btn btn-success mt-4 w-100" onclick="post_report_item('<?= $id ?>')"><i class="fa-solid fa-floppy-disk"></i> Save</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const post_report_item = (id) => {
        Swal.showLoading()
        let report_items = []

        $('#item_holder').children('tr:not(:has(.alert))').each(function () {
            const item_desc = $(this).find('textarea[name="item_desc[]"]').val()
            report_items.push({
                'inventory_id': $(this).find('input[name="inventory_id[]"]').val() ?? null,
                'item_name': $(this).find('input[name="item_name[]"]').val(),
                'item_desc': item_desc && item_desc.trim() !== "" ? item_desc : null,
                'item_qty': $(this).find('input[name="item_qty[]"]').val() ?? 1,
                'item_price': $(this).find('input[name="item_price[]"]').val() ?? null,
            });
        });

        if(report_items.length > 0){
            $.ajax({
                url: `/api/v1/report/item/${id}`,
                dataType: 'json',
                contentType: 'application/json',
                type: "POST",
                data: JSON.stringify({
                    report_item: JSON.stringify(report_items),
                }), 
                beforeSend: function (xhr) {
                    xhr.setRequestHeader("Accept", "application/json")
                    xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
                },
                success: function(response) {
                    const data = response
                    Swal.hideLoading()
                    Swal.fire({
                        title: "Success!",
                        text: `${response.message}`,
                        icon: "success",
                        allowOutsideClick: false,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            closeModalBS()
                            $('#item_holder').html('<div class="alert alert-danger w-100 mt-4"><i class="fa-solid fa-triangle-exclamation"></i> No item selected</div>')
                            get_detail_report('{{$id}}')
                        } 
                    });
                },
                error: function(response, jqXHR, textStatus, errorThrown) {
                    generate_api_error(response, true)
                }
            })
        } else {
            Swal.fire({
                title: "Oops!",
                text: "You must select at least one item",
                icon: "warning"
            });
        }
    }
    const get_list_inventory = () => {
        $.ajax({
            url: "/api/v1/inventory/list",
            datatype: "json",
            type: "get",
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json");
                xhr.setRequestHeader("Authorization", `Bearer ${token}`);
            },
        })
        .done(function (response) {
            let data =  response.data
            $('#report_item').append(`<option selected>- Browse Inventory -</option>`)

            for (var i = 0; i < data.length; i++) {
                let optionText = `${data[i]['inventory_name']}` +
                    (data[i]['inventory_vol'] != null ? ` @${data[i]['inventory_vol']} ${data[i]['inventory_unit']}` : '');
                $('#report_item').append(`<option value='${JSON.stringify(data[i])}'>${optionText}</option>`);
            }

            $('#report_item').append(`<option value="add_ext">- Add External Item -</option>`)
            $('#report_item').append(`<option value="copy_report">- Copy From Report -</option>`)
        })
        .fail(function (jqXHR, ajaxOptions, thrownError) {
            // Do someting
        });   
    }

    const post_analyze_image = () => {
        const form = $('#report-form')[0]
        const formData = new FormData(form)
        $.ajax({
            url: '/api/v1/analyze/bill',
            type: 'POST',
            data: formData,
            processData: false, 
            contentType: false,
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
                Swal.showLoading()
            },
            success: function(response) {
                Swal.close()
                const data = response.data

                clean_alert_item()
                data.forEach(el => {
                    $('#item_holder').append(`
                        <div class="container-light mt-3 item-holder-div bill-item">
                            <input hidden name="item_name[]" value="${el.item_name ?? ''}">
                            <div class="d-flex justify-content-between">
                                <span class="item_name_selected">${el.item_name ?? ''}</span>
                                <a class="btn btn-danger delete-item"><i class="fa-solid fa-trash"></i> Remove</a>
                            </div>
                            <div class="my-2">
                                <label>Notes</label>
                                <textarea class="form-control" name="item_desc[]" style="height: 100px"></textarea>
                            </div>
                            <div class="row extra-form">
                                <div class="col-4">
                                    <label>Qty</label>
                                    <input class="item_qty_selected form-control w-100" name="item_qty[]" type="number" min="1" value="1">
                                </div>
                                <div class="col">
                                    <label>Price (optional)</label>
                                    <input type="number" class="form-control w-100" min="0" name="item_price[]" value="${el.item_price ?? ''}">
                                </div>
                            </div>
                        </div>
                    `)
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                generate_api_error(response, true)
            }
        });
    }

    $(document).on('input','#file',function(){
        if($('.bill-item').length == 0){
            post_analyze_image()
        } else {
            Swal.fire({
                title: "Are you sure!",
                text: "want to upload new bill? this will remove previous item!",
                icon: "warning"
            }).then((result) => {
                if (result.isConfirmed) {
                    $('.bill-item').remove()
                    post_analyze_image()
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    Swal.fire({
                        title: "Cancelled!",
                        text: "Your previous item is safe!",
                        icon: "success"
                    });
                }
            });
        }
    })

    $(document).ready(function() {
        get_list_inventory()

        $('#report_category').on('change', function() {
            if($(this).val() != "Shopping Cart" && $(this).val() != "Wishlist"){
                $('.extra-form').empty()
            }
        })
        $(document).on('click', '.delete-item', function() {
            $(this).closest('.item-holder-div').remove()

            if($('.item-holder-div').length == 0){
                $('#item_holder').append(`<div class="alert alert-danger w-100 mt-4"><i class="fa-solid fa-triangle-exclamation"></i> No item selected</div>`)
            }
        })
    })
</script>
