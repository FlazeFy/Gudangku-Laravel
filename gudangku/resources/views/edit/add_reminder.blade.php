<div class="modal fade" id="modalAddReminder" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="exampleModalLabel">Add Reminder using item : <span id='inventory_name_title_add_reminder'></span></h5>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <form id='form_add_reminder' method="POST">
                    @csrf
                    <input hidden id='inventory_id_add_reminder' name="inventory_id">
                    <input hidden id='inventory_name_add_reminder' name="item_name">
                    <label>Description</label>
                    <textarea name="reminder_desc" id="reminder_desc" class="form-control"></textarea>
                    <div class="row">
                        <div class="col-5">
                            <label>Type</label>
                            <select class="form-select" name="reminder_type" id="reminder_type_holder" aria-label="Default select example"></select>
                        </div>
                        <div class="col-7">
                            <label>Context</label>
                            <select class="form-select" name="reminder_context" id="reminder_context" aria-label="Default select example"></select>
                        </div>
                    </div>
                    <div class="form-check d-flex align-items-center">
                        <input class="form-check-input" type="checkbox" name='send_demo' id='send_demo'>
                        <label class="form-check-label" for="flexCheckChecked">Send me the Demo</label>
                    </div>
                    <a class="btn btn-success mt-4 w-100" id='form_add_reminder_btn'><i class="fa-solid fa-floppy-disk"></i> Save</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $('#modalAddReminder').on('shown.bs.modal', async function () {
        $(async function () {
            await get_context_opt('reminder_type',token)
        })
    });

    $(document).on('change','#reminder_type_holder',function(){
        const selected = $(this).val()

        if(selected !== "-"){
            get_reminder_context_select(selected,'#reminder_context')
        } else {
            generate_empty_field_error('reminder type')
        }
    })

    const post_reminder = (form,is_checked) => {
        let formData = $(`#${form}`).serializeArray()
        formData.push({ name: "send_demo", value: is_checked })
        let dataObject = {}
        formData.forEach(item => dataObject[item.name] = item.value)

        $.ajax({
            url: '/api/v1/reminder',
            type: 'POST',
            data: dataObject,
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
            },
            success: function(response) {
                $('#modalAddReminder').modal('hide')
                Swal.fire({
                    title: "Success!",
                    text: response.message,
                    icon: "success",
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.close()
                        get_detail_inventory("<?= $id ?>")
                    }
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                generate_api_error(response, true)
            }
        });
    }

    $(document).on('click', '#form_add_reminder_btn', function() {
        post_reminder('form_add_reminder',$('#send_demo').is(":checked"))
    })
</script>