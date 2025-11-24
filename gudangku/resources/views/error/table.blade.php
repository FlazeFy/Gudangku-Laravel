<div id="error_holder">
    <table class="table">
        <thead class="text-center">
            <tr>
                <th scope="col" style='max-width:160px;'>Message</th>
                <th scope="col" style='min-width:80px;'>Stack Trace</th>
                <th scope="col" style='max-width:180px;'>File</th>
                <th scope="col" style='min-width:140px;'>Faced At</th>
                <th scope="col" style='max-width:140px;'>Action</th>
            </tr>
        </thead>
        <tbody id="error_tb_body"></tbody>
    </table>
</div>
<hr>

<script>
    let page = 1
    const get_all_error = (page) => {
        Swal.showLoading()
        const item_holder = 'error_tb_body'
        $(`#${item_holder}`).empty()
        $.ajax({
            url: `/api/v1/error?page=${page}`,
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
                            <td>${el.message}</td>
                            <td>
                                <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalDetailStack_${el.id}"><i class="fa-solid fa-circle-info" style="font-size:var(--textXLG);"></i></button>
                                <div class="modal fade" id="modalDetailStack_${el.id}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Stack Trace</h5>
                                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>${el.stack_trace}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><span class='bg-success rounded-pill px-3 py-1 fw-bold'>Line ${el.line}</span><br><div class="mt-1">${el.file}</div></td>
                            <td class='text-center'>${getDateToContext(el.created_at,'calendar')}</td>
                            <td>
                                <button class="btn btn-danger d-block mx-auto btn-delete" data-bs-toggle="modal" data-bs-target="#modalDelete_${el.id}"><i class="fa-solid fa-trash mx-2"></i></button>
                                <div class="modal fade" id="modalDelete_${el.id}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold" id="exampleModalLabel">Delete</h5>
                                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Delete this error about "${el.message}"?</p>
                                                <button class="btn btn-danger mt-4" onclick="destroy_error_by_id('${el.id}', '${token}', 
                                                ()=>get_all_error(${page}))">Yes, Delete</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `);
                });

                generate_pagination(item_holder, get_all_error, total_page, current_page)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                if(response.status != 404){
                    generate_api_error(response, true)
                } else {
                    template_alert_container(item_holder, 'no-data', "No error found to show", null, '<i class="fa-solid fa-scroll"></i>')
                }
            }
        });
    }
    get_all_error(page)
</script>