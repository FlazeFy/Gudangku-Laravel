<div id="work_area"></div>
<script>
    let editor = new RichTextEditor("#work_area")
    const type = '<?= $type ?>'
    const id = '<?= $id ?>'
    const get_generated_default_document = () => {
        Swal.showLoading()
        $.ajax({
            url: type == 'report' ? `/api/v1/report/detail/item/${id}/doc` : `/api/v1/inventory/layout/${id}/doc`,
            type: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
            },
            success: function(response) {
                Swal.close()
                editor.setHTML(response.data)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                if(response.status != 404){
                    Swal.fire({
                        title: "Oops!",
                        text: "Failed to generated",
                        icon: "error"
                    });
                }
            }
        });
    }
    get_generated_default_document()
</script>