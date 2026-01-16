<div class="d-flex justify-content-between align-items-center">
    <h2>Report</h2>
    <span id="add_report-holder"></span>
</div>
<div id="report_holder"></div>
<script>
    const highlight_item = (find,items) => {
        const index = items.toLowerCase().indexOf(find.toLowerCase())
        if (index === -1) return items
        const beforeMatch = items.slice(0, index)
        const match = items.slice(index, index + find.length)
        const afterMatch = items.slice(index + find.length)
        return `${beforeMatch}<div class='fst-italic bg-primary rounded px-2 py-0'>${match}</div>${afterMatch}`
    } 

    let page = 1
    const get_my_report_all = (page,search,id) => {
        const item_holder = 'report_holder'
        Swal.showLoading()
        $.ajax({
            url: `/api/v1/report/${search}/${id}?page=${page}`,
            type: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json");
                xhr.setRequestHeader("Authorization", `Bearer ${token}`);    
            },
            success: function(response) {
                Swal.close()
                const data = response.data.data
                const current_page = response.data.current_page
                const total_page = response.data.last_page

                $(`#${item_holder}`).empty()
                data.forEach(el => {
                    $(`#${item_holder}`).append(generateReportBox(el, search));
                });
                $(`#add_report-holder`).html(`
                    <a class='btn btn-success' data-bs-toggle="modal" data-bs-target="#modalAddReport"><i class="fa-solid fa-plus"></i> Add Report</a>
                `)

                generatePagination(item_holder, get_my_report_all, total_page, current_page)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                if(response.status != 404){
                    generateAPIError(response, true)
                } else {
                    templateAlertContainer(item_holder, 'no-data', "This inventory doesn't asigned in any report", 'assign to report', '<i class="fa-solid fa-scroll"></i>')
                }
            }
        });
    }
</script>