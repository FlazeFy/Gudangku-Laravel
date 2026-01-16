<style>
    .history-box {
        padding: var(--spaceXMD);
        margin-bottom: var(--spaceXMD);
        border-radius: var(--roundedMD);
        border: 1.5px solid var(--primaryColor);
    }
</style>

<div id="history_holder"></div>
<script>
    let page = 1
    const get_history = (page) => {
        Swal.showLoading()
        const item_holder = 'history_holder'
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

                $(`#${item_holder}`).empty()

                data.forEach(el => {
                    $(`#${item_holder}`).append(`
                        <div class="history-box">
                            <div class="d-flex justify-content-between">
                                <div class="">
                                    <h6>${el.history_type} from item called ${el.history_context}</h6>
                                    <p class='date-text mt-2 mb-0'>Created At : ${getDateToContext(el.created_at,'calendar')}</p>
                                </div>
                                <div class="pe-2 ps-3">
                                    <button class="btn btn-danger d-block mx-auto btn-delete" data-bs-toggle="modal" data-bs-target="#modalDelete_${el.id}"><i class="fa-solid fa-trash mx-2"></i></button>
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
                                                    ()=>get_history(${page}))">Yes, Delete</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                });

                generatePagination(item_holder, get_history, total_page, current_page)
                $('#export-section').html(`
                    <form class="d-inline" action="/history/save_as_csv" method="POST">
                        @csrf
                        <button class="btn btn-primary" type="submit" id="save_as_csv_btn"><i class="fa-solid fa-print"></i> Print</button>
                    </form>
                `)
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
                    templateAlertContainer(item_holder, 'no-data', "No history found to show", null, '<i class="fa-solid fa-rotate-left"></i>')
                    $(`#${item_holder}`).prepend(`<h2 class='title-chart'>${ucEachWord(title)}</h2>`)
                }
            }
        });
    }
    get_history(page)
</script>
