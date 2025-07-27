<div id="inventory-holder" class="row"></div>
<div id="inventory-holder-navigation"></div>

<script>
    const SELECTED_STORAGE_KEY = `selected_lend_items_${lend_id}`

    const get_selected_items = () => {
        return JSON.parse(localStorage.getItem(SELECTED_STORAGE_KEY)) || []
    }

    const save_selected_items = (items) => {
        localStorage.setItem(SELECTED_STORAGE_KEY, JSON.stringify(items))
    }

    const toggle_select_item = (itemId, buttonEl) => {
        $(document).ready(function () {
            let selected = get_selected_items()
            const isSelected = selected.includes(itemId)

            if (isSelected) {
                selected = selected.filter(id => id !== itemId)
                buttonEl.style.borderColor = 'var(--primaryColor)'
            } else {
                selected.push(itemId);
                buttonEl.style.borderColor = 'var(--successBG)' 
            }

            save_selected_items(selected)
        })
    }

    let page = 1
    const get_lend_inventory = (lend_id,page) => {
        Swal.showLoading();
        $.ajax({
            url: `/api/v1/lend/inventory/${lend_id}?per_page_key=18&page=${page}`,
            type: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
            },
            success: function(response) {
                Swal.close()

                const data = response.data.data
                const current_page = response.data.current_page
                const total_page = response.data.last_page

                const selectedItems = get_selected_items()
                data.forEach(el => {
                    const isSelected = selectedItems.includes(el.id)
                    const buttonStyle = isSelected ? "border-color: var(--successBG) !important;" : ""

                    $('#inventory-holder').append(`
                        <div class='col-xl-3 col-lg-4 col-md-6 col-sm-12'>
                            <button class="btn-feature mb-4 position-relative inventory-item" style="${buttonStyle}" data-id="${el.id}">
                                ${el.inventory_image ? `<img class="img img-fluid" style="border-radius: var(--roundedMD);" src="${el.inventory_image}" title="${el.inventory_image}">` : `<i class="fa-solid fa-box" style="font-size:90px;"></i>`}
                                <h2 class="mt-3" style="font-size:var(--textXLG);">${el.inventory_name}</h2>
                                <div class="mt-3 d-flex justify-content-center props-box">
                                    <span style="background: var(--primaryColor);" class="py-1 px-2 me-1 rounded d-inline-flex align-items-center">${el.inventory_vol} ${el.inventory_unit}</span>
                                </div>
                                <h6 class='date-text mt-2'>Created At : ${getDateToContext(el.created_at, 'calendar')}</h6>
                            </button>
                        </div>
                    `);
                });

                $('#inventory-holder-navigation').html(`<a class='btn btn-primary d-block mx-auto mt-4' id='see-more-button' style='max-width:140px;'>See More</a>`)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                
                if (response.status === 404) {
                    if(page != 1){
                        generate_last_page_error()
                        return
                    }
                    const json = JSON.parse(response.responseText)
                    const message = json.message
                    $('#inventory-holder').html(`<span class="fst-italic text-white text-center">- ${ucFirst(message)} -</span>`)
                } else {
                    generate_api_error(response, true)
                }
            }
        })
    }

    $('#inventory-holder').empty()
    get_lend_inventory(lend_id,page)

    $(document).on('click','.inventory-item', function() {
        const id = $(this).data('id')
        toggle_select_item(id, this)
    })
    $(document).on('click','#see-more-button', function() {
        page++
        get_lend_inventory(lend_id,page)
    })
</script>