<div class='container bordered'>
    <div id="stats_total_inventory_by_favorite_holder"></div>
</div>
<script>
    const get_total_inventory_by_favorite = (page) => {
        Swal.showLoading()
        $.ajax({
            url: `/api/v1/stats/inventory/total_by_favorite/<?= session()->get('toogle_total_stats') ?>`,
            type: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
            },
            success: function(response) {
                Swal.close()
                const data = response.data
                generate_pie_chart('Total <?= session()->get('toogle_total_stats') ?> Inventory By Favorite','stats_total_inventory_by_favorite_holder',data)
                generate_table_context_total('stats_total_inventory_by_favorite_holder',data)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                Swal.fire({
                    title: "Oops!",
                    text: "Failed to get the stats",
                    icon: "error"
                });
            }
        });
    }
    get_total_inventory_by_favorite()
</script>