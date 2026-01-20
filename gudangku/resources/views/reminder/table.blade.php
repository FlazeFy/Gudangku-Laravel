<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th scope="col" style='min-width:160px'>Username</th>
                <th scope="col" style='min-width:160px'>Inventory</th>
                <th scope="col" style='min-width:180px'>Reminder Desc</th>
                <th scope="col" style='min-width:180px'>Reminder Type</th>
                <th scope="col" style='min-width:180px'>Reminder Context</th>
                <th scope="col" style='min-width:140px'>Last Execute</th>
                <th scope="col" style='min-width:80px'>Action</th>
            </tr>
        </thead>
        <tbody id="reminder_tb_body"></tbody>
    </table>
</div>
<hr>

<script>
    let page = 1

    const getAllReminder = (page) => {
        const item_holder = 'reminder_tb_body'
        $(`#${item_holder}`).empty()
        $.ajax({
            url: `/api/v1/reminder/mark?page=${page}`,
            type: 'GET',
            beforeSend: function (xhr) {
                Swal.showLoading()
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
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
                            <td>
                                <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalReRemind_${el.id}"><i class="fa-solid fa-bell"></i></button>
                                <div class="modal fade" id="modalReRemind_${el.id}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Properties</h5>
                                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure to re-remind @${el.username} about this reminder with description <span class="fst-italic fw-bold bg-primary rounded px-2 py-0 mx-1 my-2">"${el.reminder_desc}"</span> that attached with inventory ${el.inventory_name}</p>
                                                <button class="btn btn-success mt-4" onclick="post_re_remind('${el.id}')">Yes, Remind</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `)
                })

                generatePagination(item_holder, getAllReminder, total_page, current_page)
            },
            error: function(response, jqXHR, textStatus, reminderThrown) {
                if(response.status != 404){
                    generateAPIError(response, true)
                } else {
                    $(`#${item_holder}`).html(`<tr><td colspan='7' id='err_no_data-msg'></td></tr>`)
                    templateAlertContainer('err_no_data-msg', 'no-data', "No reminder found to show", null, '<i class="fa-solid fa-scroll"></i>')
                }
            }
        })
    }
    getAllReminder(page)

    const post_re_remind = (id) => {
        $.ajax({
            url: '/api/v1/reminder/re_remind',
            type: 'POST',
            data: {
                'reminder_id':id
            },
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
                Swal.showLoading()
            },
            success: function(response) {
                $(`#modalReRemind_${id}`).modal('hide')
                Swal.close()
                Swal.fire("Success!",response.message, "success").then((result) => {
                    if (result.isConfirmed) {
                        getAllReminder(1)
                    }
                })
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                generateAPIError(response, true)
            }
        })
    }
</script>