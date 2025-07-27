<a class="btn btn-primary mb-3 me-2" id="selected-inventory-modal-button" data-bs-target="#modalBorrowInventory" data-bs-toggle="modal"><i class="fa-solid fa-cart-shopping" style="font-size:var(--textXLG);"></i> <b id="total-item-selected">0</b> @if(!$isMobile) Item Selected @endif</a>
<div class="modal fade" id="modalBorrowInventory" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fw-bold" id="exampleModalLabel">Borrow From <span class="inventory-owner"></span></h2>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div id="selected-inventory-holder"></div>
                <a class="btn btn-success mt-4 w-100" id="save-borrow-button"><i class="fa-solid fa-floppy-disk"></i> Save</a>
                <div id="reset-button-holder"></div>
            </div>
        </div>
    </div>
</div>

<script>
    const post_borrow = (lend_id) => {

    }
    const get_selected_inventory_modal = () => {
        const selected = get_selected_items()
        $('#selected-inventory-holder').empty()

        if(selected.length > 0){
            $('#reset-button-holder').html(`
                <a class="btn btn-danger mt-3 w-100" id="save-borrow-button"><i class="fa-solid fa-rotate-left"></i> Reset All</a>
            `)
            selected.forEach(el => {
                $('#selected-inventory-holder').append(`
                    <button class="btn-feature mb-2 text-start" style="padding:var(--spaceXMD) !important;">
                        <h2 style="font-size:var(--textXLG);">${el.inventory_name}</h2>
                        <div class="mt-2 d-flex text-start props-box">
                            <span style="background: var(--successBG);" class="py-1 px-2 me-1 rounded d-inline-flex align-items-center">${el.inventory_category}</span>
                            <span style="background: var(--primaryColor);" class="py-1 px-2 me-1 rounded d-inline-flex align-items-center">${el.inventory_room}</span>
                        </div>
                    </button>
                `)
            });        
        } else {
            $('#selected-inventory-holder').html(`<p class="text-white text-center">- No Items Selected -</p>`)
        }
    }

    $(document).on('click','#save-borrow-button', function() {
        post_borrow(lend_id)
    })
    $(document).on('click','#selected-inventory-modal-button',function(){
        get_selected_inventory_modal()
    })
    $(document).on('click','#reset-button-holder',function(){
        localStorage.removeItem(SELECTED_STORAGE_KEY)
        $('#reset-button-holder').empty()
        $('.inventory-item').css('border-color', 'var(--primaryColor)')
        get_selected_inventory_modal()
        get_cart_button()
    })
</script>