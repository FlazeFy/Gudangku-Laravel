<style>
    .btn-feature i {
        font-size: 80px;
    }
    @media (min-width: 576px) and (max-width: 767px) {
        .btn-feature i {
            font-size: 60px;
        }
    }
    @media (max-width: 575px) {
        .btn-feature i {
            font-size: 40px;
        }
    }
</style>

<div class="container-form">
    <h2>By Room</h2><hr>
    <div class="row gy-3" id="inventory_by_room-holder"></div>
</div>
<div class="container-form">
    <h2>By Category</h2><hr>
    <div class="row gy-3" id="inventory_by_category-holder"></div>
</div>
<div class="container-form">
    <h2>By Storage</h2><hr>
    <div class="row gy-3" id="inventory_by_storage-holder"></div>
</div>

<script>
    const get_inventory = () => {
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
                                <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6 col-6 px-2">
                                    <button class="btn-feature" onclick="location.href='/inventory/by/${el}/${dt.context}';">
                                        <i class="fa-solid ${icon}"></i>
                                        <h5 class="mt-3 mb-2">${dt.context ?? `<span class="no-data-message">- No ${ucFirst(el)} -</span>`}</h5>
                                        <span class="py-1 px-2 rounded bg-success">${dt.total} Item</span>
                                    </button>
                                </div>
                            `)
                        });
                    } else {
                        templateAlertContainer(`inventory_by_${el}-holder`, 'no-data', `No inventory by ${el} to show`, null, '<i class="fa-solid fa-rotate-left"></i>')
                    }
                });                
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                if(response.status != 404){
                    generateAPIError(response, true)
                } else {
                    holder.forEach(el => {
                        $(`#inventory_by_${el}-holder`).empty()
                        templateAlertContainer(`inventory_by_${el}-holder`, 'no-data', `No inventory by ${el} to show`, null, '<i class="fa-solid fa-rotate-left"></i>')
                    });
                }
            }
        });
    }
    get_inventory()
</script>