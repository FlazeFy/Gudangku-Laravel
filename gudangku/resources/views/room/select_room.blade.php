<form action="/room/select_room" method="POST" id="toogle_total_view_select" class="d-flex align-items-center">
    @csrf
    <label for="select_room" class="mt-0 text-nowrap">Select Room</label>
    <select class="form-select mb-0" id="select_room" name="select_room" style='width:200;' onchange="this.form.submit()"></select>
</form>

<script>
    const getAllRoom = (page) => {
        $.ajax({
            url: `/api/v1/inventory/room`,
            type: 'GET',
            beforeSend: function (xhr) {
                Swal.showLoading()
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
            },
            success: function(response) {
                Swal.close()
                const data = response.data
                const selected_room = '<?= session()->get('room_opened') ?>'
                data.forEach(el => {
                    $('#select_room').append(`<option value='${el.inventory_room}' ${el.inventory_room == selected_room ? 'selected':''}>${el.inventory_room}</option>`)
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                generateAPIError(response, true)
            }
        });
    }
    getAllRoom()
</script>