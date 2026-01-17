<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th scope="col" style='min-width:160px;'>Username</th>
                <th scope="col" style='min-width:180px;'>Contact</th>
                <th scope="col" style='min-width:100px;'>Timezone</th>
                <th scope="col" style='min-width:110px;'>Joined At</th>
                <th scope="col" style='min-width:110px;'>Last Updated</th>
                <th scope="col" style='min-width:80px;'>Action</th>
            </tr>
        </thead>
        <tbody id="user_tb_body"></tbody>
    </table>
</div>
<hr>

<script>
    const getAllUser = (page) => {
        const item_holder = 'user_tb_body'
        $(`#${item_holder}`).empty()

        $.ajax({
            url: `/api/v1/user?page=${page}`,
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
                            <td>
                                <a class="btn btn-danger px-2 shadow" data-bs-toggle="modal" data-bs-target="#modalDelete_${el.id}"><i class="fa-solid fa-trash-can"></i></a>
                                <div class="modal fade" id="modalDelete_${el.id}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold" id="exampleModalLabel">Delete</h5>
                                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><span class="text-danger">Permanently Delete</span> this user "@${el.username}"?</p>
                                                <a class="btn btn-danger mt-4" onclick="deleteModuleByID('${el.id}', 'user', 'destroy', '${token}', 
                                                    ()=>getAllUser(${page}))">Yes, Delete</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>    
                            </td>
                        </tr>
                    `);
                });

                generatePagination(item_holder, getAllUser, total_page, current_page)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                if(response.status != 404){
                    generateAPIError(response, true)
                } else {
                    templateAlertContainer(item_holder, 'no-data', "No user found to show", 'add a user', '<i class="fa-solid fa-scroll"></i>')
                }
            }
        });
    }
    getAllUser(page)
</script>