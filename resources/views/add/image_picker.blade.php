<link rel="stylesheet" href="{{ asset('/usecases/manage_image_v1.0.css') }}"/>

<div class="d-flex flex-column gap-1 w-100">
    <div class="img-holder">
        <div class='no-image-picker' title='Change Image' id='image-picker'>
            <label for='file-input'>
                <img id='frame' title='Change Image' style='width: var(--spaceXLG)' src="<?= asset('images/change_image.png')?>"/>
                <a class="bg-transparent">No image has been selected</a>
            </label>
            <input id='file-input' type='file' accept='image/*' name="file" class='d-none'/>
        </div>
        <div class='no-image-picker d-none' title='Change Image' id='no-image-picker'></div>
    </div>
    <canvas id="imageCanvas" style="display: none"></canvas>
    <div id='inventory_image_toolbar'></div>
</div>

<script>
    $(document).on('change', '#file-input', function () {
        const file = this.files[0]
        if (file) {
            if (!file.type.startsWith('image/')) {
                Swal.fire("Error!", "Please select a valid image file!","error")
                return
            }

            const reader = new FileReader()
            reader.onload = function (e) {
                $('#image-picker').addClass('d-none')
                $('#no-image-picker').removeClass('d-none').html(`<img src="${e.target.result}" data-bs-toggle='modal' data-bs-target='#zoom_image' class='img-responsive img-zoomable-modal d-block mx-auto'>`)
                $('#inventory_image_toolbar').html(`
                    <a class="btn btn-danger py-1" style='font-size:var(--textMD)' title="Reset to default image" id='reset-image-btn'><i class="fa-solid fa-trash-can"></i> Reset Image</a>
                    <span id='status-select-image'></span>
                `)
                zoomableModal()
            }
            reader.readAsDataURL(file)
        }
    })

    $(document).on('click', '#reset-image-btn', function () {
        $('#image-picker').removeClass('d-none')
        $('#no-image-picker').addClass('d-none')
        $('#file-input').val('')
        $('#inventory_image_toolbar').empty()
    })
</script>