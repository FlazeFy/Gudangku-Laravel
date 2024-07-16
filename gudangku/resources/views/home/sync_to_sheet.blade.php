<a class="btn btn-primary mb-3 me-2" data-bs-toggle="modal" data-bs-target="#modalSyncToSheet"><i class="fa-solid fa-globe" 
    style="font-size:var(--textXLG);"></i> @if(!$isMobile) Sync to Sheet @endif</a>
<div class="modal fade" id="modalSyncToSheet" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fw-bold" id="exampleModalLabel">Sync To Sheet</h2>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <label>SpreadSheet Link</label>
                <input type="text" name="sheet_link" class="form-control my-2"/>
                <a class="text-warning"><i class="fa-solid fa-triangle-exclamation"></i> Only sync to public sheet</a>

                <p class="fst-italic mb-0 mt-2">Last Sync : <span id="last_sync">-</span></p>
                <button class="btn btn-success mt-4" type="submit"><i class="fa-solid fa-arrows-rotate"></i> Sync</button>
            </div>
        </div>
    </div>
</div>