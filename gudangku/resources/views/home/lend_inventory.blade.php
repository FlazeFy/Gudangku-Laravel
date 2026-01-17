<div id="lend-item-section"></div>

<script>
    const getLendItem = (list_inventory) => {
        let item_el = ''
        $('#lend-item-section').empty()
        
        if(list_inventory.length > 0){
            list_inventory.forEach(el => {
                item_el += `
                    <h6 class="fw-bold mb-1 mt-3 bg-success rounded-pill px-3 py-2 ms-2" style="width: fit-content; font-size:var(--textXMD);">${el.inventory_category}</h6>
                    <p>${el.list_inventory}</p>
                `
            })

            $('#lend-item-section').html(`
                <div class='control-panel'>
                    <div class="position-relative py-2">
                        <a class="fw-bold" style='font-size:var(--textXJumbo)' data-bs-toggle="collapse" href="#collapseControlLend">Lended Item</a>
                        <div class="mt-1 mb-2 collapse show" id="collapseControlLend">
                            <div class="row">${item_el}</div>
                        </div>
                    </div>
                </div>
            `)
        }
    }
</script>