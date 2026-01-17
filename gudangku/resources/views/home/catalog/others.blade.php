<div class="container-form">
    <h2>By Room</h2><hr>
    <div class="d-flex flex-wrap gap-2" id="inventory_by_room-holder"></div>
</div>
<div class="container-form">
    <h2>By Category</h2><hr>
    <div class="d-flex flex-wrap gap-2" id="inventory_by_category-holder"></div>
</div>
<div class="container-form">
    <h2>By Storage</h2><hr>
    <div class="d-flex flex-wrap gap-2" id="inventory_by_storage-holder"></div>
</div>

<script>
    const get_others_inventory = () => {
        const holder = ['room','category','storage']

        $.ajax({
            url: `/api/v1/inventory/catalog`,
            type: 'GET',
            beforeSend: function (xhr) {
                Swal.showLoading()
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
            },
            success: function(response) {
                Swal.close()

                holder.forEach(el => {
                    const data = response.data[el]
                    $(`#inventory_by_${el}-holder`).empty()

                    const icon = el === 'room' ? 'fa-house' : el === 'category' ? 'fa-toolbox' : 'fa-box-archive'

                    if(data){
                        data.forEach((dt, idx) => {
                            $(`#inventory_by_${el}-holder`).append(`
                                <a class="p-2 ${el === view && catalog === dt.context ? 'bg-primary' : 'bordered bg-transparent'} rounded d-inline-flex gap-2 text-nowrap" href="/inventory/by/${el}/${dt.context}">
                                    ${dt.context ?? `<i>- No ${ucFirst(el)} -</i>`}${el === view && catalog === dt.context ? '' : ` <span class="px-2 rounded bg-primary">${dt.total}</span>`}
                                </a>
                            `)
                        })
                    } else {
                        templateAlertContainer(`inventory_by_${el}-holder`, 'no-data', `No inventory by ${el} to show`, null, '<i class="fa-solid fa-rotate-left"></i>')
                    }
                })                
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                if(response.status != 404){
                    generateAPIError(response, true)
                } else {
                    holder.forEach(el => {
                        $(`#inventory_by_${el}-holder`).empty()
                        templateAlertContainer(`inventory_by_${el}-holder`, 'no-data', `No inventory by ${el} to show`, null, '<i class="fa-solid fa-rotate-left"></i>')
                    })
                }
            }
        })
    }
    get_others_inventory()
</script>