<form action="/stats/toogleTotal" method="POST" id="toogle_total_view_select">
    @csrf
    <div class="form-floating">
        <select class="form-select" id="toogle_total" name="toogle_total" onchange="this.form.submit()">
            @php($selected = session()->get('toogle_total_stats'))
            <option value="item" <?php if($selected == 'item'){ echo 'selected'; }?>>Total By Item</option>
            <option value="price" <?php if($selected == 'price'){ echo 'selected'; }?>>Total Price</option>
        </select>
        <label for="toogle_total">Toogle Total</label>
    </div>
</form>