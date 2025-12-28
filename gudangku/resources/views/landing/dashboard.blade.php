<style>
    .dashboard-title, .dashboard-subtitle {
        text-align: center;
    }
    .dashboard-title {
        font-size: calc(var(--textXJumbo) * 2.75) !important; 
    }
    .dashboard-second {
        font-size: calc(var(--textXJumbo) * 1.5) !important; 
    }
    .dashboard-title, .dashboard-second {
        font-weight: bold;
    }
    .dashboard-subtitle {
        font-size: var(--textXLG) !important; 
        padding: var(--spaceXSM) var(--spaceXMD);
        margin-top: var(--spaceXSM);
        font-weight: 600;
        background: var(--infoBG);
        width: fit-content;
        margin-inline: auto;
        display: block;
        border-radius: var(--roundedXLG);
    }
    @media screen and (min-width: 768px) and (max-width: 1023px) {
        .dashboard-title {
            font-size: calc(var(--textXJumbo) * 2.25) !important; 
        }
        .dashboard-second {
            font-size: calc(var(--textXJumbo) * 1.25) !important; 
        }
        .dashboard-subtitle {
            font-size: var(--textLG) !important; 
            padding: var(--spaceXXSM) var(--spaceMD);
            margin-top: var(--spaceXXSM);
        }
    }
    @media screen and (max-width: 767px) {
        .dashboard-title {
            font-size: calc(var(--textXJumbo) * 1.75) !important; 
        }
        .dashboard-second {
            font-size: var(--textXJumbo) !important; 
        }
        .dashboard-subtitle {
            font-size: var(--textXMD) !important; 
            padding: var(--spaceMini) var(--spaceSM);
            margin-top: var(--spaceMini);
        }
    }    
</style>

<div class="row g-2 mb-3" id="dashboard-holder">
    <div class="col-4" id="total_item-section">
        <h3 class="dashboard-title" id="total_item"></h3>
        <h3 class="dashboard-subtitle">Total Item</h3>
    </div>
    <div class="col-4" id="total_fav-section">
        <h3 class="dashboard-title" id="total_fav"></span></h3>
        <h3 class="dashboard-subtitle">Favorite Item</h3>
    </div>
    <div class="col-4" id="total_low-section">
        <h3 class="dashboard-title" id="total_low"></h3>
        <h3 class="dashboard-subtitle">Low Capacity</h3>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12 pt-2 d-flex align-items-center justify-content-center text-center" id="last_added-section">
        <div>
            <h6 class="dashboard-subtitle d-md-none">Last Added</h6>
            <h3 class="dashboard-second" id="last_added"></h3>
            <h3 class="dashboard-subtitle d-none d-md-block">Last Added</h3>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12 pt-2 d-flex align-items-center justify-content-center text-center" id="most_category_total-section">
        <div>
            <h6 class="dashboard-subtitle d-md-none">Most Category</h6>
            <h3 class="dashboard-second">(<span id="most_category_total"></span>) <span id="most_category_context"></span></h3>
            <h3 class="dashboard-subtitle d-none d-md-block">Most Category</h3>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12 pt-2 d-flex align-items-center justify-content-center text-center mx-auto" id="highest_price_name-section">
        <div>
            <h6 class="dashboard-subtitle d-md-none">Most Expensive</h6>
            <h3 class="dashboard-second">(<span id="highest_price_name"></span>) <span id="highest_price"></span></h3>
            <h3 class="dashboard-subtitle d-none d-md-block">Most Expensive</h3><br>
        </div>
    </div>
</div>

<script>
    const get_dashboard = () => {
        $.ajax({
            url: `/api/v1/stats/dashboard`,
            type: 'GET',
            beforeSend: function (xhr) {
                Swal.showLoading()
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
            },
            success: function(response) {
                Swal.close()
                const data = response.data
                $('#total_item').html(data.total_item)
                $('#total_fav').html(data.total_fav)
                $('#total_low').html(data.total_low)
                $('#last_added').html(data.last_added ?? '-')
                $('#most_category_total').html(data.most_category ? data.most_category.total : '-')
                $('#most_category_context').html(data.most_category ? data.most_category.context : '-')
                $('#highest_price_name').html(data.highest_price ? data.highest_price.inventory_name : '-')
                $('#highest_price').html(data.highest_price ? `Rp. ${data.highest_price.inventory_price.toLocaleString()}` : '-')
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                if(response.status != 404){
                    generate_api_error(response, true)
                } else {
                    template_alert_container('dashboard-holder', 'no-data', "No stats found to show", null, '<i class="fa-solid fa-warehouse"></i>')
                }
            }
        });
    }
    get_dashboard()
</script>