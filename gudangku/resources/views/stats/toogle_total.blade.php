<form action="/stats/toogleTotal" method="POST" id="toogle_total_view_select">
    @csrf
    <div class="form-floating">
        <select class="form-select" id="toogle_total" name="toogle_total">
            @php($selected = session()->get('toogle_total_stats'))
            <option value="item" <?php if($selected == 'item'){ echo 'selected'; }?>>Total By Item</option>
            <option value="price" <?php if($selected == 'price'){ echo 'selected'; }?>>Total By Price</option>
        </select>
        <label for="toogle_total">Toogle Total</label>
    </div>
</form>

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