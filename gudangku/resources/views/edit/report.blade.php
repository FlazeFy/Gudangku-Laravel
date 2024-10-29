<hr>
<h6 class="fw-bold mt-3" style="font-size:var(--textXLG);">Report</h6>
<div id="report_holder"></div>
<script>
    const highlight_item = (find,items) => {
        const index = items.toLowerCase().indexOf(find.toLowerCase())
        if (index === -1) return items
        const beforeMatch = items.slice(0, index)
        const match = items.slice(index, index + find.length)
        const afterMatch = items.slice(index + find.length)
        return `${beforeMatch}<div class='fst-italic fw-bold bg-primary rounded px-2 py-0 mx-1'>${match}</div>${afterMatch}`
    } 

    let page = 1
    const get_my_report_all = (page,search,id) => {
        const item_holder = 'report_holder'
        Swal.showLoading()
        $.ajax({
            url: `/api/v1/report/${search}/${id}?page=${page}`,
            type: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json");
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>");    
            },
            success: function(response) {
                Swal.close()
                const data = response.data.data
                const current_page = response.data.current_page
                const total_page = response.data.last_page

                $(`#${item_holder}`).empty()
                data.forEach(el => {
                    $(`#${item_holder}`).append(`
                        <button class="report-box mt-2">
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
                            <div class='d-flex justify-content-start mt-2'>${highlight_item(search,el.report_items)}</div>

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
                            <h6 class='date-text mt-2'>Created At : ${getDateToContext(el.created_at,'calendar')}</h6>
                        </button>
                    `);
                });

                generate_pagination(item_holder, get_my_report_all, total_page, current_page)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                if(response.status != 404){
                    Swal.fire({
                        title: "Oops!",
                        text: "Something wrong. Please contact admin",
                        icon: "error"
                    });
                } else {
                    template_alert_container(item_holder, 'no-data', "This item doesn't asigned in any report", 'assign to report', '<i class="fa-solid fa-scroll"></i>')
                }
            }
        });
    }
</script>