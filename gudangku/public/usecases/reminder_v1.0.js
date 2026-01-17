const updateReminderByID = (id,inventory_id,data) => {    
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
            Swal.fire("Success!", response.message, "success").then((result) => {
                if (result.isConfirmed) {
                    getDetailInventoryByID(inventory_id)
                }
            });
        },
        error: function(response, jqXHR, textStatus, errorThrown) {
            generateAPIError(response, true)
        }
    });
}