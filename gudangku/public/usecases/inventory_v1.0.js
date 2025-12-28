const delete_inventory_by_id = (id, type, token, refreshData) => {
    Swal.showLoading()
    $.ajax({
        url: `/api/v1/inventory/${type}/${id}`,
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
                    closeModalBS()    
                    refreshData()
                } 
            });
        },
        error: function(response, jqXHR, textStatus, errorThrown) {
            generate_api_error(response, true)
        }
    });
}

const fav_toogle_inventory_by_id = (id, is_favorite, token, refreshData) => {
    Swal.showLoading()
    $.ajax({
        url: `/api/v1/inventory/fav_toggle/${id}`,
        type: 'PUT',
        data: {
            is_favorite: is_favorite
        },
        dataType: 'json',
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

const recover_inventory_by_id = (id, token, refreshData) => {
    Swal.showLoading()
    $.ajax({
        url: `/api/v1/inventory/recover/${id}`,
        type: 'PUT',
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
                    closeModalBS()    
                    refreshData()
                } 
            });
        },
        error: function(response, jqXHR, textStatus, errorThrown) {
            generate_api_error(response, true)
        }
    });
}