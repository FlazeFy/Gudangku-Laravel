<style>
    .img-holder {
        min-height: 260px;
        border: 2px dashed var(--whiteColor);
        width: 100%;
        border-radius: var(--roundedMD);
        padding: var(--textLG);
        margin: var(--spaceMD) 0;
        text-align: center;
        display: flex;
        justify-content: center;
        align-items: center; 
        cursor: pointer;
    }
    .img-holder:hover {
        transform: scale(1.01);
    }
    .img-holder a {
        vertical-align:middle;
    }
    .image-upload{
        position: absolute;
        bottom: 3px;
        right: 10px;
    }
    .image-upload>input {
        display: none;
    }
    .btn-icon-reset-image{
        position: absolute; 
        bottom: 10px; 
        left: 10px;
        background: var(--dangerBG) !important;
        color:var(--whiteColor) !important;
        -webkit-transition: all 0.4s;
        -o-transition: all 0.4s;
        transition: all 0.4s;
    }
</style>

<div class="img-holder">
    <div class='no-image-picker' title='Change Image' id='image-picker'>
        <label for='file-input'>
            <img id='frame' title='Change Image' style='width: var(--spaceXLG);' src="<?= asset('images/change_image.png')?>"/>
            <a>No image has been selected</a>
        </label>
        <input id='file-input' type='file' accept='image/*' name="file" class='d-none'/>
    </div>
    <div class='no-image-picker d-none' title='Change Image' id='no-image-picker'></div>
</div>
<canvas id="imageCanvas" style="display: none;"></canvas>
<div class='d-flex justify-content-between' id='inventory_image_toolbar'></div>

<script>
    $(document).on('change', '#file-input', function () {
        const file = this.files[0]
        if (file) {
            if (!file.type.startsWith('image/')) {
                Swal.fire({
                    title: "Error!",
                    text: "Please select a valid image file!",
                    icon: "error"
                });
                return
            }

            const reader = new FileReader()
            reader.onload = function (e) {
                $('#image-picker').addClass('d-none')
                $('#no-image-picker').removeClass('d-none').html(`<img src="${e.target.result}" data-bs-toggle='modal' data-bs-target='#zoom_image'class='img-responsive img-zoomable-modal d-block mx-auto'>`)
                $('#inventory_image_toolbar').html(`
                    <a class="btn btn-danger px-2 shadow" title="Reset to default image" id='reset-image-btn'><i class="fa-solid fa-trash-can"></i> Reset Image</a>
                    <span id='status-select-image'></span>
                `)
                zoomableModal()

                $('#status-select-image').html(`<p class='text-success input-msg'><i class="fa-solid fa-check"></i> Image is Valid!</p>`);
                Swal.fire({
                    title: "Success!",
                    text: "Image has been selected",
                    icon: "success"
                });
            };
            reader.readAsDataURL(file)
        }
    });
    $(document).on('click', '#reset-image-btn', function () {
        $('#image-picker').removeClass('d-none')
        $('#no-image-picker').addClass('d-none')
        $('#file-input').val('')
        $('#inventory_image_toolbar').empty()

        $('#status-select-image').html(`<p class='text-danger input-msg'><i class="fa-solid fa-times"></i> Image has been reset!</p>`)
        Swal.fire({
            title: "Success!",
            text: "Image has been reset to default",
            icon: "success"
        });
    });
</script>