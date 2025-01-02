<div id="user_holder">
    <table class="table">
        <thead class="text-center">
            <tr>
                <th scope="col" style='width:160px;'>Username</th>
                <th scope="col" style='min-width:180px;'>Contact</th>
                <th scope="col" style='min-width:100px;'>Timezone</th>
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
                const current_page = response.data.current_page
                const total_page = response.data.last_page
                const total_item = response.data.total

                data.forEach(el => {
                    $(`#${item_holder}`).append(`
                        <tr>
                            <td class='text-center fw-bold'>@${el.username}</td>
                            <td>
                                <h6 class='fw-bold'>Telegram User ID</h6> 
                                <h6>${el.telegram_user_id ?? '-'}</h6>
                                <h6 class='fw-bold mt-1'>Firebase FCM Token</h6> 
                                <p>${el.firebase_fcm_token ?? '-'}</p> 
                                <h6 class='fw-bold mt-1'>Line User ID</h6> 
                                <h6>${el.line_user_id ?? '-'}</h6>    
                            </td>
                            <td class='text-center'>${el.timezone ?? '-'}</td>
                            <td class='text-center'>${getDateToContext(el.created_at,'calendar')}</td>
                            <td class='text-center'>${el.updated_at ? getDateToContext(el.updated_at,'calendar') : '-'}</td>
                            <td></td>
                        </tr>
                    `);
                });

                generate_pagination(item_holder, get_all_user, total_page, current_page)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                if(response.status != 404){
                    Swal.fire({
                        title: "Oops!",
                        text: "Failed to get the user",
                        icon: "error"
                    });
                } else {
                    template_alert_container(item_holder, 'no-data', "No user found to show", 'add a user', '<i class="fa-solid fa-scroll"></i>')
                }
            }
        });
    }
    get_all_user()
</script>