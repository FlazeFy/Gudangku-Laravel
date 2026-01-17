<table class="table">
    <thead>
        <tr>
            <th>Username</th>
            <th>Last Login</th>
        </tr>
    </thead>
    <tbody id="last_login-holder"></tbody>
</table>

<script>
    const getLastLogin = () => {
        const item_holder = 'last_login-holder'
        $(`#${item_holder}`).empty()

        $.ajax({
            url: `/api/v1/stats/user/last_login`,
            type: 'GET',
            beforeSend: function (xhr) {
                Swal.showLoading()
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
            },
            success: function(response) {
                Swal.close()
                const data = response.data

                data.forEach(el => {
                    $(`#${item_holder}`).append(`
                        <tr>
                            <td class='text-center fw-bold'>@${el.username}</td>
                            <td>${el.login_at}</td>
                        </tr>
                    `);
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                if(response.status != 404){
                    generateAPIError(response, true)
                } else {
                    templateAlertContainer(item_holder, 'no-data', "No user found to show", 'add a user', '<i class="fa-solid fa-scroll"></i>')
                }
            }
        });
    }
    getLastLogin()
</script>