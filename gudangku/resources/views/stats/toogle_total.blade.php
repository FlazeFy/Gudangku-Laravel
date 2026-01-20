<div class='control-panel <?php if(!$isMobile){ echo 'position-sticky'; } ?>' style='<?php if(!$isMobile){ echo 'top:var(--spaceMD);'; } ?>'>
    <a class="fw-bold bg-transparent" style='font-size:var(--textXJumbo)' data-bs-toggle="collapse" href="#collapseControl">Control Panel<a>
    <div class="mt-1 mb-2 row collapse show" id="collapseControl">
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <form action="/stats/toogleView" method="POST" id="toogle_view_stats_select">
                @csrf
                <label>Chart Type</label>
                <select class="form-select" id="toogle_view" name="toogle_view" onchange="this.form.submit()">
                    @php($selected = session()->get('toogle_view_stats'))
                    <option value="top chart" <?php if($selected == 'top chart'){ echo 'selected'; }?>>Top Chart</option>
                    <option value="periodic chart" <?php if($selected == 'periodic chart'){ echo 'selected'; }?>>Periodic Chart</option>
                    <option value="most expensive" <?php if($selected == 'most expensive'){ echo 'selected'; }?>>Most Expensive</option>
                    <option value="tree distribution map" <?php if($selected == 'tree distribution map'){ echo 'selected'; }?>>Tree Distribution Map</option>
                    <option value="used percentage" <?php if($selected == 'used percentage'){ echo 'selected'; }?>>Used Percentage</option>
                </select>
            </form>
        </div>
        @if(session()->get('toogle_view_stats') == "top chart")
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
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
        @endif
        @if(session()->get('toogle_view_stats') == "periodic chart")
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <form action="/stats/toogleYear" method="POST" id="toogle_year_select">
                @csrf
                <label>Select Year</label>
                <select class="form-select" id="toogle_year" name="toogle_year"></select>
            </form>
        </div>
        @endif
        @if(session()->get('toogle_view_stats') == "tree distribution map")
        <div class="col-lg-6 col-md-6 col-sm-12 col-12">
            <label>Set Layout</label><br>
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-primary" id="layoutTop">Top</button>
                <button class="btn btn-primary" id="layoutBottom">Bottom</button>
                <button class="btn btn-primary" id="layoutLeft">Left</button>
                <button class="btn btn-primary" id="layoutRight">Right</button>
                <button class="btn btn-primary" id="fitScreen">Fit</button>
            </div>
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
        })
        $('#toogle_total_view_select').submit()
    })

    $(document).on('change','#toogle_year',function(){
        const keys = ['total_inventory_created_per_month_temp','total_report_created_per_month_temp','total_report_spending_per_month_temp','total_report_used_per_month_temp']
        keys.forEach(dt => {
            localStorage.removeItem(dt)
            localStorage.removeItem(`last-hit-${dt}`) 
        })
        $('#toogle_year_select').submit()
    })
    
    getAvailableYear(token,'toogle_year',<?= session()->get('toogle_select_year') ?>)
</script>