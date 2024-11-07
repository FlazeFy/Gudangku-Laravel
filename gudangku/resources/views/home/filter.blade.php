<div class='container bordered w-100 mb-4 bg-dark shadow <?php if(!$isMobile){ echo 'position-sticky'; } ?>' style='<?php if(!$isMobile){ echo 'top:var(--spaceMD)'; } ?>'>
    <h4 class="fw-bold" style='font-size:var(--textXJumbo);'>Control Panel</h4>
    <div class="mt-1 row">
        <div class="col-lg-4 col-md-6 col-sm-12">
            <label>Search by Name or Merk</label>
            <div class="position-relative">
                <input class="form-control" id="search_by_name_merk" value="<?= $search_key ?>">
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

    $(document).on('blur', '#search_by_name_merk',function(){
        if($(this).val() != null && $(this).val().trim() != "" ){
            const url = new URL(window.location)
            const search_val = $(this).val().trim()
            url.searchParams.set('search_key', search_val)
            search_key = search_val
            window.history.pushState({ path: url.href }, '', url.href)
            place_reset_btn()
        } else {
            window.location.href = '/inventory'
        }
        get_inventory(page,search_key,filter_category)
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
        get_inventory(page,search_key,filter_category)
    })
</script>