const template_alert_container = (target, type, msg, btn_title, icon, href) => {
    $(`#${target}`).html(`
        <div class="container-fluid p-3" style="${type == 'no-data'? 'background-color:rgba(59, 131, 246, 0.2);':''}">
            <h6>${icon} ${msg}</h6>
            ${btn_title != null ? `<a class="btn btn-primary mt-3" href=${href}><i class="${type == 'no-data'? 'fa-solid fa-plus':''}"></i> ${ucEachWord(btn_title)}</a>`:''}
        </div>
    `)
}

const get_context_opt = (context, token, selected = null) => {
    return new Promise((resolve, reject) => {
        Swal.showLoading()
        let ctx_holder

        if (context.includes(',')) {
            ctx_holder = []
            context = context.split(',')
            context.forEach(el => {
                ctx_holder.push(`${el}_holder`)
            })
        } else {
            ctx_holder = `${context}_holder`
        }

        const generate_context_list = (holder, data, selected = null) => {
            if (Array.isArray(holder)) {
                holder.forEach(dt => {
                    $(`#${dt}`).empty().append(`<option>-</option>`)
                    data.forEach(el => {
                        if (el.dictionary_type === dt.replace('_holder','').replace('_split','')) {
                            $(`#${dt}`).append(`<option value="${el.dictionary_name}" ${selected === el.dictionary_name ? "selected":""}>${el.dictionary_name}</option>`)
                        }
                    })
                })
            } else {
                $(`#${holder}`).empty().append(`<option>-</option>`)
                data.forEach(el => {
                    $(`#${holder}`).append(`<option value="${el.dictionary_name}" ${selected === el.dictionary_name ? "selected":""}>${el.dictionary_name}</option>`)
                })
            }

            resolve()
        }

        const fetchData = () => {
            $.ajax({
                url: `/api/v1/dictionary/type/${context}`,
                type: 'GET',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader("Accept", "application/json")
                },
                success: function (response) {
                    Swal.close()
                    const data = response.data

                    localStorage.setItem(ctx_holder, JSON.stringify(data))
                    localStorage.setItem(`last-hit-${ctx_holder}`, Date.now())

                    $(document).ready(function () {
                        generate_context_list(ctx_holder, data, selected)
                    })
                },
                error: function (response) {
                    Swal.close()
                    generateApiError(response, true)
                    reject(response)
                }
            })
        }

        if (ctx_holder in localStorage) {
            const lastHit = parseInt(localStorage.getItem(`last-hit-${ctx_holder}`))
            const now = Date.now()

            if (((now - lastHit) / 1000) < statsFetchRestTime) {
                const data = JSON.parse(localStorage.getItem(ctx_holder))

                if (data) {
                    Swal.close()
                    $(document).ready(function () {
                        generate_context_list(ctx_holder, data, selected)
                    })
                } else {
                    Swal.close()
                    failedMsg(`get the ${context} list`)
                    reject("No cached data")
                }
            } else {
                fetchData()
            }
        } else {
            fetchData()
        }
    })
}

const generate_floor_range = (data) => {
    let rawLetter = []
    let rawNum = [] 

    data.forEach(dt => {
        if (dt.layout) {
            const coor = dt.layout.split(':')
            coor.forEach(cr => {
                const match = cr.match(/^([A-Z]+)(\d+)$/)
                if (match) {
                    const [_, letters, numbers] = match
                    rawLetter.push(letters)
                    rawNum.push(numbers)
                }
            })
        }
    })
    const highestLetter = rawLetter.reduce((max, current) => current > max ? current : max, "A")
    const highestNumber = Math.max(...rawNum)
    const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.slice(0, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.indexOf(highestLetter) + 2)
    
    return {
        letters:letters,
        rows:highestNumber + 1,
        cols:letters.length
    }
}

const generate_modal_detail = (storage, storage_desc, room, coor, id) => {
    if(storage){
        return `
            <div class="modal fade" id="modalDetail-${coor}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold" id="exampleModalLabel">Coordinate ${coor}</h5>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                        <div class="modal-body">
                            <div class='row gy-3'>
                                <div class='col-lg-4 col-md-5 col-sm-12'>
                                    <div id='pie-chart-${coor}'></div>
                                    <label>Storage Name</label>
                                    <input type="text" name="inventory_storage" class="form-control" value='${storage ?? ''}'/>
                                    <label>Description</label>
                                    <textarea name="inventory_desc" class="form-control">${storage_desc ?? ''}</textarea>
                                    <div class='mt-3'>
                                        <input value='${id}_${coor}' class='id-coor-holder' hidden>
                                        <a class='btn btn-danger remove_coordinate w-100'><i class="fa-solid fa-trash"></i> Remove Coordinate</a>
                                    </div>
                                </div>
                                <div class='col-lg-8 col-md-7 col-sm-12'>
                                    <div style='overflow-x:auto;'>
                                        <table id='table-inventory-${coor}' class='table' style='min-width:600px;'>
                                            <thead>
                                                <tr class='text-center'>
                                                    <th style="width: 120px;">Rack</th>
                                                    <th style="width: 200px;">Name, Unit & Volume</th>
                                                    <th style="width: 140px;">Category</th>
                                                    <th style="width: 140px;">Price</th>
                                                    <th>Edit</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>                                    
                        </div>
                    </div>
                </div>
            </div>
        `
    } else {
        return `
            <div class="modal fade" id="modalDetail-${coor}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold" id="exampleModalLabel">Coordinate ${coor}</h5>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                        <div class="modal-body">
                            <form id='add-storage-${coor}'>
                                <input type="text" name="layout" value='${coor}' hidden required/>
                                <label>Room</label>
                                <input type="text" name="inventory_room" class="form-control" value='${room}' readonly required/>
                                <label>Storage</label>
                                <input type="text" name="inventory_storage" class="form-control" required/>
                                <label>Description</label>
                                <textarea name="storage_desc" class="form-control"></textarea>
                                <a class='btn btn-success mt-4 w-100 submit_add_storage'><i class="fa-solid fa-floppy-disk"></i> Submit to Coordinate ${coor}</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        ` 
    }
}

const generate_map_room = (target,data,is_interact,room) => {
    let rows
    let letters
    let cols

    if(data){
        const floor_range = generate_floor_range(data)
        rows = floor_range.rows
        letters = floor_range.letters
        cols = floor_range.cols
    } else {
        rows = 5
        letters = 'ABCDE'
        cols = 5
    }

    for (let row = 1; row <= rows; row++) {
        const rowContainer = $('<div class="row"></div>')
        for (let col = 0; col < cols; col++) {
            const label = `${letters[col]}${row}`
            let used = false
            let inventory_storage = null
            let storage_desc = null
            let id = null

            if(data){
                data.forEach(dt => {
                    const coor = dt.layout.split(':')
                    coor.forEach(cr => {
                        if(cr == label){
                            used = true
                            inventory_storage = dt.inventory_storage
                            storage_desc = dt.storage_desc
                            id = dt.id
                        }
                    });
                });
            }

            const modal = is_interact ? generate_modal_detail(inventory_storage, storage_desc, room, label, id) : ''
            const button = $(`
                <button class='d-inline-block room-floor ${used ? 'active':''}' data-bs-toggle="modal" data-bs-target="#modalDetail-${label}" ${inventory_storage && `onclick="get_inventory_room_storage('${room}','${inventory_storage}','${label}')"`}>
                    <h6 class='coordinate'>${label}</h6>
                </button>
                ${modal}
            `)
            rowContainer.append(button)
        }
        $(target).append(rowContainer)
    }

    if(is_interact){
        $(target).append(`
            <div class='floor-config m-3'>
                <a class='d-inline-block btn btn-success' onclick='expand_floor()'><i class="fa-solid fa-up-right-and-down-left-from-center"></i> Expand</a>
                ${data ? `<a class='d-inline-block btn btn-success' href='/doc/layout/${room}'><i class="fa-solid fa-print"></i> Print</a>`:''}
                ${data ? `<a class='d-inline-block btn btn-success' href='/doc/layout/${room}/custom'><i class="fa-solid fa-pen-to-square"></i> Custom Print</a>`:''}
            </div>
        `)
    }
}

const clean_alert_item = () => {
    if ($('#item_holder').find('div.alert').length) {
        $('#item_holder').empty()
    } 
}

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
    const isPriceCategory = $("#report_category_holder").val() === 'Shopping Cart' || $("#report_category_holder").val() === 'Wishlist'

    $('.item_name_selected').each(function(index) {
        if ($(this).text() == val || $(this).text() == $('#item_name').val()) {
            $('.item_qty_selected').eq(index).val(parseInt($('.item_qty_selected').eq(index).val()) + 1)
            itemExists = true
            return false
        }
    });

    if(!itemExists){
        let priceInput = ''
        if($('#report_category_holder').val() == 'Shopping Cart' || $('#report_category_holder').val() == 'Wishlist'){
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
                            <p class="item_name_selected mb-1">${el}</p>
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
                        <p class="item_name_selected mb-1">${val}</p>
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

const reportCategoryHolderEventHandler = (el) => {
    const isPriceCategory = $(el).val() === 'Shopping Cart' || $(el).val() === 'Wishlist'
    const $table = $('.table-report')
    const $theadRow = $table.find('thead tr')
    const $tbody = $table.find('tbody')

    if (isPriceCategory) {
        $('#price_th-holder').length === 0 &&
        $theadRow.children('th').eq(-1).before(
            '<th id="price_th-holder" style="width:140px">Price</th>'
        )

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
        $('#price_th-holder').remove()
        $tbody.find('td.td-price').remove()
        $tbody.find('td[colspan]').attr('colspan', 3)
    }
}

const generate_report_box = (el, search = null) => {
    return `
        <button class="report-box mt-2" onclick="window.location.href='/report/detail/${el.id}'">
            <div class="d-flex justify-content-between mb-3">
                <h5>${el.report_title}</h5>
                <span class="bg-success rounded-pill px-2 py-1">${el.report_category}</span>
            </div>
            ${el.report_desc ? `<p>${el.report_desc}</p>` : `<p class="no-data-message">- No Description Provided -</p>`}
            <h6><b>Items :</b></h6>
            <div class='d-flex justify-content-start mt-2'>${
                search ? `<div class="mb-3 d-flex">${highlight_item(search,el.report_items)}</div>` : el.report_items ? `<p>${el.report_items}</p>` : `<p class="no-data-message">- No items attached -</p>`}
            </div><hr class="mt-0">
            ${(el.report_category === 'Shopping Cart' || el.report_category === 'Wishlist') ? `
                <div class="d-flex justify-content-between mt-2">
                    <div class='total-price'>
                        ${
                            isMobile() ?
                                `<h6 class="fw-bold">Total Price</h6>
                                <p class="mb-0">Rp. ${el.item_price ? el.item_price.toLocaleString() : '-'}</p>`
                            :
                                `<h6 class="fw-bold">Total Price : Rp. ${el.item_price ? el.item_price.toLocaleString() : '-'}</h6>`
                        }
                    </div>
                    <div class='total-item'>
                        ${
                            isMobile() ?
                                `<h6 class="fw-bold">Total Item</h6>
                                <p class="mb-0">${el.total_item ?? '0'}</p>`
                            :
                                `<h6 class="fw-bold">Total Item : ${el.total_item ?? '0'}</h6>`
                        }
                    </div>
                </div>
            ` : ''}
            <p class='date-text mt-2 mb-0'>Created At : ${getDateToContext(el.created_at,'calendar')}</p>
        </button>
    `
}