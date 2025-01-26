<div class='control-panel <?php if(!$isMobile){ echo 'position-sticky'; } ?>' style='<?php if(!$isMobile){ echo 'top:var(--spaceMD);'; } ?>'>
    <a class="fw-bold" style='font-size:var(--textXJumbo);' data-bs-toggle="collapse" href="#collapseControl">Control Panel<a>
    <div class="mt-1 mb-2 row collapse show" id="collapseControl">
        <div class="col-lg-4 col-md-6 col-sm-12">
            <form action="/stats/toogleView" method="POST" id="toogle_view_stats_select">
                @csrf
                <label>Chart Type</label>
                <select class="form-select" id="toogle_view" name="toogle_view" onchange="this.form.submit()">
                    @php($selected = session()->get('toogle_view_stats'))
                    <option value="top chart" <?php if($selected == 'top chart'){ echo 'selected'; }?>>Top Chart</option>
                    <option value="periodic chart" <?php if($selected == 'periodic chart'){ echo 'selected'; }?>>Periodic Chart</option>
                </select>
            </form>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-12">
            <form action="/stats/toogleTotal" method="POST" id="toogle_total_view_select">
                @csrf
                <label>Toogle Total</label>
                <select class="form-select" id="toogle_total" name="toogle_total">
                    @php($selected = session()->get('toogle_total_stats'))
                    <option value="item" <?php if($selected == 'item'){ echo 'selected'; }?>>Total By Item</option>
                    <option value="price" <?php if($selected == 'price'){ echo 'selected'; }?>>Total By Price</option>
                </select>
            </form>
        </div>
        @if(session()->get('toogle_view_stats') == "periodic chart")
        <div class="col-lg-4 col-md-6 col-sm-12">
            <form action="/stats/toogleYear" method="POST" id="toogle_year_select">
                @csrf
                <label>Select Year</label>
                <select class="form-select" id="toogle_year" name="toogle_year"></select>
            </form>
        </div>
        @endif
    </div>
</div>

<script>
    $(document).on('change','#toogle_total',function(){
        const keys = ['total_inventory_by_category_temp','total_inventory_by_favorite_temp','total_inventory_by_room_temp']
        keys.forEach(dt => {
            localStorage.removeItem(dt)
            localStorage.removeItem(`last-hit-${dt}`) 
        });
        $('#toogle_total_view_select').submit()
    })
    $(document).on('change','#toogle_year',function(){
        const keys = ['total_inventory_created_per_month_temp','total_report_created_per_month_temp','total_report_spending_per_month_temp','total_report_used_per_month_temp']
        keys.forEach(dt => {
            localStorage.removeItem(dt)
            localStorage.removeItem(`last-hit-${dt}`) 
        });
        $('#toogle_year_select').submit()
    })
    
    const get_available_year = () => {
        Swal.showLoading()
        $.ajax({
            url: `/api/v1/user/my_year`,
            type: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
            },
            success: function(response) {
                Swal.close()
                const data = response.data
                const selected_year = <?= session()->get('toogle_select_year') ?>;

                data.forEach(el => {
                    $('#toogle_year').append(`<option value="${el.year}" ${selected_year == el.year ? 'selected' :''}>${el.year}</option>`) 
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                Swal.fire({
                    title: "Oops!",
                    text: "Failed to get available year",
                    icon: "error"
                });
            }
        });
    }
    get_available_year()
</script>