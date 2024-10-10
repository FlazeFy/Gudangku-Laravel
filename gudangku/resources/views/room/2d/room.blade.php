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
</style>

<div id="room-container"></div>

<script>
    const get_room_layout = () => {
        const room = '<?= session()->get('room_opened') ?>'
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

    const generate_map_room = (data) => {
        const rows = 10
        const cols = 26 
        const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
        
        for (let row = 1; row <= rows; row++) {
            const rowContainer = $('<div class="row"></div>')
            for (let col = 0; col < cols; col++) {
                const label = `${letters[col]}${row}`
                let used = false
                data.forEach(dt => {
                    const coor = dt.layout.split(':')
                    coor.forEach(cr => {
                        if(cr == `${letters[col]}${row}`){
                            used = true
                        }
                    });
                });

                const button = $(`
                    <button class='room-floor ${used ? 'active':''}'>
                        <h6 class='coordinate'>${label}</h6>
                    </button>
                `)
                rowContainer.append(button)
            }
            $('#room-container').append(rowContainer)
        }
    }
</script>