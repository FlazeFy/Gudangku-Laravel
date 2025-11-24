<style>
    .dashboard-title, .dashboard-subtitle {
        text-align: center;
    }
    .dashboard-title {
        font-size: calc(var(--textXJumbo) * 3) !important; 
        font-weight: bold;
    }
    .dashboard-subtitle {
        font-size: var(--textXLG) !important; 
        font-weight: 600;
        background: var(--infoBG);
        padding: var(--spaceXSM) var(--spaceXMD);
        width: fit-content;
        margin-inline: auto;
        display: block;
        border-radius: var(--roundedXLG);
        margin-top: var(--spaceXSM);
    }
</style>

<div class="row mb-3">
    <div class="col-lg-4 col-md-6 col-sm-12" id='total_item-section'>
        <h2 class="dashboard-title"><span id='total_item'></span> @if($isMobile) <span style="font-size:var(--textJumbo)">Total Item</span> @endif</h2>
        @if(!$isMobile)
            <h2 class="dashboard-subtitle">Total Item</h2>
        @endif
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12" id='total_fav-section'>
        <h2 class="dashboard-title"><span id='total_fav'></span> @if($isMobile) <span style="font-size:var(--textJumbo)">Favorite Item</span> @endif</h2>
        @if(!$isMobile)
            <h2 class="dashboard-subtitle">Favorite Item</h2>
        @endif
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12" id='total_low-section'>
        <h2 class="dashboard-title"><span id='total_low'></span> @if($isMobile) <span style="font-size:var(--textJumbo)">Low Capacity</span> @endif</h2>
        @if(!$isMobile)
            <h2 class="dashboard-subtitle">Low Capacity</h2>
        @endif
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12 pt-4 d-flex align-items-center justify-content-center text-center" id='last_added-section'>
        <div>
            @if($isMobile)
                <h6 class="dashboard-subtitle" style="font-size:var(--textJumbo) !important;">Last Added</h6>
            @endif
            <h2 class="text-center fw-bold" style="font-size: calc(var(--textXJumbo) * 1.2) !important;"><span id='last_added'></span></h2>
            @if(!$isMobile)
                <h2 class="dashboard-subtitle">Last Added</h2>
            @endif
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12 pt-4 py-2 d-flex align-items-center justify-content-center text-center" id='most_category_total-section'>
        <div>
            @if($isMobile)
                <h6 class="dashboard-subtitle" style="font-size:var(--textJumbo) !important;">Most Category</h6>
            @endif
            <h2 class="text-center fw-bold" style="font-size: calc(var(--textXJumbo) * 1.2) !important;">
                (<span id='most_category_total'></span>) <span id='most_category_context'></span>
            </h2>
            @if(!$isMobile)
                <h2 class="dashboard-subtitle">Most Category</h2>
            @endif
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12 pt-4 d-flex align-items-center justify-content-center text-center" id='highest_price_name-section'>
        <div>
            @if($isMobile)
                <h6 class="dashboard-subtitle" style="font-size:var(--textJumbo) !important;">Highest Price</h6>
            @endif
            <h2 class="text-center fw-bold" style="font-size: calc(var(--textXJumbo) * 1.2) !important;">(<span id='highest_price_name'></span>) <span id='highest_price'></span> </h2>
            @if(!$isMobile)
                <h2 class="dashboard-subtitle">Highest Price</h2>
            @endif
        </div>
    </div>
</div><br>

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
                $('#highest_price').html(data.highest_price ? `Rp. ${number_format(data.highest_price.inventory_price, 0, ',', '.')}` : '-')
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                generate_api_error(response, true)
            }
        });
    }
    get_dashboard()
</script>