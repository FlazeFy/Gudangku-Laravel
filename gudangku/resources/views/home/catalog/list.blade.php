<style>
    .btn-feature i {
        font-size: var(--textLG);
    }
    .btn-feature i.catalog-icon {
        font-size: 80px;
    }
    @media (min-width: 576px) and (max-width: 767px) {
        .btn-feature i.catalog-icon {
            font-size: 60px;
        }
    }
    @media (max-width: 575px) {
        .btn-feature i.catalog-icon {
            font-size: 40px;
        }
    }
</style>

<div class="container-form">
    <h2>By {{ucwords($view)}} - {{ucwords($context)}}</h2><hr>
    <div class="row gy-3" id="inventory-holder"></div>
</div>

<script>
    let page = 1
    $('#inventory-holder').empty()

    const get_inventory = (page) => {
        $.ajax({
            url: `/api/v1/inventory/catalog/${view}/${catalog}?page=${page}`,
            type: 'GET',
            beforeSend: function (xhr) {
                Swal.showLoading()
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
            },
            success: function(response) {
                Swal.close()

                const data = response.data.data
                const current_page = response.data.current_page
                const last_page = response.data.last_page

                data.forEach(dt => {
                    $('#inventory-holder').append(`
                        <div class="col-md-6 col-sm-12 px-2">
                            <button class="btn-feature" style="${dt.deleted_at !== null ? 'background:rgba(221, 0, 33, 0.15) !important;' : ''}; ${dt.deleted_at ? 'border-color:var(--dangerBG);':''}"
                                onclick="window.location.href='/inventory/edit/${dt.id}'">
                                <div class="row m-0 align-items-center">
                                    <div class="col-4">
                                        ${dt.inventory_image == null ? `<i class="fa-solid fa-box catalog-icon"></i>` : `
                                            <img class="img img-fluid" style="border-radius: var(--roundedMD);" src="${dt.inventory_image}" title="${dt.inventory_name}">
                                        `}
                                        <h5 class="mt-3">${dt.inventory_name}</h5>
                                    </div>
                                    <div class="col-8 text-start">
                                        <h6>Detail</h6>
                                        <div class="d-flex flex-wrap gap-2 mb-2" style="font-size:var(--textSM);">
                                            ${dt.is_favorite == '1' ? `
                                                <span class="p-2 rounded d-inline-flex align-items-center bg-danger">
                                                    <i class="fa-solid fa-heart"></i>
                                                </span>` : ''}
                                            <span class="p-2 rounded d-inline-flex align-items-center bg-success">Rp. ${dt.inventory_price ? dt.inventory_price.toLocaleString() : '-'}</span>
                                            <span class="p-2 rounded d-inline-flex align-items-center bg-primary">${dt.inventory_vol} ${dt.inventory_unit}</span>
                                            ${dt.inventory_capacity_unit === 'percentage' ? `
                                                <span class="p-2 rounded ${dt.inventory_capacity_vol > 30 ? 'bg-primary' : 'bg-danger'}">${dt.inventory_capacity_vol}%</span>` : ''}
                                            ${dt.reminder_type ? `
                                                <span class="p-2 rounded bg-success">
                                                    <i class="fa-solid fa-bell"></i> ${dt.reminder_type.replaceAll('_',' ').toLowerCase().replace(/\b\w/g, c => c.toUpperCase())}
                                                </span>` : ''}
                                        </div><hr class="mb-0 mt-3">
                                        <p class='date-text mt-2 mb-0'>Created At : ${getDateToContext(dt.created_at,'calendar')}</p>
                                        ${dt.updated_at ? `<p class='date-text mb-0'>Last Updated : ${getDateToContext(dt.updated_at,'calendar')}</p>`:''}
                                    </div>
                                </div>
                            </button>
                        </div>
                    `);
                }); 
                
                if(current_page < last_page){
                    $('#inventory-holder').append(`
                        <div class="col-12"><button class="btn btn-primary" onclick="navigate_page(${page})">Next Page</button></div>
                    `);
                } 
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                if(response.status != 404){
                    generate_api_error(response, true)
                } else {
                    
                }
            }
        });
    }
    get_inventory(page)

    const navigate_page = (page) => {
        $('#inventory-holder').children().last().remove()
        get_inventory(page + 1)
    }
</script>