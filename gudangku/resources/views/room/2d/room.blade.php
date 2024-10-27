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
    .btn-layout-config{
        margin: var(--textXMD) 0 0 var(--textMD);
        font-size: var(--textXMD);
        font-weight: 500;
    }
</style>

<div id="room-container"></div>

<script>
    const room = '<?= session()->get('room_opened') ?>'

    const get_room_layout = () => {
        $(document).ready(function() {
            $('.modal').modal('hide')
        })
        Swal.showLoading()
        $.ajax({
            url: `/api/v1/inventory/layout/${room}`,
            type: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
            },
            success: function(response) {
                $('#room-container').empty()
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
        $(`#table-inventory-${target} tbody`).empty()
        $(`#pie-chart-${target}`).empty()
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
                    const stats = response.stats
                    data.forEach(dt => {
                        $(`#table-inventory-${target} tbody`).append(`
                            <tr>
                                <td>${dt.inventory_name}</td>
                                <td class='text-center'>${dt.inventory_category}</td>
                                <td>Rp. ${number_format(dt.inventory_price, 0, ',', '.')}</td>
                                <td class='text-center'>${dt.inventory_vol} ${dt.inventory_unit}</td>
                            </tr>
                        `)
                    });
                    if(stats){
                        generate_pie_chart('Category Distribution',`pie-chart-${target}`,stats)
                    }
                },
                error: function(response, jqXHR, textStatus, errorThrown) {
                    $(`#table-inventory-${target} tbody`).append(`
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

    const generate_floor_range = (data) => {
        let rawLetter = []
        let rawNum = [] 

        data.forEach(dt => {
            if (dt.layout) {
                const coor = dt.layout.split(':')
                coor.forEach(cr => {
                    const match = cr.match(/^([A-Z]+)(\d+)$/)
                    if (match) {
                        const [_, letters, numbers] = match
                        rawLetter.push(letters)
                        rawNum.push(numbers)
                    }
                })
            }
        })
        const highestLetter = rawLetter.reduce((max, current) => current > max ? current : max, "A")
        const highestNumber = Math.max(...rawNum)
        const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.slice(0, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.indexOf(highestLetter) + 2)
        
        return {
            letters:letters,
            rows:highestNumber + 1,
            cols:letters.length
        }
    }

    const generate_modal_detail = (storage, storage_desc, room, coor, id) => {
        if(storage){
            return `
                <div class="modal fade" id="modalDetail-${coor}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2 class="modal-title fw-bold" id="exampleModalLabel">Coordinate ${coor}</h2>
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                            <div class="modal-body">
                                <div class='row'>
                                    <div class='col'>
                                        <div id='pie-chart-${coor}'></div>
                                        <label>Name</label>
                                        <input type="text" name="inventory_storage" class="form-control" value='${storage ?? ''}'/>
                                        <label>Description</label>
                                        <textarea name="inventory_desc" class="form-control">${storage_desc ?? ''}</textarea>
                                        <div class='mt-3'>
                                            <input value='${id}_${coor}' class='id-coor-holder' hidden>
                                            <a class='btn btn-danger remove_coordinate'><i class="fa-solid fa-trash"></i> Remove Coordinate</a>
                                        </div>
                                    </div>
                                    <div class='col'>
                                        <table id='table-inventory-${coor}' class='table'>
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
            `
        } else {
            return `
                <div class="modal fade" id="modalDetail-${coor}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2 class="modal-title fw-bold" id="exampleModalLabel">Coordinate ${coor}</h2>
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                            <div class="modal-body">
                                <form id='add-storage-${coor}'>
                                    <input type="text" name="layout" value='${coor}' hidden required/>
                                    <label>Room</label>
                                    <input type="text" name="inventory_room" class="form-control" value='${room}' readonly required/>
                                    <label>Storage</label>
                                    <input type="text" name="inventory_storage" class="form-control" required/>
                                    <label>Description</label>
                                    <textarea name="storage_desc" class="form-control"></textarea>
                                    <a class='btn btn-success mt-4 w-100 submit_add_storage'><i class="fa-solid fa-floppy-disk"></i> Submit to Coordinate ${coor}</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            ` 
        }
    }

    const generate_map_room = (data) => {
        const floor_range = generate_floor_range(data)
        const rows = floor_range.rows
        let letters = floor_range.letters
        const cols = floor_range.cols
        
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
                        if(cr == label){
                            used = true
                            inventory_storage = dt.inventory_storage
                            storage_desc = dt.storage_desc
                            id = dt.id
                        }
                    });
                });

                const modal = generate_modal_detail(inventory_storage, storage_desc, room, label, id)
                const button = $(`
                    <button class='d-inline-block room-floor ${used ? 'active':''}' data-bs-toggle="modal" data-bs-target="#modalDetail-${label}" ${inventory_storage && `onclick="get_inventory_room_storage('${room}','${inventory_storage}','${label}')"`}>
                        <h6 class='coordinate'>${label}</h6>
                    </button>
                    ${modal}
                `)
                rowContainer.append(button)
            }
            $('#room-container').append(rowContainer)
        }
        $('#room-container').append(`
            <div class='floor-config'>
                <a class='d-inline-block btn-layout-config btn btn-success' onclick='expand_floor()'><i class="fa-solid fa-up-right-and-down-left-from-center"></i> Expand</a>
                <a class='d-inline-block btn-layout-config btn btn-success' href='/doc/layout/${room}'><i class="fa-solid fa-print"></i> Print</a>
            </div>
        `)
    }

    const post_storage = (form) => {
        $.ajax({
            url: '/api/v1/inventory/layout',
            type: 'POST',
            data: $(`#${form}`).serialize(),
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
            },
            success: function(response) {
                Swal.fire({
                    title: "Success!",
                    text: response.message,
                    icon: "success",
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        get_room_layout() 
                    }
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                generate_api_error(response, true)
            }
        });
    }

    const remove_coordinate = (id,coor) => {
        $.ajax({
            url: `/api/v1/inventory/delete_layout/${id}/${coor}`,
            type: 'DELETE',
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
            },
            success: function(response) {
                Swal.fire({
                    title: "Success!",
                    text: response.message,
                    icon: "success",
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        get_room_layout() 
                    }
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                generate_api_error(response, true)
            }
        });
    }

    const expand_floor = () => {
        let max_letter
        let max_number
        $('#room-container > .row').each(function(idx, el) {
            const coor = $(this).find('.coordinate').last().text()
            const match = coor.match(/^([A-Z]+)(\d+)$/)

            if (match) {
                const [_, letters, numbers] = match
                const letters_next = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.slice(0, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.indexOf(letters) + 2).slice(-1)
                max_letter = letters_next
                max_number = parseInt(numbers)
                const label = `${letters_next}${numbers}`

                const modal = generate_modal_detail(null, null, room, label, null)
                $(this).append(`
                    <button class='d-inline-block room-floor' data-bs-toggle="modal" data-bs-target="#modalDetail-${label}">
                        <h6 class='coordinate'>${label}</h6>
                    </button>
                    ${modal}
                `)
            }
        })

        const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.slice(0, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.indexOf(max_letter) + 1)
        const rowContainer = $('<div class="row"></div>')

        for (let idx = 0; idx < letters.length; idx++) {
            const label = `${letters[idx]}${max_number+1}`
            const modal = generate_modal_detail(null, null, room, label, null)
            rowContainer.append(`
                <button class='d-inline-block room-floor' data-bs-toggle="modal" data-bs-target="#modalDetail-${label}">
                    <h6 class='coordinate'>${label}</h6>
                </button>
                ${modal}
            `)
        }
        $('#room-container .floor-config').first().before(rowContainer)
    }

    $(document).ready(function() {
        $(document).on('click', '.submit_add_storage', function(event) {
            const form_id = $(this).closest('form').attr('id')
            post_storage(form_id)
        });

        $(document).on('click', '.remove_coordinate', function(event) {
            const idx = $(this).index('.remove_coordinate')
            const id_coor_holder = $('.id-coor-holder').eq(idx).val().split('_')
            const id = id_coor_holder[0]
            const coor = id_coor_holder[1]
            remove_coordinate(id,coor)
        })
    });
</script>