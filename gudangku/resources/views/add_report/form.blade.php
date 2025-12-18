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
        font-size: var(--textXMD);
        margin-bottom: var(--spaceXXSM) !important;
    }
    table.table-report tr td {
        padding: var(--spaceMD) var(--spaceXSM)  !important;
    }
    table.table-report tbody span, table.table-report tbody input, table.table-report tbody textarea {
        font-size: var(--textMD) !important;
    }
    table.table-report tbody input, table.table-report tbody textarea {
        padding: var(--spaceXSM) !important;
    }
    @media (max-width: 575.98px) {
        .table-report {
            table-layout: fixed; 
            min-width: 500px;
        }
    }
</style>

<div class="container-form">
    <form id="add_report">
        <h2 class="mt-3">Report Detail</h2><hr>
        <div class="row">
            <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12 col-12">
                <label>Title</label>
                <input name="report_title" class="form-control" type="text" id="report_title" required>
                <label>Description</label>
                <textarea name="report_desc" id="report_desc" class="form-control"></textarea>
                <div class="row">
                    <div class="col-md-6 col-sm-6 col-12">
                        <label>Category</label>
                        <select class="form-select" name="report_category"  id="report_category" aria-label="Default select example">
                            @foreach($dct_cat as $dct)
                                <option value="{{$dct['dictionary_name']}}">{{$dct['dictionary_name']}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-sm-6 col-12">
                        <label>Created At</label>
                        <input type="datetime-local" name="created_at" id="created_at" class="form-control form-validated"/>
                    </div>
                </div>
                <hr>
                <label>Item</label>
                <select class="form-select" id="report_item" onchange="browse_item(this.value)" aria-label="Default select example"></select>
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
                <div class="d-grid d-md-inline-block">
                    <a class="btn btn-success mt-4 w-100 w-md-auto mb-2" onclick="post_report()"><i class="fa-solid fa-floppy-disk"></i> Save Report</a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    setCurrentLocalDateTime('created_at')

    const clean_alert_item = () => {
        if ($('#item_holder').find('div.alert').length) {
            $('#item_holder').empty()
        } 
    }

    const get_list_inventory = () => {
        $.ajax({
            url: "/api/v1/inventory/list",
            datatype: "json",
            type: "get",
            beforeSend: function (xhr) {
                Swal.showLoading()
                xhr.setRequestHeader("Accept", "application/json");
                xhr.setRequestHeader("Authorization", `Bearer ${token}`);
            },
            success: function(response) {
                Swal.close()
                let data =  response.data
                $('#report_item').append(`<option selected>- Browse Inventory -</option>`)

                for (var i = 0; i < data.length; i++) {
                    let optionText = `${data[i]['inventory_name']}` +
                        (data[i]['inventory_vol'] != null ? ` @${data[i]['inventory_vol']} ${data[i]['inventory_unit']}` : '');
                    $('#report_item').append(`<option value='${JSON.stringify(data[i])}'>${optionText}</option>`);
                }

                $('#report_item').append(`<option value="add_ext">- Add External Item -</option>`)
                $('#report_item').append(`<option value="copy_report">- Copy From Report -</option>`)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                $('#report_item').append(`<option selected>- Browse Inventory -</option>`)
                $('#report_item').append(`<option value="add_ext">- Add External Item -</option>`)
                $('#report_item').append(`<option value="copy_report">- Copy From Report -</option>`)
                generate_api_error(response, true)
            }
        })
    }
    get_list_inventory()

    const post_report = () => {
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
            Swal.fire({
                title: "Warning!",
                text: 'Please select at least one inventory item.',
                icon: "warning",
            })
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
                report_category: $('#report_category').val(),
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
                        Swal.close()
                        get_list_inventory()
                    }
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                generate_api_error(response, true)
            }
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
                        <tr class="item-holder-div bill-item align-middle">
                            <td>
                                <input hidden name="item_name[]" value="${el.item_name ?? ''}">
                                <p class="item_name_selected">${el.item_name ?? ''}</p>
                                <textarea class="form-control" name="item_desc[]"></textarea>
                            </td>
                            <td><input class="item_qty_selected form-control w-100" name="item_qty[]" type="number" min="1" value="1"></td>
                            <td><input type="number" class="form-control w-100" min="0" name="item_price[]" value="${el.item_price ?? ''}"></td>
                            <td><a class="btn btn-danger delete-item" style="font-size:var(--textMD);"><i class="fa-solid fa-trash"></i></a></td>
                        </tr>
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

    const browse_item = (val) => {
        let inventory_id = null
        if(val == 'add_ext'){
            $('#item_form').empty().append(`
                <div class="row">
                    <div class="col-lg-8">
                        <label>Item Name</label>
                        <input class="form-control" type="text" id="item_name">
                    </div>
                    <div class="col-lg-4">
                        <label>Qty</label>
                        <input class="form-control" type="number" id="item_qty" value="1" min="1">
                    </div>
                </div>
                <label>Description</label>
                <textarea id="item_desc" class="form-control"></textarea>
            `)
        } else if(val == 'copy_report'){
            $('#item_form').empty().append(`
                <label>Report Title</label><br>
                <div class="autocomplete" style="width:300px;">
                    <input id="report_title_template" class="form-control w-100" type="text">
                </div>
                <input id="temp_items_report" hidden>
            `)
            $(document).ready(function() {
                autocomplete(document.getElementById("report_title_template"), warehouse)
            });
        } else {
            val = JSON.parse(val)
            inventory_id = val['id']
            val = val['inventory_name']

            $('#item_form').empty().append(`
                <div class="row">
                    <div class="col-lg-8">
                        <label>Description</label>
                        <textarea id="item_desc" class="form-control"></textarea>
                    </div>
                    <div class="col-lg-4">
                        <label>Qty</label>
                        <input class="form-control" type="number" id="item_qty" value="1" min="1">
                    </div>
                </div>
            `)
        }
        $('#item_form').append(`<a class="btn btn-success mt-3 w-100" onclick='add_item("${val}","${inventory_id}")'><i class="fa-solid fa-plus"></i> Add Item</a>`)
    }
    const add_item = (val, inventory_id) => {
        clean_alert_item()

        let itemExists = false
        const isPriceCategory = $("#report_category").val() === 'Shopping Cart' || $("#report_category").val() === 'Wishlist'

        $('.item_name_selected').each(function(index) {
            if ($(this).text() == val || $(this).text() == $('#item_name').val()) {
                $('.item_qty_selected').eq(index).val(parseInt($('.item_qty_selected').eq(index).val()) + 1)
                itemExists = true
                return false
            }
        });

        if(!itemExists){
            let priceInput = ''
            if($('#report_category').val() == 'Shopping Cart' || $('#report_category').val() == 'Wishlist'){
                priceInput = `<input type="number" class="form-control w-100" min="0" name="item_price[]" value="0">`
            }

            if(val == 'add_ext'){
                if($('#item_name').val() != ''){
                    $('#item_holder').append(`
                        <tr class="item-holder-div align-middle">
                            <td>
                                <input hidden name="item_name[]" value="${$('#item_name').val()}">
                                <span class="item_name_selected">${$('#item_name').val()}</span>
                                <textarea class="form-control" name="item_desc[]">${$('#item_desc').val()}</textarea>
                            </td>
                            <td><input class="item_qty_selected form-control w-100" name="item_qty[]" type="number" min="1" value="${$('#item_qty').val()}"></td>
                            ${isPriceCategory ? `<td class="td-price">${priceInput}</td>`:''}
                            <td><a class="btn btn-danger delete-item" style="font-size:var(--textMD);"><i class="fa-solid fa-trash"></i></a></td>
                        </tr>
                    `)
                }
            } else if(val == 'copy_report') {
                const items_list = $('#temp_items_report').val().split(", ")

                items_list.forEach(el => {
                    $('#item_holder').append(`
                        <tr class="item-holder-div align-middle">
                            <td>
                                <input hidden name="item_name[]" value="${el}">
                                <p class="item_name_selected">${el}</p>
                                <textarea class="form-control" name="item_desc[]"></textarea>
                            </td>
                            <td><input class="item_qty_selected form-control w-100" name="item_qty[]" type="number" min="1" value="1"></td>
                            ${isPriceCategory ? `<td class="td-price">${priceInput}</td>`:''}
                            <td><a class="btn btn-danger delete-item" style="font-size:var(--textMD);"><i class="fa-solid fa-trash"></i></a></td>
                        </tr>
                    `)
                });
            } else {
                $('#item_holder').append(`
                    <tr class="item-holder-div align-middle">
                        <td>
                            <input hidden name="item_name[]" value="${val}">
                            <input hidden name="inventory_id[]" value="${inventory_id}">
                            <p class="item_name_selected">${val}</p>
                            <textarea class="form-control" name="item_desc[]">${$('#item_desc').val()}</textarea>
                        </td>
                        <td><input class="item_qty_selected form-control w-100" name="item_qty[]" type="number" min="1" value="${$('#item_qty').val()}"></td>
                        ${isPriceCategory ? `<td class="td-price">${priceInput}</td>`:''}
                        <td><a class="btn btn-danger delete-item" style="font-size:var(--textMD);"><i class="fa-solid fa-trash"></i></a></td>
                    </tr>
                `)
            }
        }

        $('#item_name').val('')
        $('#item_qty').val(1)
        $('#item_desc').val('')
    }

    $(document).ready(function() {
        $('#report_category').on('change', function () {
            const isPriceCategory = $(this).val() === 'Shopping Cart' || $(this).val() === 'Wishlist'
            const $table = $('.table-report')
            const $theadRow = $table.find('thead tr')
            const $tbody = $table.find('tbody')

            if(isPriceCategory){
                $('#price_th-holder').length === 0 &&
                $theadRow.children('th').eq(-1).before('<th id="price_th-holder" style="width:140px">Price</th>'),

                $tbody.find('tr').each(function () {
                    const $row = $(this)
                    const $cells = $row.children('td')

                    if ($cells.length === 1 && $cells.attr('colspan')) {
                        $cells.attr('colspan', 4)
                        return
                    }

                    $row.find('td.td-price').length === 0 &&
                    $cells.eq(-1).before(`
                        <td class="td-price">
                            <input type="number" class="form-control w-100" min="0" name="item_price[]" value="0">
                        </td>
                    `)
                })
            } else {
                $('#price_th-holder').remove(),
                $tbody.find('td.td-price').remove(),
                $tbody.find('td[colspan]').attr('colspan', 3)
            }
        })

        $(document).on('click', '.delete-item', function() {
            $(this).closest('.item-holder-div').remove()

            if($('.item-holder-div').length == 0){
                $('#item_holder').append(`<div class="alert alert-danger w-100 mt-4"><i class="fa-solid fa-triangle-exclamation"></i> No item selected</div>`)
            }
        })
    })

    const autocomplete = (inp, arr) => {
        var currentFocus

        inp.addEventListener("input", function(e) {
            var a, b, i, val = this.value

            closeAllLists()
            if (!val) { return false }
            currentFocus = -1

            a = document.createElement("DIV")
            a.setAttribute("id", this.id + "autocomplete-list")
            a.setAttribute("class", "autocomplete-items")
            this.parentNode.appendChild(a)

            for (i = 0; i < arr.length; i++) {
                if (arr[i]['title'].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
                    b = document.createElement("DIV")
                    b.innerHTML = "<strong>" + arr[i]['title'].substr(0, val.length) + "</strong>"
                    b.innerHTML += arr[i]['title'].substr(val.length)
                    b.innerHTML += "<input type='hidden' value='" + arr[i]['title'] + "'>"
                    const items = arr[i]['items']

                    b.addEventListener("click", function(e) {
                        inp.value = this.getElementsByTagName("input")[0].value
                        closeAllLists()
                        $('#temp_items_report').val(items)
                    })
                    a.appendChild(b)
                }
            }
        })

        inp.addEventListener("keydown", function(e) {
            var x = document.getElementById(this.id + "autocomplete-list")
            if (x) x = x.getElementsByTagName("div")
            if (e.keyCode == 40) {
                currentFocus++
                addActive(x)
            } else if (e.keyCode == 38) {
                currentFocus--
                addActive(x)
            } else if (e.keyCode == 13) {
                e.preventDefault()
                if (currentFocus > -1) {
                    if (x) x[currentFocus].click()
                }
            }
        })
        const addActive = (x) => {
            if (!x) return false
            removeActive(x)
            if (currentFocus >= x.length) currentFocus = 0
            if (currentFocus < 0) currentFocus = (x.length - 1)
            x[currentFocus].classList.add("autocomplete-active")
        }
        const removeActive = (x) => {
            for (var i = 0; i < x.length; i++) {
                x[i].classList.remove("autocomplete-active")
            }
        }
        const closeAllLists = (elmnt) => {
            var x = document.getElementsByClassName("autocomplete-items")
            for (var i = 0; i < x.length; i++) {
                if (elmnt != x[i] && elmnt != inp) {
                    x[i].parentNode.removeChild(x[i])
                }
            }
        }
        document.addEventListener("click", function (e) {
            closeAllLists(e.target)
        })
    }
</script>
