@if($role == 0)
    <div id="report_holder"></div>
@else 
    <table class="table">
        <thead class="text-center">
            <tr>
                <th scope="col" style='width:140px;'>Report Title</th>
                <th scope="col" style='width:160px;'>Category</th>
                <th scope="col" style='min-width:140px;'>Description</th>
                <th scope="col" style='min-width:140px;'>Items</th>
                <th scope="col" style='min-width:140px;'>Price</th>
                <th scope="col">Props</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        <tbody id="report_holder"></tbody>
    </table>
@endif
<script>
    const get_my_report_all = (page,name,category,sort) => {
        Swal.showLoading()
        const item_holder = 'report_holder'
        let search_key_url = name ? `&search_key=${name}`:''
        let filter_cat_url = category ? `&filter_category=${category}`:''
        let sorting_url = sort ? `&sorting=${sort}`:''
        const token = '<?= session()->get("token_key"); ?>'

        $.ajax({
            url: `/api/v1/report?page=${page}${search_key_url}${filter_cat_url}${sorting_url}`,
            type: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
            },
            success: function(response) {
                Swal.close()
                const data = response.data.data
                const data_report_header = response.report_header
                const current_page = response.data.current_page
                const total_page = response.data.last_page
                const total_item = response.data.total
                const role = "<?php echo $role; ?>"; 

                $('#total-item').text(total_item)
                $(`#${item_holder}`).empty()
                data_report_header.forEach(el => {
                    warehouse.push({
                        'title':el.report_title,
                        'items':el.report_items
                    })
                })

                data.forEach(el => {
                    if(role == 0){
                        $(`#${item_holder}`).append(`
                            <button class="report-box mt-2" onclick="window.location.href='/report/detail/${el.id}'">
                                <div class="d-flex justify-content-between mb-2">
                                    <div>
                                        <h2 style="font-weight:500; font-size:var(--textJumbo);">${el.report_title}</h2>
                                    </div>
                                    <div>
                                        <span class="bg-success text-white rounded-pill px-3 py-2 report-category">${el.report_category}</span>
                                    </div>
                                </div>
                                <div class="report-desc">
                                ${el.report_desc ? `<p class="mt-2">${el.report_desc}</p>` : `<p class="text-secondary fst-italic mt-2">- No Description Provided -</p>`}
                                </div>
                                <br>
                                <h3 class='fw-bold'>Items : </h3>
                                <div class='d-flex justify-content-start mt-2 report-items'>${el.report_items ?? '<span class="text-secondary fst-italic mt-2">- No Items Found -</span>'}</div>
                                ${(el.report_category === 'Shopping Cart' || el.report_category === 'Wishlist') ? `
                                    <div class="d-flex justify-content-between mt-3">
                                        <div class='total-price'>
                                            ${
                                                isMobile() ?
                                                    `<h3 class="fw-bold" style="font-size:var(--textLG);">Total Price</h3>
                                                    <h3 style="font-size:var(--textLG);">Rp. ${el.item_price ? number_format(el.item_price, 0, ',', '.') : '-'}</h3>`
                                                :
                                                    `<h3 class="fw-bold" style="font-size:var(--textJumbo);">Total Price : Rp. ${el.item_price ? number_format(el.item_price, 0, ',', '.') : '-'}</h3>`
                                            }
                                        </div>
                                        <div class='total-item'>
                                            ${
                                                isMobile() ?
                                                    `<h3 class="fw-bold" style="font-size:var(--textLG);">Total Item</h3>
                                                    <h3 style="font-size:var(--textLG);">${el.total_item ?? '0'}</h3>`
                                                :
                                                    `<h3 class="fw-bold" style="font-size:var(--textJumbo);">Total Item : ${el.total_item ?? '0'}</h3>`
                                            }
                                        </div>
                                    </div>
                                ` : ''}
                                <h6 class='date-text mt-2'>Created At : ${getDateToContext(el.created_at,'calendar')}</h6>
                                ${ role == 1 ? `<h6 class='date-text mt-2'>Created By : @${el.username}</h6>` : ''}
                            </button>
                        `);
                    } else {
                        $(`#${item_holder}`).append(`
                            <tr>
                                <td>${el.report_title}</td>
                                <td class='text-center pt-3'><span class="bg-success text-white rounded-pill px-3 py-2 w-100">${el.report_category}</span></td>
                                <td>${el.report_desc ?? '<span class="text-secondary fst-italic mt-2">- No Description Provided -</span>'}</td>
                                <td>${el.report_items ?? '<span class="text-secondary fst-italic mt-2">- No items found -</span>'}</td>
                                <td>Rp. ${el.item_price ? number_format(el.item_price, 0, ',', '.') : '-'}</td>
                                <td>
                                    <b>Created By</b>
                                    <a>@${el.username}</a><br><br>
                                    <b>Created At</b>
                                    ${getDateToContext(el.created_at,'calendar')}
                                </td>
                                <td style="width:120px;">
                                    <a class="btn btn-warning me-2" href="/report/detail/${el.id}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a class="btn btn-danger me-2" data-bs-toggle="modal" data-bs-target="#modalDelete_${el.id}">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                    <div class="modal fade" id="modalDelete_${el.id}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h2 class="modal-title fw-bold" id="exampleModalLabel">Delete</h2>
                                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                                                </div>
                                                <div class="modal-body">
                                                    <h2><span class="text-danger">Permanently Delete</span> this report "${el.report_title}" from user @${el.username}?</h2>
                                                    <a class="btn btn-danger mt-4" onclick="destroy_report_by_id('${el.id}', '${token}', 
                                                    ()=>get_my_report_all(${page},'${search_key}','${filter_category}','${sorting}'))">Yes, Delete</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>  
                                </td>
                            </tr>
                        `);
                    }
                });

                generate_pagination(item_holder, get_my_report_all, total_page, current_page)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                if(response.status != 404){
                    Swal.fire({
                        title: "Oops!",
                        text: "Failed to get the report",
                        icon: "error"
                    });
                } else {
                    template_alert_container(item_holder, 'no-data', "No report found to show", null, '<i class="fa-solid fa-scroll"></i>','')
                }
            }
        });
    }
    get_my_report_all(page,search_key,filter_category,sorting)
</script>