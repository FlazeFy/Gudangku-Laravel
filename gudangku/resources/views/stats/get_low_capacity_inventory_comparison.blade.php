<div class='container bordered'>
    <div id="stats_total_low_capacity_inventory_comparison_holder"></div>
</div>
<script>
    const getTotalLowCapacityInventoryComparison = (page) => {
        const title = 'Low Capacity Inventory Comparison'
        const ctx = 'total_low_capacity_inventory_comparison_temp'
        const ctx_holder = 'stats_total_low_capacity_inventory_comparison_holder'

        const failedMsg = () => {
            Swal.fire("Oops!",`Failed to get the stats Total ${title}`,"error")
        }
        const fetchData = () => {
            $.ajax({
                url: `/api/v1/stats/inventory/low_capacity_inventory_comparison`,
                type: 'GET',
                beforeSend: function (xhr) {
                    Swal.showLoading()
                    xhr.setRequestHeader("Accept", "application/json")
                    xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
                },
                success: function(response) {
                    Swal.close()
                    const data = response.data
                    localStorage.setItem(ctx,JSON.stringify(data))
                    localStorage.setItem(`last-hit-${ctx}`,Date.now())
                    generateGaugeChart(title,ctx_holder,data,'low_best')
                },
                error: function(response, jqXHR, textStatus, errorThrown) {
                    Swal.close()
                    if(response.status != 404){
                        generateAPIError(response, true)
                    } else {
                        templateAlertContainer(ctx_holder, 'no-data', "No inventory found for this context to generate the stats", 'add a inventory', '<i class="fa-solid fa-warehouse"></i>','/inventory/add')
                        $(`#${ctx_holder}`).prepend(`<h2 class='title-chart'>${ucEachWord(title)}</h2>`)
                    }
                }
            });
        }

        if(ctx in localStorage){
            const lastHit = parseInt(localStorage.getItem(`last-hit-${ctx}`))
            const now = Date.now()

            if(((now - lastHit) / 1000) < statsFetchRestTime){
                const data = JSON.parse(localStorage.getItem(ctx))
                if(data){
                    generateGaugeChart(title,ctx_holder,data,'low_best')
                    Swal.close()
                } else {
                    Swal.close()
                    failedMsg()
                }
            } else {
                fetchData()
            }
        } else {
            fetchData()
        }
    }
    getTotalLowCapacityInventoryComparison()
</script>