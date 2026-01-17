@extends('components.layout')

@section('content')
<div class="row">
    <div class="col">
        <div class='container bordered'>
            <div id="stats_total_item_inventory_by_favorite_holder"></div>
        </div>
    </div>
    <div class="col">
        <div class='container bordered'>
            <div id="stats_total_price_inventory_by_favorite_holder"></div>
        </div>
    </div>
</div>
<script>
    const getTotalInventoryByFavorite = (type_chart,ctx_holder) => {
        Swal.showLoading()
        const title = 'Inventory By Favorite'

        const failedMsg = () => {
            Swal.fire("Oops!",`Failed to get the stats Total ${title}`,"error")
        }
        const fetchData = () => {
            $.ajax({
                url: `/api/v1/stats/inventory/total_by_favorite/${type_chart}`,
                type: 'GET',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader("Accept", "application/json")
                },
                success: function(response) {
                    Swal.close()
                    const data = response.data
                    localStorage.setItem(ctx_holder,JSON.stringify(data))
                    localStorage.setItem(`last-hit-${ctx_holder}`,Date.now())
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

        if(ctx_holder in localStorage){
            const lastHit = parseInt(localStorage.getItem(`last-hit-${ctx_holder}`))
            const now = Date.now()

            if(((now - lastHit) / 1000) < statsFetchRestTime){
                const data = JSON.parse(localStorage.getItem(ctx_holder))
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
    getTotalInventoryByFavorite('item','stats_total_item_inventory_by_favorite_holder')
    getTotalInventoryByFavorite('price','stats_total_price_inventory_by_favorite_holder')
</script>
@endsection