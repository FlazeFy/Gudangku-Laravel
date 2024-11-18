<div id="user_holder">
    <table class="table">
        <thead class="text-center">
            <tr>
                <th scope="col" style='width:160px;'>Username</th>
                <th scope="col" style='min-width:180px;'>Contact</th>
                <th scope="col" style='min-width:60px;'>Timezone</th>
                <th scope="col" style='min-width:110px;'>Joined At</th>
                <th scope="col" style='min-width:110px;'>Last Updated</th>
                <th scope="col" style='min-width:140px;'>Action</th>
            </tr>
        </thead>
        <tbody id="user_tb_body"></tbody>
    </table>
</div>
<hr>

<script>
    const get_all_user = () => {
        Swal.showLoading()
        const item_holder = 'user_tb_body'

        $.ajax({
            url: `/api/v1/user`,
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

                data.forEach(el => {
                    $(`#${item_holder}`).append(`
                        <tr>
                            <td>${el.username}</td>
                            <td>
                                <h6 class='fw-bold'>Telegram User ID</h6> 
                                <h6>${el.telegram_user_id ?? '-'}</h6>   
                            </td>
                            <td>${el.timezone}</td>
                            <td>${el.created_at}</td>
                            <td>${el.updated_at ?? '-'}</td>
                            <td></td>
                        </tr>
                    `);
                });

                generate_pagination(item_holder, get_my_report_all, total_page, current_page)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                if(response.status != 404){
                    Swal.fire({
                        title: "Oops!",
                        text: "Failed to get the inventory",
                        icon: "error"
                    });
                } else {
                    template_alert_container(item_holder, 'no-data', "No inventory found to show", 'add a inventory', '<i class="fa-solid fa-scroll"></i>')
                    $(`#${item_holder}`).prepend(`<h2 class='title-chart'>${ucEachWord(title)}</h2>`)
                }
            }
        });
    }
    get_all_user()
</script>