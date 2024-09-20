<div id="report_holder"></div>
<script>
    let page = 1
    const get_my_report_all = (page) => {
        Swal.showLoading()
        $.ajax({
            url: `/api/v1/report?page=${page}`,
            type: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
            },
            success: function(response) {
                Swal.close()
                const item_holder = 'report_holder'
                const data = response.data.data
                const data_report_header = response.report_header
                const current_page = response.data.current_page
                const total_page = response.data.last_page

                $(`#${item_holder}`).empty()
                data_report_header.forEach(el => {
                    warehouse.push({
                        'title':el.report_title,
                        'items':el.report_items
                    })
                })

                data.forEach(el => {
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
                            <h3>Items : </h3>
                            <div class='d-flex justify-content-start mt-2'>${el.report_items}</div>

                            ${(el.report_category === 'Shopping Cart' || el.report_category === 'Wishlist') ? `
                                <div class="d-flex justify-content-between mt-3">
                                    <div>
                                        <h3 class="fw-bold" style="font-size:var(--textJumbo);">Total Price: Rp. ${el.item_price.toLocaleString()}</h3>
                                    </div>
                                    <div>
                                        <h3 class="fw-bold" style="font-size:var(--textJumbo);">Total Item: ${el.total_item}</h3>
                                    </div>
                                </div>
                            ` : ''}
                        </button>
                    `);
                });

                generate_pagination(item_holder, get_my_report_all, total_page, current_page)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                Swal.fire({
                    title: "Oops!",
                    text: "Failed to get the report",
                    icon: "error"
                });
            }
        });
    }
    get_my_report_all(page)
</script>