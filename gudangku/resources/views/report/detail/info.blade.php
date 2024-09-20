<div id="report_holder"></div>
<div id="report_item_holder">
    <table class="table mt-3" id="report_item_tb">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Item Name</th>
                <th scope="col">Description</th>
                <th scope="col">Qty</th>
                <th scope="col">Price</th>
                <th scope="col">Created At</th>
                <th scope="col">Edit</th>
                <th scope="col">Delete</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script>
    const get_detail_report = (id) => {
        Swal.showLoading()
        $.ajax({
            url: `/api/v1/report/detail/item/${id}`,
            type: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
            },
            success: function(response) {
                Swal.close()
                const item_holder = 'report_holder'
                const data = response.data
                const data_item = response.data_item

                $(`#report-title-holder`).text(data.report_title)
                $(`#${item_holder}`).html(`
                    <div class="d-flex justify-content-between mb-2">
                        <div>
                            <h3 style="font-weight:500; font-size:var(--textJumbo);">${data.report_title}</h3>
                        </div>
                        <div>
                            <span class="bg-success text-white rounded-pill px-3 py-2">${data.report_category}</span>
                        </div>
                    </div>
                    ${data.report_desc ? `<p class="mt-2">${data.report_desc}</p>` : `<p class="text-secondary fst-italic mt-2">- No Description Provided -</p>`}
                    <br>

                    ${(data.report_category === 'Shopping Cart' || data.report_category === 'Wishlist') ? `
                        <div class="d-flex justify-content-between mt-3">
                            <div>
                                <h3 class="fw-bold" style="font-size:var(--textJumbo);">Total Price: Rp. ${data.total_price}</h3>
                            </div>
                            <div>
                                <h3 class="fw-bold" style="font-size:var(--textJumbo);">Total Item: ${data.total_item}</h3>
                            </div>
                        </div>
                    ` : ''}
                `);

                data_item.forEach(dt => {
                    $('#report_item_tb tbody').append(`
                        <tr>
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="">
                                </div>
                            </td>
                            <td>${dt.item_name}</td>
                            <td>${dt.item_desc ?? '<span class="fst-italic text-secondary">- No Description Provided -</span>'}</td>
                            <td>${dt.item_qty}</td>
                            <td>Rp. ${dt.item_price}</td>
                            <td>${getDateToContext(dt.created_at,'calendar')}</td>
                            <td><button class="btn btn-warning"><i class="fa-solid fa-pen-to-square" style="font-size:var(--textXLG);"></i></button></td>
                            <td><button class="btn btn-danger"><i class="fa-solid fa-fire" style="font-size:var(--textXLG);"></i></button></td>
                        </tr>
                    `)
                });
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
    get_detail_report('{{$id}}')
</script>