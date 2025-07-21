const delete_reminder_by_id = (id, token, refreshData) => {
    Swal.showLoading()
    $.ajax({
        url: `/api/v1/reminder/${id}`,
        type: 'DELETE',
        beforeSend: function (xhr) {
            xhr.setRequestHeader("Accept", "application/json")
            xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
        },
        success: function(response) {
            $(`#modalDeleteReminder_${id}`).modal('hide')
            Swal.fire({
                title: "Success!",
                text: response.message,
                icon: "success",
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.close()
                    refreshData()
                }
            });
        },
        error: function(response, jqXHR, textStatus, errorThrown) {
            Swal.close()
            if(response.status != 404){
                Swal.fire({
                    title: "Oops!",
                    text: "Something wrong. Please contact admin",
                    icon: "error"
                });
            } else {
                Swal.fire({
                    title: "Oops!",
                    text: response.responseJSON.message,
                    icon: "error"
                });
            }
        }
    });
}