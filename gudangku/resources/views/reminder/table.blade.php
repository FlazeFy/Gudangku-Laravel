<div id="reminder_holder">
    <table class="table">
        <thead class="text-center">
            <tr>
                <th scope="col" style='max-width:160px;'>Username</th>
                <th scope="col" style='min-width:160px;'>Inventory</th>
                <th scope="col" style='max-width:180px;'>Reminder Desc</th>
                <th scope="col" style='max-width:180px;'>Reminder Type</th>
                <th scope="col" style='max-width:180px;'>Reminder Context</th>
                <th scope="col" style='min-width:140px;'>Last Execute</th>
                <th scope="col" style='max-width:140px;'>Action</th>
            </tr>
        </thead>
        <tbody id="reminder_tb_body"></tbody>
    </table>
</div>
<hr>

<script>
    let page = 1
    const get_all_reminder = (page) => {
        Swal.showLoading()
        const item_holder = 'reminder_tb_body'
        $(`#${item_holder}`).empty()
        $.ajax({
            url: `/api/v1/reminder/mark?page=${page}`,
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
                            <td class='fw-bold'>@${el.username}</td>
                            <td><span class='bg-success rounded-pill px-3 py-1 fw-bold'>${el.inventory_category}</span><br><div class="mt-1">${el.inventory_name}</div></td>
                            <td>${el.reminder_desc}</td>
                            <td>${el.reminder_type}</td>
                            <td>${el.reminder_context}</td>
                            <td class='text-center'>${getDateToContext(el.last_execute,'calendar')}</td>
                            <td></td>
                        </tr>
                    `);
                });

                generate_pagination(item_holder, get_all_reminder, total_page, current_page)
            },
            reminder: function(response, jqXHR, textStatus, reminderThrown) {
                Swal.close()
                if(response.status != 404){
                    Swal.fire({
                        title: "Oops!",
                        text: "Failed to get the reminder",
                        icon: "reminder"
                    });
                } else {
                    template_alert_container(item_holder, 'no-data', "No reminder found to show", null, '<i class="fa-solid fa-scroll"></i>')
                }
            }
        });
    }
    get_all_reminder(page)
</script>