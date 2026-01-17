<div id="inventory-holder" class="row"></div>
<div id="inventory-holder-navigation"></div>

<script>
    let page = 1

    const toggle_select_item = (inventory, buttonEl) => {
        $(document).ready(function () {
            let selected = getSelectedItems()
            const isSelected = selected.some(item => item.id === inventory.id)

            if (isSelected) {
                selected = selected.filter(item => item.id !== inventory.id)
                buttonEl.style.borderColor = 'var(--primaryColor)'
            } else {
                selected.push(inventory)
                buttonEl.style.borderColor = 'var(--successBG)'
            }

            saveSelectedItems(selected)
        })
    }

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
                const owner = response.owner

                $('.inventory-owner').text(`@${owner.username}'s`)

                const selectedItems = getSelectedItems()
                data.forEach(el => {
                    const isSelected = selectedItems.includes(el.id)
                    const buttonStyle = isSelected ? "border-color: var(--successBG) !important;" : ""
                    const inventory = {
                        id : el.id,
                        inventory_name : el.inventory_name,
                        inventory_category : el.inventory_category,
                        inventory_room : el.inventory_room,
                    }

                    $('#inventory-holder').append(`
                        <div class='col-xl-3 col-lg-4 col-md-6 col-sm-12'>
                            <button class="btn-feature mb-4 position-relative inventory-item" style="${buttonStyle}" data-inventory="${encodeURIComponent(JSON.stringify(inventory))}">
                                ${el.inventory_image ? `<img class="img img-fluid" style="border-radius: var(--roundedMD);" src="${el.inventory_image}" title="${el.inventory_image}">` : `<i class="fa-solid fa-box" style="font-size:90px;"></i>`}
                                <h2 class="mt-3">${el.inventory_name}</h2>
                                <div class="mt-3 d-flex justify-content-center props-box">
                                    <span style="background: var(--successBG);" class="py-1 px-2 me-1 rounded d-inline-flex align-items-center">${el.inventory_category}</span>
                                    <span style="background: var(--primaryColor);" class="py-1 px-2 me-1 rounded d-inline-flex align-items-center">${el.inventory_vol} ${el.inventory_unit}</span>
                                </div>
                                <div class="mt-2 d-flex justify-content-center props-box">
                                    <span style="background: var(--warningBG);" class="py-1 px-2 me-1 rounded d-inline-flex align-items-center">${el.inventory_room}${el.inventory_storage ? ` - ${el.inventory_storage}`:''}</span>
                                </div>
                                <p class='date-text mt-2'>Created At : ${getDateToContext(el.created_at, 'calendar')}</p>
                            </button>
                        </div>
                    `);
                });

                $('#inventory-holder-navigation').html(`<a class='btn btn-primary d-block mx-auto mt-4' id='see-more-button' style='max-width:140px;'>See More</a>`)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                
                if (response.status === 404 || response.status === 400) {
                    if(page != 1){
                        generateLastPageError()
                        return
                    }
                    const json = JSON.parse(response.responseText)
                    const message = json.message
                    $('#selected-inventory-modal-button').remove()
                    $('#inventory-holder').html(`<span class="fst-italic text-white text-center">- ${ucFirst(message)} -</span>`)
                } else {
                    generateAPIError(response, true)
                }
            }
        })
    }

    $('#inventory-holder').empty()
    get_lend_inventory(lend_id,page)
    getCartButton()

    $(document).on('click', '.inventory-item', function () {
        const inventory = JSON.parse(decodeURIComponent($(this).attr('data-inventory')))
        toggle_select_item(inventory, this)
        getCartButton()
    })
    
    $(document).on('click','#see-more-button', function() {
        page++
        get_lend_inventory(lend_id,page)
    })
</script>