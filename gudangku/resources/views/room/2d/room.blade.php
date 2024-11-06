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
               
                generate_map_room('#room-container',data,true,room)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                if(response.status != 404){
                    Swal.fire({
                        title: "Oops!",
                        text: "Failed to get the layout",
                        icon: "error"
                    });
                } else {
                    generate_map_room('#room-container',null,true,room)
                }
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
                                <td><a class="btn btn-warning modal-btn" href="/inventory/edit/${dt.id}"><i class="fa-solid fa-pen-to-square" style="font-size:var(--textXLG);"></i></a></td>
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
                    if(response.status != 404){
                        Swal.fire({
                            title: "Oops!",
                            text: "Something wrong. Please contact admin",
                            icon: "error"
                        });
                    }
                }
            });
        }
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