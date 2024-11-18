<div id="history_holder">
    <table class="table">
        <thead class="text-center">
            <tr>
                <th scope="col" style='width:160px;'>Username</th>
                <th scope="col" style='min-width:180px;'>History</th>
                <th scope="col" style='min-width:110px;'>Created At</th>
                <th scope="col" style='min-width:140px;'>Action</th>
            </tr>
        </thead>
        <tbody id="history_tb_body"></tbody>
    </table>
</div>
<hr>

<script>
    let page = 1
    const get_all_history = (page) => {
        Swal.showLoading()
        const item_holder = 'history_tb_body'
        $(`#${item_holder}`).empty()
        $.ajax({
            url: `/api/v1/history?page=${page}`,
            type: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
            },
            success: function(response) {
                Swal.close()
                const data = response.data.data
                const current_page = response.data.current_page
                const total_page = response.data.last_page
                const total_item = response.data.total

                data.forEach(el => {
                    $(`#${item_holder}`).append(`
                        <tr>
                            <td class='text-center fw-bold'>@${el.username}</td>
                            <td>${el.history_type} from item called ${el.history_context}</td>
                            <td class='text-center'>${getDateToContext(el.created_at,'calendar')}</td>
                            <td></td>
                        </tr>
                    `);
                });

                generate_pagination(item_holder, get_all_history, total_page, current_page)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                if(response.status != 404){
                    Swal.fire({
                        title: "Oops!",
                        text: "Failed to get the history",
                        icon: "error"
                    });
                } else {
                    template_alert_container(item_holder, 'no-data', "No history found to show", null, '<i class="fa-solid fa-scroll"></i>')
                }
            }
        });
    }
    get_all_history(page)
</script>