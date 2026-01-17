<div id="room-container"></div>

<script>
    const room = '<?= session()->get('room_opened') ?>'

    const getRoomLayout = () => {
        $(document).ready(function() {
            $('.modal').modal('hide')
        })
        Swal.showLoading()
        $.ajax({
            url: `/api/v1/inventory/layout/${room}`,
            type: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
            },
            success: function(response) {
                $('#room-container').empty()
                Swal.close()
                const data = response.data
               
                generateMapRoom('#room-container',data,true,room)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                if(response.status != 404){
                    generateAPIError(response, true)
                } else {
                    generateMapRoom('#room-container',null,true,room)
                }
            }
        });
    }
    getRoomLayout()

    const getInventoryRoomStorage = (room,storage,target) => {
        $(`#table-inventory-${target} tbody`).empty()
        $(`#pie-chart-${target}`).empty()
        if(storage != '' && storage){
            Swal.showLoading()
            $.ajax({
                url: `/api/v1/inventory/search/by_room_storage/${room}/${storage}`,
                type: 'GET',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader("Accept", "application/json")
                    xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
                },
                success: function(response) {
                    Swal.close()
                    const data = response.data
                    const stats = response.stats
                    data.forEach(dt => {
                        $(`#table-inventory-${target} tbody`).append(`
                            <tr class="text-center">
                                <td>${dt.inventory_rack ?? '-'}</td>
                                <td class="text-start">
                                    <b class="mb-0">${dt.inventory_name}</b>
                                    <p class="mb-0">${dt.inventory_vol} ${dt.inventory_unit}</p>
                                </td>
                                <td>${dt.inventory_category}</td>
                                <td>Rp. ${dt.inventory_price.toLocaleString()}</td>
                                <td><a class="btn btn-warning modal-btn" href="/inventory/edit/${dt.id}"><i class="fa-solid fa-pen-to-square"></i></a></td>
                            </tr>
                        `)
                    });
                    if(stats){
                        generatePieChart('Category Distribution',`pie-chart-${target}`,stats)
                        $(`#pie-chart-${target}`).append('<hr class="mb-1 mt-5">')
                    }
                },
                error: function(response, jqXHR, textStatus, errorThrown) {
                    Swal.close()
                    $(`#table-inventory-${target} tbody`).append(`
                        <tr><td colspan='4' class='text-secondary fst-italic text-center'>- No inventory to show -</td></tr>
                    `)
                }
            });
        }
    }

    const postStorage = (form) => {
        $.ajax({
            url: '/api/v1/inventory/layout',
            type: 'POST',
            data: $(`#${form}`).serialize(),
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
            },
            success: function(response) {
                Swal.fire({
                    title: "Success!",
                    text: response.message,
                    icon: "success",
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        getRoomLayout() 
                    }
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                generateAPIError(response, true)
            }
        });
    }

    const expandFloor = () => {
        let max_letter
        let max_number
        $('#room-container > .floor-row').each(function(idx, el) {
            const coor = $(this).find('.coordinate').last().text()
            const match = coor.match(/^([A-Z]+)(\d+)$/)

            if (match) {
                const [_, letters, numbers] = match
                const letters_next = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.slice(0, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.indexOf(letters) + 2).slice(-1)
                max_letter = letters_next
                max_number = parseInt(numbers)
                const label = `${letters_next}${numbers}`

                const modal = generateModalDetail(null, null, room, label, null)
                $(this).append(`
                    <button class='d-inline-block room-floor' data-bs-toggle="modal" data-bs-target="#modalDetail-${label}">
                        <h6 class='coordinate'>${label}</h6>
                    </button>
                    ${modal}
                `)
            }
        })

        const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.slice(0, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.indexOf(max_letter) + 1)
        const rowContainer = $('<div class="floor-row"></div>')

        for (let idx = 0; idx < letters.length; idx++) {
            const label = `${letters[idx]}${max_number+1}`
            const modal = generateModalDetail(null, null, room, label, null)
            rowContainer.append(`
                <button class='d-inline-block room-floor' data-bs-toggle="modal" data-bs-target="#modalDetail-${label}">
                    <h6 class='coordinate'>${label}</h6>
                </button>
                ${modal}
            `)
        }
        $('#room-container .floor-config').first().before(rowContainer)

        requestAnimationFrame(() => {
            const $container = $('#room-container')
            $container.scrollTop($container[0].scrollHeight)
            $container.scrollLeft(0)
        })
    }

    $(document).ready(function() {
        $(document).on('click', '.submit_add_storage', function(event) {
            const form_id = $(this).closest('form').attr('id')
            postStorage(form_id)
        });

        $(document).on('click', '.remove_coordinate', function(event) {
            const idx = $(this).index('.remove_coordinate')
            const id_coor_holder = $('.id-coor-holder').eq(idx).val().split('_')
            const id = id_coor_holder[0]
            const coor = id_coor_holder[1]
            deleteModuleByID(`${id}/${coor}`, 'inventory', 'delete_layout', token, () => getRoomLayout())
        })
    });
</script>