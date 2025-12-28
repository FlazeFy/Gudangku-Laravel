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
            generate_api_error(response, true)
        }
    });
}

const update_reminder_by_id = (id,inventory_id,data) => {    
    $.ajax({
        url: `/api/v1/reminder/${id}`,
        type: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify({
            reminder_type: data.reminder_type,
            reminder_context: data.reminder_context,
            reminder_desc: data.reminder_desc,
            inventory_id: inventory_id
        }),
        beforeSend: function (xhr) {
            Swal.showLoading()
            xhr.setRequestHeader("Accept", "application/json")
            xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
        },
        success: function(response) {
            Swal.close()
            Swal.fire({
                title: "Success!",
                text: response.message,
                icon: "success"
            }).then((result) => {
                if (result.isConfirmed) {
                    get_detail_inventory(inventory_id)
                }
            });
        },
        error: function(response, jqXHR, textStatus, errorThrown) {
            generate_api_error(response, true)
        }
    });
}