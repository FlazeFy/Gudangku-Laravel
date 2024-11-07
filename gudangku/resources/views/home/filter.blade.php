<div class='container bordered w-100'>
    <h4 class="fw-bold" style='font-size:var(--textXJumbo);'>Control Panel</h4>
    <div class="mt-1 row">
        <div class="col-lg-4 col-md-6 col-sm-12">
            <label>Search by Name or Merk</label>
            <div class="position-relative">
                <input class="form-control" id="search_by_name_merk" value="<?= $search_key ?>">
                <span id='reset_search_btn_holder'></span>
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
    place_reset_btn()

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
        get_inventory(page,search_key)
    })
</script>