const template_alert_container = (target, type, msg, btn_title, icon, href) => {
    $(`#${target}`).html(`
        <div class="container p-3" style="${type == 'no-data'? 'background-color:rgba(59, 131, 246, 0.2);':''}">
            <div class="d-flex justify-content-start">
                <div class="me-3">
                    <h1 style="font-size: 70px;">${icon}</h1>
                </div>
                <div>
                    <h4>${msg}</h4>
                    ${btn_title != null ? `<a class="btn btn-primary mt-3" href=${href}><i class="${type == 'no-data'? 'fa-solid fa-plus':''}"></i> ${ucEachWord(btn_title)}</a>`:''}
                </div>
            </div>
        </div>
    `)
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
                                                <th>Edit</th>
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

const generate_map_room = (target,data,is_interact,room) => {
    let rows
    let letters
    let cols

    if(data){
        const floor_range = generate_floor_range(data)
        rows = floor_range.rows
        letters = floor_range.letters
        cols = floor_range.cols
    } else {
        rows = 5
        letters = 'ABCDE'
        cols = 5
    }

    for (let row = 1; row <= rows; row++) {
        const rowContainer = $('<div class="row"></div>')
        for (let col = 0; col < cols; col++) {
            const label = `${letters[col]}${row}`
            let used = false
            let inventory_storage = null
            let storage_desc = null
            let id = null

            if(data){
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
            }

            const modal = is_interact ? generate_modal_detail(inventory_storage, storage_desc, room, label, id) : ''
            const button = $(`
                <button class='d-inline-block room-floor ${used ? 'active':''}' data-bs-toggle="modal" data-bs-target="#modalDetail-${label}" ${inventory_storage && `onclick="get_inventory_room_storage('${room}','${inventory_storage}','${label}')"`}>
                    <h6 class='coordinate'>${label}</h6>
                </button>
                ${modal}
            `)
            rowContainer.append(button)
        }
        $(target).append(rowContainer)
    }

    if(is_interact){
        $(target).append(`
            <div class='floor-config'>
                <a class='d-inline-block btn-layout-config btn btn-success' onclick='expand_floor()'><i class="fa-solid fa-up-right-and-down-left-from-center"></i> Expand</a>
                ${data && `<a class='d-inline-block btn-layout-config btn btn-success' href='/doc/layout/${room}'><i class="fa-solid fa-print"></i> Print</a>`}
                ${data && `<a class='d-inline-block btn-layout-config btn btn-success' href='/doc/layout/${room}/custom'><i class="fa-solid fa-pen-to-square"></i> Custom Print</a>`}
            </div>
        `)
    }
}