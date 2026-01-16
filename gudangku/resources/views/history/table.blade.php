<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th scope="col" style='min-width:160px;'>Username</th>
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
                            <td>${el.history_type} from item called ${el.history_context}</td>
                            <td class='text-center'>${getDateToContext(el.created_at,'calendar')}</td>
                            <td>
                                <button class="btn btn-danger d-block mx-auto" data-bs-toggle="modal" data-bs-target="#modalDelete_${el.id}"><i class="fa-solid fa-trash mx-2"></i></button>
                                <div class="modal fade" id="modalDelete_${el.id}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold" id="exampleModalLabel">Delete</h5>
                                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Delete this history about ${el.history_type} from item called ${el.history_context}?</p>
                                                <button class="btn btn-danger mt-4" onclick="destroy_history_by_id('${el.id}', '${token}', 
                                                ()=>get_all_history(${page}))">Yes, Delete</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `);
                });

                generatePagination(item_holder, get_all_history, total_page, current_page)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                if(response.status != 404){
                    generateAPIError(response, true)
                } else {
                    templateAlertContainer(item_holder, 'no-data', "No history found to show", null, '<i class="fa-solid fa-scroll"></i>')
                }
            }
        });
    }
    get_all_history(page)
</script>