<a class="btn btn-danger" id="delete-report-modal-btn" data-bs-toggle="modal" data-bs-target="#deleteReportModal"><i class="fa-solid fa-trash"></i> Delete</a>
<div class="modal fade" id="deleteReportModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="exampleModalLabel">Delete</h5>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <p>Are you sure want to <span class="text-danger">Permanently Delete</span> this report?</p>
                <button class="btn btn-danger mt-4" id="submit-delete-report-btn" onclick="deleteModuleByID('<?= $id ?>', 'report', 'destroy/report', token, ()=>window.location.href='/report')" >Yes, Delete</button>
            </div>
        </div>
    </div>
</div>