<div id="work_area"></div>
<script>
    let editor = new RichTextEditor("#work_area")
    const type = '<?= $type ?>'
    const id = '<?= $id ?>'
    const filter_in = '<?= $filter_in ?? ''?>'
    const get_generated_default_document = () => {
        Swal.showLoading()
        $.ajax({
            url: (() => {
                if (type === 'report') {
                    return `/api/v1/report/detail/item/${id}/doc${filter_in ? `?filter_in=${filter_in}` : ''}`
                } else if (type === 'layout') {
                    return `/api/v1/inventory/layout/${id}/doc`
                } else if (type === 'inventory') {
                    return `/api/v1/inventory/detail/${id}/doc`
                } else {
                    return ''
                }
            })(),
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