<style>
    #room-container {
        height: 75vh;
        width: 100%;
        border: var(--spaceMini) solid var(--primaryColor);
        border-radius: var(--roundedLG);
        overflow: auto;
        display: block;
    }
    .row {
        display: flex;
    }
    .room-floor {
        border-radius: 0 !important;
        width: 60px;
        height: 60px;
        text-align: center;
        border: 0.5px solid var(--whiteColor) !important;
        position: relative;
    }
    .room-floor:hover {
        background: var(--successBG);
    }
    .room-floor .coordinate {
        font-size: var(--textSM);
        font-weight: 600;
        position: absolute;
        bottom: 5px;
        right: 5px;
    }
    .room-floor.active {
        background: var(--primaryColor);
    }
    table td {
        padding: var(--textXSM) !important;
    }
    table td, table th {
        vertical-align: middle;
    }
</style>

<div id="room-container"></div>

<script>
    const room = '<?= session()->get('room_opened') ?>'

    const get_room_layout = () => {
        Swal.showLoading()
        $.ajax({
            url: `/api/v1/inventory/layout/${room}`,
            type: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
            },
            success: function(response) {
                Swal.close()
                const data = response.data
               
                generate_map_room(data)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                Swal.fire({
                    title: "Oops!",
                    text: "Failed to get the layout",
                    icon: "error"
                });
            }
        });
    }
    get_room_layout()

    const get_inventory_room_storage = (room,storage,target) => {
        $(`#${target} tbody`).empty()
        if(storage != '' && storage){
            Swal.showLoading()
            $.ajax({
                url: `/api/v1/inventory/search/by_room_storage/${room}/${storage}`,
                type: 'GET',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader("Accept", "application/json")
                    xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
                },
                success: function(response) {
                    Swal.close()
                    const data = response.data
                    data.forEach(dt => {
                        $(`#${target} tbody`).append(`
                            <tr>
                                <td>${dt.inventory_name}</td>
                                <td class='text-center'>${dt.inventory_category}</td>
                                <td>Rp. ${number_format(dt.inventory_price, 0, ',', '.')}</td>
                                <td class='text-center'>${dt.inventory_vol} ${dt.inventory_unit}</td>
                            </tr>
                        `)
                    });
                },
                error: function(response, jqXHR, textStatus, errorThrown) {
                    $(`#${target} tbody`).append(`
                        <tr>
                            <td colspan='4' class='text-secondary fst-italic text-center'>- No inventory to show -</td>
                        </tr>
                    `)
                    Swal.close()
                    Swal.fire({
                        title: "Oops!",
                        text: "Failed to get the inventory",
                        icon: "error"
                    });
                }
            });
        }
    }

    const generate_map_room = (data) => {
        const rows = 10
        const cols = 26 
        const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
        
        for (let row = 1; row <= rows; row++) {
            const rowContainer = $('<div class="row"></div>')
            for (let col = 0; col < cols; col++) {
                const label = `${letters[col]}${row}`
                let used = false
                let inventory_storage = null
                let storage_desc = null
                data.forEach(dt => {
                    const coor = dt.layout.split(':')
                    coor.forEach(cr => {
                        if(cr == `${letters[col]}${row}`){
                            used = true
                            inventory_storage = dt.inventory_storage
                            storage_desc = dt.storage_desc
                        }
                    });
                });

                const button = $(`
                    <button class='d-inline-block room-floor ${used ? 'active':''}' data-bs-toggle="modal" data-bs-target="#modalDetail-${letters[col]}${row}" onclick="get_inventory_room_storage('${room}','${inventory_storage}','table-inventory-${letters[col]}${row}')">
                        <h6 class='coordinate'>${label}</h6>
                    </button>
                    <div class="modal fade" id="modalDetail-${letters[col]}${row}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h2 class="modal-title fw-bold" id="exampleModalLabel">Coordinate ${letters[col]}${row}</h2>
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                                </div>
                                <div class="modal-body">
                                    <div class='row'>
                                        <div class='col'>
                                            <label>Name</label>
                                            <input type="text" name="inventory_storage" class="form-control my-2" value='${inventory_storage ?? ''}'/>
                                            <label>Description</label>
                                            <textarea name="inventory_desc" class="form-control mt-2">${storage_desc ?? ''}</textarea>
                                        </div>
                                        <div class='col'>
                                            <table id='table-inventory-${letters[col]}${row}' class='table'>
                                                <thead>
                                                    <tr class='text-center'>
                                                        <th>Name</th>
                                                        <th>Category</th>
                                                        <th>Price</th>
                                                        <th>Unit & Volume</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>                                    
                                </div>
                            </div>
                        </div>
                    </div>
                `)
                rowContainer.append(button)
            }
            $('#room-container').append(rowContainer)
        }
    }
</script>