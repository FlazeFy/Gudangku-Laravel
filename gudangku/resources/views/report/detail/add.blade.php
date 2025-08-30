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
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fw-bold" id="exampleModalLabel">Add Report</h2>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <form id="report-item-form">
                    @csrf
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                            <label>Item</label>
                            <select class="form-select" id="report_item" onchange="browse_item(this.value)" aria-label="Default select example"></select>
                            <div id="item_form"></div>
                            <hr>
                            <label>Upload Shopping Bills</label>
                            <input class="form-control" type="file" id="file" name="file" accept='.png, .jpg, .jpeg, .pdf, .csv'>
                            <a class="btn btn-success mt-4 w-100" onclick="post_report_item('<?= $id ?>')"><i class="fa-solid fa-floppy-disk"></i> Save</a>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                            <h5>List Selected Item</h5>
                            <div id="item_holder">
                                <div class="alert alert-danger w-100 mt-4"><i class="fa-solid fa-triangle-exclamation"></i> No item selected</div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const clean_alert_item = () => {
        if ($('#item_holder').find('div.alert').length) {
            $('#item_holder').empty()
        } 
    }
    const post_report_item = (id) => {
        Swal.showLoading()
        let report_items = []

        $('#item_holder').children(':not(.alert)').each(function () {
            report_items.push({
                'inventory_id': $(this).find('input[name="inventory_id[]"]').val() ?? null,
                'item_name': $(this).find('input[name="item_name[]"]').val(),
                'item_desc': $(this).find('input[name="item_desc[]"]').val() ?? null,
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
                    xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
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
                            $('#item_holder').html('<div class="alert alert-danger w-100 mt-4"><i class="fa-solid fa-triangle-exclamation"></i> No item selected</div>')
                            get_detail_report('{{$id}}')
                        } 
                    });
                },
                error: function(response, jqXHR, textStatus, errorThrown) {
                    Swal.hideLoading()
                    Swal.fire({
                        title: "Oops!",
                        text: "Something error! Please call admin",
                        icon: "error"
                    });
                }
            })
        } else {
            Swal.fire({
                title: "Oops!",
                text: "You must select at least one item",
                icon: "error"
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
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>");
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
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
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
                Swal.close()
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
        $('#item_form').append(`<a class="btn btn-success mt-3 w-100" onclick='add_item("${val}")'><i class="fa-solid fa-plus"></i> Add Item</a><hr>`)
    }
    const add_item = (val) => {
        clean_alert_item()
        let itemExists = false
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
                priceInput = `
                    <div class="col">
                        <label>Price (optional)</label>
                        <input type="number" class="form-control w-100" min="0" name="item_price[]" value="0">
                    </div>
                `
            }

            if(val == 'add_ext'){
                if($('#item_name').val() != ''){
                    $('#item_holder').append(`
                        <div class="container-light mt-3 item-holder-div">
                            <input hidden name="item_name[]" value="${$('#item_name').val()}">
                            <div class="d-flex justify-content-between">
                                <span class="item_name_selected">${$('#item_name').val()}</span>
                                <a class="btn btn-danger delete-item"><i class="fa-solid fa-trash"></i> Remove</a>
                            </div>
                            <div class="my-2">
                                <label>Notes</label>
                                <textarea class="form-control" name="item_desc[]" style="height: 100px">${$('#item_desc').val()}</textarea>
                            </div>
                            <div class="row extra-form">
                                <div class="col-4">
                                    <label>Qty</label>
                                    <input class="item_qty_selected form-control w-100" name="item_qty[]" type="number" min="1" value="${$('#item_qty').val()}">
                                </div>
                                ${priceInput}
                            </div>
                        </div>
                    `)
                }
            } else if(val == 'copy_report') {
                const items_list = $('#temp_items_report').val().split(", ")

                items_list.forEach(el => {
                    $('#item_holder').append(`
                        <div class="container-light mt-3 item-holder-div">
                            <input hidden name="item_name[]" value="${el}">
                            <div class="d-flex justify-content-between">
                                <span class="item_name_selected">${el}</span>
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
                                ${priceInput}
                            </div>
                        </div>
                    `)
                });
            } else {
                $('#item_holder').append(`
                    <div class="container-light mt-3 item-holder-div">
                        <input hidden name="item_name[]" value="${val}">
                        <div class="d-flex justify-content-between">
                            <span class="item_name_selected">${val}</span>
                            <a class="btn btn-danger delete-item"><i class="fa-solid fa-trash"></i> Remove</a>
                        </div>
                        <div class="my-2">
                            <label>Notes</label>
                            <textarea class="form-control" name="item_desc[]" style="height: 100px">${$('#item_desc').val()}</textarea>
                        </div>
                        <div class="row extra-form">
                            <div class="col-4">
                                <label>Qty</label>
                                <input class="item_qty_selected form-control w-100" name="item_qty[]" type="number" min="1" value="${$('#item_qty').val()}">
                            </div>
                            ${priceInput}
                        </div>
                    </div>
                `)
            }
        }

        $('#item_name').val('')
        $('#item_qty').val(1)
        $('#item_desc').val('')
    }

    $( document ).ready(function() {
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
