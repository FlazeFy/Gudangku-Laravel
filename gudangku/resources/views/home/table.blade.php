<style>
    .action-inventory {
        min-width: 140px;
    }
    @media screen and (max-width: 1023px) {
        .action-inventory {
            min-width: 240px;
        }
    }
</style>

<div class="table-responsive">
    <table class="table" id="inventory_tb">
        <thead>
            <tr class="tr-header">
                <th scope="col" style='min-width:200px;'>Name & Description</th>
                <th scope="col" style='min-width:140px;'>Category & Merk</th>
                <th scope="col" style='min-width:140px;'>Placement</th>
                <th scope="col" style='min-width:110px;'>Price</th>
                <th scope="col" style='min-width:110px;'>Unit</th>
                <th scope="col" style='min-width:110px;'>Capacity</th>
                <th scope="col" class='action-inventory'>Action</th>
            </tr>
        </thead>
        <tbody id="inventory_tb_body"></tbody>
    </table>
</div>
<hr>

<script>
    const date_holder = document.querySelectorAll('.date_holder')

    date_holder.forEach(e => {
        const date = new Date(e.textContent);
        e.textContent = getDateToContext(e.textContent, "datetime")
    });

    const get_inventory = (page,name,category,sort) => {
        Swal.showLoading()
        const item_holder = 'inventory_tb_body'
        let search_key_url = name ? `&search_key=${name}`:''
        let filter_cat_url = category ? `&filter_category=${category}`:''
        let sorting_url = sort ? `&sorting=${sort}`:''

        $.ajax({
            url: `/api/v1/inventory?page=${page}${search_key_url}${filter_cat_url}${sorting_url}`,
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
                const role = <?php echo $role; ?>; 

                $('#total-item').text(total_item)
                $(`#${item_holder}`).empty()

                data.forEach((el, idx) => {
                    let styletr = ''
                    let reminders = ''
                    if (el.deleted_at != null) {
                        styletr = `style="background:rgba(221, 0, 33, 0.15);"`
                    }
                    const idCollapse = el.reminder ? `collapseReminder-${el.id}` : null

                    if(el.reminder){
                        reminders += `<tr class="collapse" id="${idCollapse}" style="border-style: hidden !important;"><td colspan="5">`
                        el.reminder.forEach(rm => {
                            reminders += `
                                <div class="box-reminder mb-3">
                                    <h6 class="fw-bold mb-3">Reminder | ${rm.reminder_type.replace(/_/g, " ").replace(/\b\w/g, c => c.toUpperCase())}</h6>
                                    <p>${rm.reminder_desc}</p>
                                    <p class="mt-2 mb-0">Time: ${rm.reminder_context.replace(/_/g, " ").replace(/\b\w/g, c => c.toUpperCase())}</p>
                                    <p class="my-0">Created At: ${getDateToContext(rm.created_at,'calendar')}</p>
                                    <hr class="my-2">
                                    
                                    <button class="btn btn-danger py-1 px-3" data-bs-toggle="modal" data-bs-target="#modalDeleteReminder_${rm.id}">
                                        <i class="fa-solid fa-trash" style="font-size:var(--textMD);"></i>
                                    </button>
                                    <div class="modal fade" id="modalDeleteReminder_${rm.id}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold" id="exampleModalLabel">Delete</h5>
                                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="reminder_desc" value="${rm.reminder_desc}"/>
                                                    <p><span class="text-danger">Permanently Delete</span> this reminder "${rm.reminder_desc}"?</p>
                                                    <a class="btn btn-danger" onclick="deleteModuleByID('${rm.id}', 'reminder', 'destroy', '${token}', () => get_inventory(${page},'${name}','${category}','${sort}'))"> Yes, Delete</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <button class="btn btn-success py-1 px-3" data-bs-toggle="modal" onclick="reset_reminder_form()" data-bs-target="#modalCopyReminder_${rm.id}">
                                        <i class="fa-solid fa-copy" style="font-size:var(--textMD);"></i>
                                    </button>
                                    <div class="modal fade" id="modalCopyReminder_${rm.id}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold" id="exampleModalLabel">Copy Reminder</h5>
                                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form id="copy_reminder_form_${rm.id}">
                                                        <input type="hidden" value="${rm.reminder_context}" name="reminder_context">
                                                        <input type="hidden" value="${rm.reminder_desc}" name="reminder_desc">
                                                        <input type="hidden" value="${rm.reminder_type}" name="reminder_type">

                                                        <div style='overflow-x:auto;'>
                                                            <table class="table" id="tb-inventory-name-${rm.id}" style="min-width: 530px;">
                                                                <thead>
                                                                    <tr>
                                                                        <th scope="col" style="width:110px;"><a class="btn btn-primary checked_all_holder_btn py-1" style="font-size:var(--textSM);">Check All</a></th>
                                                                        <th scope="col" style="min-width:240px;">Inventory Name</th>
                                                                        <th scope="col" style="width:180px;">Category</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class='inventory-selectable'></tbody>
                                                            </table>
                                                        </div>
                                                        <p>Are you sure to copy this reminder "${rm.reminder_desc}" to inventory <span id="inventory_selected_name"></span>?</p>
                                                        <a class="btn btn-success" onclick="copy_reminder('${rm.id}')">Yes, Copy</a>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `
                        });
                        reminders += `</td></tr><tr><td colspan="15"></td></tr>`
                    }
                    
                    $(`#${item_holder}`).append(`
                        <tr ${styletr} class='inventory-tr ${el.deleted_at ? 'deleted-inventory' :''}'>
                            <td ${el.reminder ? 'rowspan="2"' : ''}>
                                ${el.inventory_image ? `
                                    <img src="${el.inventory_image}" data-bs-toggle='modal' data-bs-target='#zoom_image-${el.id}'class='img-responsive img-zoomable-modal mb-3' title="${el.inventory_name}">
                                ` : ''}
                                ${el.is_favorite ? `<span class='bg-success rounded-pill px-3 py-1 favorite-status'><i class="fa-solid fa-bookmark" title="Favorite"></i> Favorite</span>` : ''}
                                <h6 class='mt-2 inventory-name' style='font-size:var(--textLG); font-weight:600;'>${el.inventory_name}</h6>
                                <hr class='my-2'>
                                <h6 class='mt-2'>Description</h6>
                                <p class='inventory-desc'>${el.inventory_desc || '<span class="no-data-message">- No Description Provided -<span>'}</p>
                                ${role == 1 ? `
                                    <h6 class='mt-2'>Created By</h6>
                                    <p>@${el.username}</p>
                                ` : ''}
                            </td>
                            <td ${el.reminder ? 'rowspan="2"' : ''}>
                                <h6>Category</h6>
                                <p class='inventory-category'>${el.inventory_category}</p>
                                <h6 class='mt-2'>Merk</h6>
                                <p class='inventory-merk'>${el.inventory_merk || '-'}</p>
                            </td>
                            <td ${el.reminder ? 'rowspan="2"' : ''}>
                                <h6>Room</h6>
                                <p class='inventory-room'>${el.inventory_room}</p>
                                <h6 class='mt-2'>Storage</h6>
                                <p class='inventory-storage'>${el.inventory_storage ?? '-'}</p>
                                ${el.inventory_rack ? `
                                    <h6 class='mt-2'>Rack</h6>
                                    <p class='inventory-rack mb-0'>${el.inventory_rack ?? '-'}</p>
                                    `:''
                                }
                            </td>
                            <td ${el.reminder ? 'rowspan="2"' : ''} class="text-center">Rp. ${el.inventory_price ? el.inventory_price.toLocaleString() : '-'}</td>
                            <td ${el.reminder ? 'rowspan="2"' : ''} class="text-center">${el.inventory_vol} ${el.inventory_unit === 'Kilogram' ? 'Kg':el.inventory_unit}</td>
                            <td ${el.reminder ? 'rowspan="2"' : ''} class="text-center">
                                ${el.inventory_capacity_unit === 'percentage' ? `${el.inventory_capacity_vol}%` : '-'}
                            </td>
                            <td>
                                <button class="btn btn-primary w-100 btn-props" data-bs-toggle="modal" data-bs-target="#modalInfoProps_${el.id}">
                                    <i class="fa-solid fa-circle-info"></i> Properties
                                </button>
                                <div class="modal fade" id="modalInfoProps_${el.id}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Properties</h5>
                                                <button type="button" class="btn btn-danger btn-close" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <h6>Created At</h6>
                                                        <p>${getDateToContext(el.created_at,'calendar')}</p>
                                                    </div>
                                                    <div class="col-6">
                                                        <h6>Updated At</h6>
                                                        <p>${el.updated_at ? getDateToContext(el.updated_at,'calendar') : '-'}</p>
                                                    </div>
                                                </div>
                                                <h6>Deleted At</h6>
                                                <p>${el.deleted_at ? getDateToContext(el.deleted_at,'calendar') : '-'}</p>
                                                <div class="alert alert-primary mt-3" role="alert">
                                                    <h6 class='fw-bold'><i class="fa-solid fa-circle-info"></i> For Your Information</h6>
                                                    <p class='mt-2 mb-0'><b>${el.inventory_name}</b> is been existed in your inventory for about <b>${countTime(el.created_at,null)}</b></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class='row mt-2 mx-1'>
                                    ${role === 0 ? `
                                        <div class='col p-0 pe-1'>
                                            <a class="btn btn-danger w-100 btn-like" onclick="favToogleInventoryByID('${el.id}', ${el.is_favorite == 0 ? '1' : '0'}, '${token}', 
                                                ()=>get_inventory(${page},'${search_key}','${filter_category}',sorting))" style="${el.is_favorite ? 'background:var(--dangerBG); border:none;' : ''}">
                                                <i class="fa-solid fa-heart"></i>
                                            </a>
                                        </div>
                                    ` : ''}
                                    <div class='col p-0 ${role === 0 && 'ps-1' }'>
                                        <input type="hidden" name="type_delete" value="${el.deleted_at ? "hard" : "soft"}">
                                        <button class="btn btn-danger modal-btn w-100 btn-delete" data-bs-toggle="modal" data-bs-target="#modalDelete_${el.id}">
                                            <i class="fa-solid ${el.deleted_at ? "fa-fire" : "fa-trash"}"></i>
                                        </button>
                                        <div class="modal fade" id="modalDelete_${el.id}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold" id="exampleModalLabel">Delete</h5>
                                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close">
                                                            <i class="fa-solid fa-xmark"></i>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>${el.deleted_at ? '<span class="text-danger">Permanently Delete</span>' : 'Delete'} this inventory "${el.inventory_name}"?</p>
                                                        <a class="btn btn-danger" onclick="deleteModuleByID('${el.id}', 'inventory', '${el.deleted_at ? 'destroy' : 'delete'}', '${token}', ()=>get_inventory(${page},'${search_key}','${filter_category}',sorting))">Yes, Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class='row mt-2 mx-1'>
                                    <div class='col p-0 pe-1'>
                                        <input type="hidden" name="is_editable" value="${el.deleted_at ? "false" : "true"}">
                                        <a class="btn btn-warning modal-btn w-100 btn-manage"
                                            ${el.deleted_at 
                                                ? `data-bs-toggle="modal" data-bs-target="#modalRecover_${el.id}"` 
                                                : `href="/inventory/edit/${el.id}"`
                                            }>
                                            <i class="fa-solid ${el.deleted_at ? "fa-rotate" : "fa-pen-to-square"}"></i>
                                        </a>
                                        ${el.deleted_at ? `
                                        <div class="modal fade" id="modalRecover_${el.id}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold" id="exampleModalLabel">Recover</h5>
                                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close">
                                                            <i class="fa-solid fa-xmark"></i>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Recover this inventory "${el.inventory_name}"?</p>
                                                        <a class="btn btn-success" onclick="recover_inventory_by_id('${el.id}', '${token}', ()=>get_inventory(${page},'${search_key}','${filter_category}',sorting))">Yes, Recover</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>` : ''}
                                    </div>
                                    ${idCollapse ? `
                                        <div class='col p-0 ps-1'>
                                            <button class="btn btn-success ${el.reminder && "bg-success border-0"} w-100 btn-reminder" data-bs-toggle="collapse" href="#${idCollapse}">
                                                <i class="fa-solid ${el.reminder ? "fa-bell" : "fa-bell-slash"}"></i>
                                            </button>
                                            ${reminders}
                                        </div>
                                        `:''
                                    }
                                </div>
                            </td>
                        </tr>
                    `)
                });

                $('.inventory-selectable').empty()
                data.forEach((el, idx) => {
                    $('.inventory-selectable').append(`
                        <tr class="align-middle">
                            <td class="text-center">
                                <div class="form-check d-inline-flex justify-content-center">
                                    <input class="form-check-input check_inventory" type="checkbox" value="${el.id}" id="flexCheckDefault">
                                </div>
                            </td>
                            <td>${el.inventory_name}</td>
                            <td>${el.inventory_category}</td>
                        </tr>
                    `)
                })

                zoomableModal()
                generatePagination(item_holder, get_inventory, total_page, current_page,sorting)

                get_lend_item(response.lend_data)

                $('#toolbar-button-section').html(`
                    <form class="d-inline" action="/inventory/save_as_csv" method="POST">
                        @csrf
                        <button class="btn btn-primary" id="save_as_csv_btn" type="submit"><i class="fa-solid fa-print"></i> Print</button>
                    </form>
                `)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                if(response.status != 404){
                    generateAPIError(response, true)
                } else {
                    $('#total-item').text(0)
                    $(`#${item_holder}`).html(`<tr><td colspan='7' class='text-center py-3'>- No Inventory Found -</td></tr>`)
                }
            }
        });
    }
    get_inventory(page,search_key,filter_category,sorting)

    const reset_reminder_form = () => {
        $(document).ready(function () {
            $('.check_inventory').prop('checked', false)
        })
    }

    const copy_reminder = (id) => {
        const modal = $(`#modalCopyReminder_${id}`)
        const reminder_id = modal.find('.reminder_id').val()
        const reminder_desc = modal.find('input[name="reminder_desc"]').val()
        const reminder_type = modal.find('input[name="reminder_type"]').val()
        const reminder_context = modal.find('input[name="reminder_context"]').val()
        const checkedInventory = modal.find('.check_inventory:checked')
        const list_inventory_id = checkedInventory.map(function () {
            return $(this).val()
        }).get().join(',')

        if (!list_inventory_id) {
            Swal.fire("Oops!",'Please select at least one inventory item.',"warning")
            return
        }

        $.ajax({
            url: `/api/v1/reminder/copy`,
            type: 'POST',
            beforeSend: function (xhr) {
                Swal.showLoading()
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
            },
            data: {
                list_inventory_id: list_inventory_id,
                reminder_desc: reminder_desc,
                reminder_type: reminder_type,
                reminder_context: reminder_context
            },
            dataType:'json',
            success: function(response) {
                $(`#modalCopyReminder_${reminder_id}`).modal('hide')
                Swal.fire({
                    title: "Success!",
                    text: response.message,
                    icon: "success",
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        closeModalBS() 
                        get_inventory(page,search_key,filter_category,sorting)
                    }
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                generateAPIError(response, true)
            }
        });
    }

    $(document).on('click', '.checked_all_holder_btn', function () {
        const index = $('.checked_all_holder_btn').index(this)
        const $tbody = $('.inventory-selectable').eq(index)
        $tbody.find('input[type="checkbox"]').prop('checked', true)
    });
</script>