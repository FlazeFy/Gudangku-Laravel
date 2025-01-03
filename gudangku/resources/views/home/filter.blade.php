<div class='control-panel <?php if(!$isMobile){ echo 'position-sticky'; } ?>' style='<?php if(!$isMobile){ echo 'top:var(--spaceMD)'; } ?>'>
    <div class="position-relative py-2">
        <a class="fw-bold" style='font-size:var(--textXJumbo);' data-bs-toggle="collapse" href="#collapseControl">Control Panel</a>
        <div class="mt-1 mb-2 row collapse show" id="collapseControl">
            <div class="col-lg-4 col-md-6 col-sm-12">
                <label>Search by Name or Merk</label>
                <div class="position-relative">
                    <input class="form-control" id="search_by_name_merk" value="<?= $search_key ?>" onkeydown="return submitOnEnter(event)">
                    <span id='reset_search_btn_holder'></span>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <label>Search by Category</label>
                <div class="position-relative">
                    <select class="form-select" aria-label="Default select example" id='search_by_category'>
                        <option value="all" <?= ($filter_category == 'all') ? 'selected':'' ?>>All</option>
                        <option value="deleted" <?= ($filter_category == 'deleted') ? 'selected':'' ?>>Deleted</option>
                        <option value="favorite" <?= ($filter_category == 'favorite') ? 'selected':'' ?>>Favorite</option>
                        <option value="reminder" <?= ($filter_category == 'reminder') ? 'selected':'' ?>>Reminder</option>
                        <hr>
                    </select>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <label>Sorting</label>
                <div class="position-relative">
                    <select class="form-select" aria-label="Default select example" id='sorting'>
                        <option value="desc_name" <?= ($sorting == 'desc_name') ? 'selected':'' ?>>Descending by Name</option>
                        <option value="asc_name" <?= ($sorting == 'asc_name') ? 'selected':'' ?>>Ascending by Name</option>
                        <hr>
                        <option value="desc_price" <?= ($sorting == 'desc_price') ? 'selected':'' ?>>Descending by Price</option>
                        <option value="asc_price" <?= ($sorting == 'asc_price') ? 'selected':'' ?>>Ascending by Price</option>
                        <hr>
                        <option value="desc_created" <?= ($sorting == 'desc_created') ? 'selected':'' ?>>Descending by Created Date</option>
                        <option value="asc_created" <?= ($sorting == 'asc_created') ? 'selected':'' ?>>Ascending by Created Date</option>
                        <hr>
                        <option value="desc_updated" <?= ($sorting == 'desc_updated') ? 'selected':'' ?>>Descending by Updated Date</option>
                        <option value="asc_updated" <?= ($sorting == 'asc_updated') ? 'selected':'' ?>>Ascending by Updated Date</option>
                    </select>
                </div>
            </div>
        </div>
        <div id="total-holder">
            <h5 class='fw-bold' style='font-size:var(--textXLG);'>Showing</h5>
            <h2 class='text-primary fw-bold' style='font-size:calc(var(--textXLG)*2);'><span id='total-item'>0</span> Items</h2>
        </div>
    </div>
</div>

<script>
    const place_reset_btn = () => {
        if(search_key != ''){
            $('#reset_search_btn_holder').html(`
                <a class='btn bg-danger position-absolute' href='/inventory' style='right:10px; top:13px; font-size:var(--textLG); height:var(--spaceXLG); width:var(--spaceJumbo);'>
                    <i class="fa-solid fa-xmark position-absolute" style='margin-top:-7px; margin-left:-6px;'></i>
                </a>
            `)
        }
    }
    const fetch_dct = async () => {
        const list_cat = await get_dct_by_type('inventory_category')
        list_cat.forEach(el => {
            $('#search_by_category').append(`<option value='${el}' ${el == filter_category && 'selected'}>${el}</option>`)
        });
    }
    place_reset_btn()
    fetch_dct()

    const search_by_name_merk = (val) => {
        const url = new URL(window.location)
        const curr_page = url.href.replace(url.origin, "")

        if(val != null && val.trim() != "" ){
            const search_val = val.trim()
            url.searchParams.set('search_key', search_val)
            search_key = search_val
            window.history.pushState({ path: url.href }, '', url.href)
            place_reset_btn()
        } else {
            if(curr_page != "/inventory"){
                window.location.href = '/inventory'
            }
        }

        if((curr_page != "/inventory" && (val == null || val.trim() == "")) || (val != null && val.trim() != "")){
            get_inventory(page,search_key,filter_category,sorting)
        }
    }
    const submitOnEnter = (event) => {
        if (event.keyCode === 13) { 
            event.preventDefault() 
            search_by_name_merk($('#search_by_name_merk').val())
            return false 
        }
        return true 
    }
    $(document).on('blur', '#search_by_name_merk',function(){
        search_by_name_merk($(this).val())
    })

    $(document).on('change', '#search_by_category',function(){
        if($(this).val() != 'all'){
            const url = new URL(window.location)
            const search_val = $(this).val()
            url.searchParams.set('filter_category', search_val)
            filter_category = search_val
            window.history.pushState({ path: url.href }, '', url.href)
        } else {
            window.location.href = '/inventory'
        }
        get_inventory(page,search_key,filter_category,sorting)
    })
    $(document).on('change', '#sorting',function(){
        if($(this).val()){
            const url = new URL(window.location)
            const search_val = $(this).val()
            url.searchParams.set('sorting', search_val)
            sorting = search_val
            window.history.pushState({ path: url.href }, '', url.href)
        } else {
            window.location.href = '/inventory'
        }
        get_inventory(page,search_key,filter_category,sorting)
    })
</script>