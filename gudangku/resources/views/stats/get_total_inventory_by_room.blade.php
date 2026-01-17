<div class='container bordered'>
    <div id="stats_total_inventory_by_room_holder"></div>
</div>
<script>
    const getTotalInventoryByRoom = (page) => {
        Swal.showLoading()
        const title = 'Inventory By Room'
        const ctx = 'total_inventory_by_room_temp'
        const ctx_holder = 'stats_total_inventory_by_room_holder'
        const type_chart =  '<?= session()->get('toogle_total_stats') ?>'

        const failedMsg = () => {
            Swal.fire("Oops!",`Failed to get the stats Total ${title}`,"error")
        }
        const fetchData = () => {
            $.ajax({
                url: `/api/v1/stats/inventory/total_by_room/${type_chart}`,
                type: 'GET',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader("Accept", "application/json")
                    xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
                },
                success: function(response) {
                    Swal.close()
                    const data = response.data
                    localStorage.setItem(ctx,JSON.stringify(data))
                    localStorage.setItem(`last-hit-${ctx}`,Date.now())
                    generatePieChart(`Total ${type_chart} ${title}`,ctx_holder,data)
                    generateTableContextTotal(ctx_holder,data,type_chart == 'price' && ['total'])
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
                    generatePieChart(`Total ${type_chart} ${title}`,ctx_holder,data)
                    generateTableContextTotal(ctx_holder,data,type_chart == 'price' && ['total'])
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
    getTotalInventoryByRoom()
</script>