const destroy_history_by_id = (id, token, refreshData) => {
    Swal.showLoading()
    $.ajax({
        url: `/api/v1/history/destroy/${id}`,
        type: 'DELETE',
        beforeSend: function (xhr) {
            xhr.setRequestHeader("Accept", "application/json")
            xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
        },
        success: function(response) {
            Swal.hideLoading()
            Swal.fire({
                title: "Success!",
                text: `${response.message}`,
                icon: "success",
                allowOutsideClick: false,
            }).then((result) => {
                if (result.isConfirmed) {
                    refreshData()
                } 
            });
        },
        error: function(response, jqXHR, textStatus, errorThrown) {
            generate_api_error(response, true)
        }
    });
}