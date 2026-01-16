<div class="row text-center">
    <div class="col">
        <h5 class="fw-bold" style="font-size:var(--textJumbo);">Most Inventory</h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody id="most_inventory-holder"></tbody>
        </table>
    </div>
    <div class="col">
        <h5 class="fw-bold" style="font-size:var(--textJumbo);">Most Report</h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody id="most_report-holder"></tbody>
        </table>
    </div>
</div>

<script>
    const get_leaderboard = () => {
        $.ajax({
            url: `/api/v1/stats/user/leaderboard`,
            type: 'GET',
            beforeSend: function (xhr) {
                Swal.showLoading()
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
            },
            success: function(response) {
                Swal.close()
                const data = response.data

                const context = ['inventory','report']
                context.forEach(ctx => {
                    data[`user_with_most_${ctx}`].forEach(el => {
                        $(`#most_${ctx}-holder`).append(`
                            <tr>
                                <td class='text-center fw-bold'>@${el.username}</td>
                                <td>${el.total}</td>
                            </tr>
                        `)
                    });
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                if(response.status != 404){
                    Swal.fire({
                        title: "Oops!",
                        text: "Failed to get the user",
                        icon: "error"
                    });
                } else {
                    templateAlertContainer(item_holder, 'no-data', "No user found to show", 'add a user', '<i class="fa-solid fa-scroll"></i>')
                }
            }
        });
    }
    get_leaderboard()
</script>