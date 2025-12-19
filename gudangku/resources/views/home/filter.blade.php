<div class='control-panel <?php if(!$isMobile){ echo 'position-sticky'; } ?>' style='<?php if(!$isMobile){ echo 'top:var(--spaceMD)'; } ?>'>
    <div class="position-relative pt-2">
        <a class="fw-bold bg-transparent" style='font-size:var(--textJumbo);' data-bs-toggle="collapse" href="#collapseControl">Control Panel</a>
        <div class="mt-1 mb-2 row collapse show" id="collapseControl">
            <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                <label>Search by Name or Merk</label>
                <div class="position-relative">
                    <input class="form-control" id="search_by_name_merk" value="<?= $search_key ?>" onkeydown="return submitOnEnter(event)">
                    <span id='reset_search_btn_holder'></span>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                <label>Search by Category</label>
                <div class="position-relative">
                    <select class="form-select" aria-label="Default select example" id='inventory_category_holder'>
                        <option value="all" <?= ($filter_category == 'all') ? 'selected':'' ?>>-</option>
                        <option value="deleted" <?= ($filter_category == 'deleted') ? 'selected':'' ?>>Deleted</option>
                        <option value="favorite" <?= ($filter_category == 'favorite') ? 'selected':'' ?>>Favorite</option>
                        <option value="reminder" <?= ($filter_category == 'reminder') ? 'selected':'' ?>>Reminder</option>
                        <hr>
                    </select>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6 col-12">
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
        <div id="total-holder" class="mt-2">
            <p class="mb-0">Showing</p>
            <h5 class='text-primary mb-0' style='font-size:calc(var(--textXLG)*1.5);'><span id='total-item'>0</span> Items</h5>
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

    place_reset_btn()

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

    $(document).on('change', '#inventory_category_holder',function(){
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