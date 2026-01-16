<div class="col-lg-6 mx-auto">
    <div class='container bordered'>
        <h2 class='title-chart'>Most Expensive Inventory Per Category</h2>
        <div id="stats_most_expensive_inventory_per_category"></div>
    </div>
</div>
<div class="col-lg-6 mx-auto">
    <div class='container bordered'>
        <h2 class='title-chart'>Most Expensive Inventory Per Merk</h2>
        <div id="stats_most_expensive_inventory_per_merk"></div>
    </div>
</div>
<div class="col-lg-6 mx-auto">
    <div class='container bordered'>
        <h2 class='title-chart'>Most Expensive Inventory Per Room</h2>
        <div id="stats_most_expensive_inventory_per_room"></div>
    </div>
</div>
<div class="col-lg-6 mx-auto">
    <div class='container bordered'>
        <h2 class='title-chart'>Most Expensive Inventory Per Storage</h2>
        <div id="stats_most_expensive_inventory_per_storage"></div>
    </div>
</div>
<script>
    const get_most_expensive_inventory_per_context = () => {
        const ctx_holders = [
            { holder: "stats_most_expensive_inventory_per_category", object: "inventory_category" },
            { holder: "stats_most_expensive_inventory_per_merk", object: "inventory_merk" },
            { holder: "stats_most_expensive_inventory_per_room", object: "inventory_room" },
            { holder: "stats_most_expensive_inventory_per_storage", object: "inventory_storage" }
        ]

        const failedMsg = () => {
            Swal.fire("Oops!",`Failed to get the stats Total ${title}`, "error")
        }

        const fetchData = () => {
            $.ajax({
                url: `/api/v1/stats/inventory/most_expensive/inventory_category,inventory_room,inventory_storage,inventory_merk`,
                type: 'GET',
                beforeSend: function (xhr) {
                    Swal.showLoading()
                    xhr.setRequestHeader("Accept", "application/json")
                    xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
                },
                success: function(response) {
                    Swal.close()
                    const data = response.data
                    localStorage.setItem('most_expensive_stats', JSON.stringify(response.data))
                    localStorage.setItem(`last-hit-most_expensive_stats`, Date.now())
                    ctx_holders.forEach(ctx_holder => {
                        const ctxData = data[ctx_holder.object]
                        if (ctxData) {
                            generateTableContextTotal(ctx_holder.holder, ctxData, 'Rp. ')
                        } else {
                            templateAlertContainer(ctx_holder.holder, 'no-data', "No inventory found for this context to generate the stats", 'add a inventory', '<i class="fa-solid fa-warehouse"></i>', '/inventory/add')
                        }
                    });
                },
                error: function(response, jqXHR, textStatus, errorThrown) {
                    Swal.close()
                    if (response.status != 404) {
                        generateAPIError(response, true)
                    } else {
                        ctx_holders.forEach(ctx_holder => {
                            templateAlertContainer(ctx_holder.holder, 'no-data', "No inventory found for this context to generate the stats", 'add a inventory', '<i class="fa-solid fa-warehouse"></i>', '/inventory/add')
                            $(`#${ctx_holder.holder}`).prepend(`<h2 class='title-chart'>${ucEachWord(title)}</h2>`)
                        })
                    }
                }
            });
        }

        if ('most_expensive_stats' in localStorage) {
            const lastHit = parseInt(localStorage.getItem(`last-hit-most_expensive_stats`))
            const now = Date.now()

            if (((now - lastHit) / 1000) < statsFetchRestTime) {
                const data = JSON.parse(localStorage.getItem('most_expensive_stats'))
                if (data) {
                    ctx_holders.forEach(ctx_holder => {
                        generateTableContextTotal(ctx_holder.holder, data[ctx_holder.object], 'Rp. ')
                        Swal.close()
                    });
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

    get_most_expensive_inventory_per_context()
</script>