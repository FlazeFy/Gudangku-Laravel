<div class='control-panel'>
    <div class="position-relative py-2">
        <a class="fw-bold" style='font-size:var(--textXJumbo);' data-bs-toggle="collapse" href="#collapseControlLend">Lended Item</a>
        <div class="mt-1 mb-2 collapse show" id="collapseControlLend">
            <div class="row" id="lended-item-holder"></div>
        </div>
    </div>
</div>

<script>
    const get_lend_item = (list_inventory) => {
        $('#lended-item-holder').empty()
        list_inventory.forEach(el => {
            $('#lended-item-holder').append(`
                <h6 class="fw-bold mb-1 mt-3 bg-success rounded-pill px-3 py-2 ms-2" style="width: fit-content; font-size:var(--textXMD);">${el.inventory_category}</h6>
                <p>${el.list_inventory}</p>
            `)
        });
    }
</script>