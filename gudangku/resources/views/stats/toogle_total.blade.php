<div class='control-panel <?php if(!$isMobile){ echo 'position-sticky'; } ?>' style='<?php if(!$isMobile){ echo 'top:var(--spaceMD);'; } ?>'>
    <h4 class="fw-bold" style='font-size:var(--textXJumbo);'>Control Panel</h4>
    <div class="mt-1 mb-2 row">
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
</script>