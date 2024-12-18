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
                <th scope="col" style='min-width:140px;'>Action</th>
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

        $.ajax({
            url: `/api/v1/report?page=${page}${search_key_url}${filter_cat_url}${sorting_url}`,
            type: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
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
                                        <h3 style="font-weight:500; font-size:var(--textJumbo);">${el.report_title}</h3>
                                    </div>
                                    <div>
                                        <span class="bg-success text-white rounded-pill px-3 py-2">${el.report_category}</span>
                                    </div>
                                </div>
                                ${el.report_desc ? `<p class="mt-2">${el.report_desc}</p>` : `<p class="text-secondary fst-italic mt-2">- No Description Provided -</p>`}
                                <br>
                                <h3 class='fw-bold'>Items : </h3>
                                <div class='d-flex justify-content-start mt-2'>${el.report_items ?? '<span class="text-secondary fst-italic mt-2">- No items found -</span>'}</div>
                                ${(el.report_category === 'Shopping Cart' || el.report_category === 'Wishlist') ? `
                                    <div class="d-flex justify-content-between mt-3">
                                        <div>
                                            ${
                                                isMobile() ?
                                                    `<h3 class="fw-bold" style="font-size:var(--textLG);">Total Price</h3>
                                                    <h3 style="font-size:var(--textLG);">Rp. ${el.item_price ? number_format(el.item_price, 0, ',', '.') : '-'}</h3>`
                                                :
                                                    `<h3 class="fw-bold" style="font-size:var(--textJumbo);">Total Price : Rp. ${el.item_price ? number_format(el.item_price, 0, ',', '.') : '-'}</h3>`
                                            }
                                        </div>
                                        <div>
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
                                <td class='text-center'>${getDateToContext(el.created_at,'calendar')}</td>
                                <td>
                                    <a class="btn btn-warning me-2" href="/report/detail/${el.id}" style="padding: var(--spaceMini) var(--spaceSM) !important;">
                                        <i class="fa-solid fa-pen-to-square" style="font-size:var(--textSM);"></i>
                                    </a>
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
                    template_alert_container(item_holder, 'no-data', "No report found to show", 'add a report', '<i class="fa-solid fa-scroll"></i>')
                    $(`#${item_holder}`).prepend(`<h2 class='title-chart'>${ucEachWord(title)}</h2>`)
                }
            }
        });
    }
    get_my_report_all(page,search_key,filter_category,sorting)
</script>