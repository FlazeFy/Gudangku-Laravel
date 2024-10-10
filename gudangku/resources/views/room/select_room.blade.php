<form action="/room/selectRoom" method="POST" id="toogle_total_view_select">
    @csrf
    <div class="form-floating">
        <select class="form-select" id="select_room" name="select_room" style='width:200;' onchange="this.form.submit()"></select>
        <label for="select_room">Select Room</label>
    </div>
</form>

<script>
    const get_list_room = (page) => {
        Swal.showLoading()
        $.ajax({
            url: `/api/v1/inventory/room`,
            type: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
            },
            success: function(response) {
                Swal.close()
                const data = response.data
                const selected_room = '<?= session()->get('room_opened') ?>'
                data.forEach(el => {
                    $('#select_room').append(`
                        <option value='${el.inventory_room}' ${el.inventory_room == selected_room ? 'selected':''}>${el.inventory_room}</option>
                    `)
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                Swal.fire({
                    title: "Oops!",
                    text: "Failed to get the list room",
                    icon: "error"
                });
            }
        });
    }
    get_list_room()
</script>