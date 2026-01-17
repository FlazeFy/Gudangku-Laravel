<div class='control-panel <?php if(!$isMobile){ echo 'position-sticky'; } ?>' style='<?php if(!$isMobile){ echo 'top:var(--spaceMD)'; } ?>'>
    <div class="position-relative pt-2">
        <a class="fw-bold bg-transparent" style='font-size:var(--textJumbo);' data-bs-toggle="collapse" href="#collapseControl">Control Panel</a>
        <div class="mt-1 mb-2 row collapse show" id="collapseControl">
            <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                <label>Search by Title</label>
                <div class="position-relative">
                    <input class="form-control" id="search_by_title" value="<?= $search_key ?>" onkeydown="return submitOnEnter(event)">
                    <span id='reset_search_btn_holder'></span>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                <label>Search by Category</label>
                <div class="position-relative">
                    <select class="form-select" aria-label="Default select example" id='report_category_holder'></select>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                <label>Sorting</label>
                <div class="position-relative">
                    <select class="form-select" aria-label="Default select example" id='sorting'>
                        <option value="desc_title" <?= ($sorting == 'desc_title') ? 'selected':'' ?>>Descending by Title</option>
                        <option value="asc_title" <?= ($sorting == 'asc_title') ? 'selected':'' ?>>Ascending by Title</option>
                        <hr>
                        <option value="desc_created" <?= ($sorting == 'desc_created') ? 'selected':'' ?>>Descending by Created Date</option>
                        <option value="asc_created" <?= ($sorting == 'asc_created') ? 'selected':'' ?>>Ascending by Created Date</option>
                    </select>
                </div>
            </div>
        </div>
        <div id="total-holder" class="mt-2">
            <p class='mb-0'>Showing</p>
            <h5 class='text-primary mb-0' style='font-size:calc(var(--textXLG)*1.5)'><span id='total-item'>0</span> Items</h5>
        </div>
    </div>
</div>

<script>
    const placeResetButton = () => {
        if(search_key != ''){
            $('#reset_search_btn_holder').html(`
                <a class='btn bg-danger position-absolute' href='/report' style='right:10px; top:13px; font-size:var(--textLG); height:var(--spaceXLG); width:var(--spaceJumbo);'>
                    <i class="fa-solid fa-xmark position-absolute" style='margin-top:-7px; margin-left:-6px;'></i>
                </a>
            `)
        }
    }
    
    $(async function () {
        await getDictionaryByContext('report_category',token,"<?= $filter_category ?? "all" ?>")
    })
    placeResetButton()

    const searchByTitle = (val) => {
        const url = new URL(window.location)
        const curr_page = url.href.replace(url.origin, "")

        if(val != null && val.trim() != "" ){
            const search_val = val.trim()
            url.searchParams.set('search_key', search_val)
            search_key = search_val
            window.history.pushState({ path: url.href }, '', url.href)
            placeResetButton()
        } else {
            if(curr_page != "/report"){
                window.location.href = '/report'
            }
        }

        if((curr_page != "/report" && (val == null || val.trim() == "")) || (val != null && val.trim() != "")){
            getAllReport(page,search_key,filter_category,sorting)
        }
    }

    const submitOnEnter = (event) => {
        if (event.keyCode === 13) { 
            event.preventDefault() 
            searchByTitle($('#search_by_title').val())
            return false 
        }
        return true 
    }

    $(document).on('blur', '#search_by_title',function(){
        searchByTitle($(this).val())
    })

    $(document).on('change', '#report_category_holder',function(){
        if($(this).val() != 'all'){
            const url = new URL(window.location)
            const search_val = $(this).val()
            url.searchParams.set('filter_category', search_val)
            filter_category = search_val
            window.history.pushState({ path: url.href }, '', url.href)
        } else {
            window.location.href = '/report'
        }
        
        getAllReport(page,search_key,filter_category,sorting)
    })

    $(document).on('change', '#sorting',function(){
        if($(this).val()){
            const url = new URL(window.location)
            const search_val = $(this).val()
            url.searchParams.set('sorting', search_val)
            sorting = search_val
            window.history.pushState({ path: url.href }, '', url.href)
        } else {
            window.location.href = '/report'
        }

        getAllReport(page,search_key,filter_category,sorting)
    })
</script>