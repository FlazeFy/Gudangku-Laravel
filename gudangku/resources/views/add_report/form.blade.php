<div class="container-form">
    <form id="add_report">
        <h2>Report Detail</h2><hr>
        <div class="row">
            <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12 col-12">
                <label>Title</label>
                <input name="report_title" class="form-control" type="text" id="report_title" required>
                <label>Description</label>
                <textarea name="report_desc" id="report_desc" class="form-control"></textarea>
                <div class="row">
                    <div class="col-md-6 col-sm-6 col-12">
                        <label>Category</label>
                        <select class="form-select" name="report_category"  id="report_category_holder" aria-label="Default select example"></select>
                    </div>
                    <div class="col-md-6 col-sm-6 col-12">
                        <label>Created At</label>
                        <input type="datetime-local" name="created_at" id="created_at" class="form-control form-validated"/>
                    </div>
                </div>
                <hr>
                <label>Item</label>
                <select class="form-select" id="report_item" onchange="browseItem(this.value)" aria-label="Default select example"></select>
                <div id="item_form"></div>
                <hr>
                <label>Upload Shopping Bills</label>
                <input class="form-control" type="file" id="file" name="file" accept='.png, .jpg, .jpeg, .pdf, .csv'>
            </div>
            <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12 col-12">
                <label>List Selected Item</label>
                <div class="table-responsive">
                    <table class="table table-report">
                        <thead>
                            <tr>
                                <th style="min-width: 200px">Name & Description</th>
                                <th style="min-width: 60px">Qty</th>
                                <th style="min-width: 80px">Delete</th>
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
                <div class="d-grid d-md-inline-block">
                    <a class="btn btn-success mt-4 w-100 w-md-auto mb-2" onclick="postReport()"><i class="fa-solid fa-floppy-disk"></i> Save Report</a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    setCurrentLocalDateTime('created_at')
    $(async function () {
        await getDictionaryByContext('report_category',token)
    })

    const getAllInventory = () => {
        $.ajax({
            url: "/api/v1/inventory/list",
            datatype: "json",
            type: "get",
            beforeSend: function (xhr) {
                Swal.showLoading()
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)
            },
            success: function(response) {
                Swal.close()
                let data =  response.data
                $('#report_item').append(`<option selected value="no_item">- No Item Selected -</option>`)

                for (var i = 0; i < data.length; i++) {
                    let optionText = `${data[i]['inventory_name']}` + (data[i]['inventory_vol'] != null ? ` @${data[i]['inventory_vol']} ${data[i]['inventory_unit']}` : '')
                    $('#report_item').append(`<option value='${JSON.stringify(data[i])}'>${optionText}</option>`)
                }

                $('#report_item').append(`<option value="add_ext">- Add External Item -</option>`)
                $('#report_item').append(`<option value="copy_report">- Copy From Report -</option>`)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                $('#report_item').append(`<option selected value="no_item">- No Item Selected -</option>`)
                $('#report_item').append(`<option value="add_ext">- Add External Item -</option>`)
                $('#report_item').append(`<option value="copy_report">- Copy From Report -</option>`)
                if(response.status !== 404){
                    generateAPIError(response, true)
                } else {
                    $('.container-form').has('#add_report').before(`<div id="no_inventory-holder" class="mb-3"></div>`)
                    templateAlertContainer('no_inventory-holder', 'no-data', "You have no inventory found to select", null, '<i class="fa-solid fa-warehouse"></i>')
                }
            }
        })
    }
    getAllInventory()

    const postReport = () => {
        const reportCategory = $('#report_category_holder').val()
        if (reportCategory === "-"){
            failedMessage('create report. You must select report category')
            return
        }

        const report_items = []

        $('.item-holder-div').each(function () {
            const inventory_id = $(this).find('input[name="inventory_id[]"]').val()
            const item_name = $(this).find('input[name="item_name[]"]').val()
            const item_desc = $(this).find('textarea[name="item_desc[]"]').val()
            const item_qty = parseInt($(this).find('input[name="item_qty[]"]').val()) || 0
            const item_price = parseInt($(this).find('input[name="item_price[]"]').val()) || 0

            if (item_name && item_qty > 0) {
                report_items.push({
                    inventory_id: inventory_id,
                    item_name: item_name,
                    item_desc: item_desc,
                    item_qty: item_qty,
                    item_price: item_price
                })
            }
        })

        if (report_items.length == 0) {
            Swal.fire("Oops!", "You must select at least one item", "warning")
            return
        }

        $.ajax({
            url: `/api/v1/report`,
            type: 'POST',
            beforeSend: function (xhr) {
                Swal.showLoading()
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
            },
            data: {
                report_title: $('#report_title').val(),
                report_desc: $('#report_desc').val().trim() !== "" ? $('#report_desc').val() : null,
                report_category: reportCategory,
                created_at: tidyUpDateTimeFormat($('#created_at').val()),
                report_item: JSON.stringify(report_items),
                file: null, 
                is_reminder: 0,
            },
            dataType:'json',
            success: function(response) {
                Swal.fire({
                    title: "Success!",
                    text: response.message,
                    icon: "success",
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        const data = response.data
                        window.location.href= `/report/detail/${data.id}`
                    }
                })
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                generateAPIError(response, true)
            }
        })
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

                cleanAlertItem()
                data.forEach(el => {
                    $('#item_holder').append(`
                        <tr class="item-holder-div bill-item align-middle">
                            <td>
                                <input hidden name="item_name[]" value="${el.item_name ?? ''}">
                                <p class="item_name_selected">${el.item_name ?? ''}</p>
                                <textarea class="form-control" name="item_desc[]"></textarea>
                            </td>
                            <td><input class="item_qty_selected form-control w-100" name="item_qty[]" type="number" min="1" value="1"></td>
                            <td><input type="number" class="form-control w-100" min="0" name="item_price[]" value="${el.item_price ?? ''}"></td>
                            <td><a class="btn btn-danger delete-item" style="font-size:var(--textMD)"><i class="fa-solid fa-trash"></i></a></td>
                        </tr>
                    `)
                })
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                generateAPIError(response, true)
            }
        })
    }

    $(document).on('input','#file',function(){
        if($('.bill-item').length == 0){
            post_analyze_image()
        } else {
            Swal.fire("Are you sure!", "want to upload new bill? this will remove previous item!", "warning").then((result) => {
                if (result.isConfirmed) {
                    $('.bill-item').remove()
                    post_analyze_image()
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    Swal.fire("Cancelled!", "Your previous item is safe!", "success")
                }
            })
        }
    })

    $(document).ready(function() {
        $('#report_category_holder').on('change', function () {
            reportCategoryHolderEventHandler(this)
        })

        $(document).on('click', '.delete-item', function() {
            $(this).closest('.item-holder-div').remove()

            if($('.item-holder-div').length == 0){
                const trLen = $('#item_holder').closest('table').find('thead tr th').length
                $('#item_holder').append(`
                    <tr>
                        <td colspan="${trLen}">
                            <div class="alert alert-danger w-100"><i class="fa-solid fa-triangle-exclamation"></i> No item selected</div>
                        </td>
                    </tr>
                `)
            }
        })
    })
</script>
