<div id="work_area"></div>
<script>
    let editor = new RichTextEditor("#work_area")
    const type = '<?= $type ?>'
    const id = '<?= $id ?>'
    const filter_in = '<?= $filter_in ?? ''?>'
    
    const getGeneratedDefaultDocument = () => {
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
                Swal.showLoading()
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
            },
            success: function(response) {
                Swal.close()
                editor.setHTML(response.data)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                generateAPIError(response, true)
            }
        })
    }
    getGeneratedDefaultDocument()
</script>