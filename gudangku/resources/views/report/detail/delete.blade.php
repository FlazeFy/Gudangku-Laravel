<a class="btn btn-danger mb-3 me-2" id="delete-report-modal-btn" data-bs-toggle="modal" data-bs-target="#deleteReportModal"><i class="fa-solid fa-trash" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Delete @endif</a>
<div class="modal fade" id="deleteReportModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fw-bold" id="exampleModalLabel">Delete</h2>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <h2>Are you sure want to <span class="text-danger">Permentally Delete</span> this report?</h2>
                <button class="btn btn-danger mt-4" id="submit-delete-report-btn" onclick="delete_report('{{$id}}')" >Yes, Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
    const delete_report = (id) => {
        Swal.showLoading()
        $.ajax({
            url: `/api/v1/report/delete/report/${id}`,
            type: 'DELETE',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
            },
            success: function(response) {
                Swal.close()
                Swal.fire({
                    title: "Success!",
                    text: response.message,
                    icon: "success"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href='/report'
                    }
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.hideLoading()
                Swal.fire({
                    title: "Oops!",
                    text: "Failed to delete the report",
                    icon: "error"
                });
            }
        });
    }
</script>