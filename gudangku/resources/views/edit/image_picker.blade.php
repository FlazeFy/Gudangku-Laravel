<link rel="stylesheet" href="{{ asset('/usecases/manage_image_v1.0.0.css') }}"/>

<div class="img-holder" id='img_holder'></div>
<canvas id="imageCanvas" style="display: none;"></canvas>
<div class='d-flex justify-content-between' id='inventory_image_toolbar'></div>
<div id='reset_img_btn_handler'></div>

<script>
    $( document ).ready(function() {
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
                };
                reader.readAsDataURL(file)
                update_image_url(file)

            }
        });
        $(document).on('click', '#reset-image-btn', function () {
            update_image_url(null)
        });
    });
    const update_image_url = (img_new) => {
        const form = $('#edit-image')[0]
        const formData = new FormData(form)
        $.ajax({
            url: `/api/v1/inventory/edit_image/<?= $id ?>`,
            type: 'PUT',
            data: formData,
            processData: false, 
            contentType: false,
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json");
                xhr.setRequestHeader("Authorization", `Bearer ${token}`);
                Swal.showLoading();
            },
            success: function(response) {
                Swal.hideLoading()

                if(img_new == null){
                    $('#image-picker').removeClass('d-none')
                    $('#no-image-picker').addClass('d-none')
                    $('#file-input').val('')
                    $('#inventory_image_toolbar').empty()
                    $('#status-select-image').html(`<p class='text-danger input-msg'><i class="fa-solid fa-times"></i> Image has been reset!</p>`)
                    $('#reset_img_btn_handler').empty()
                } 
                
                Swal.fire({
                    title: "Success!",
                    text: response.message,
                    icon: "success",
                    allowOutsideClick: false, 
                    allowEscapeKey: false, 
                    confirmButtonText: "OK", 
                }).then((result) => {
                    if (result.isConfirmed) {
                        get_dictionary();
                        get_detail_inventory("<?= $id ?>");
                    }
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.hideLoading();
                generate_api_error(response, true)
            }
        });
    }

    $( document ).ready(function() {
        const image = document.getElementById('frame')
        const canvas = document.getElementById('imageCanvas')
        const ctx = canvas.getContext('2d');
        canvas.width = image.width;
        canvas.height = image.height;
        ctx.drawImage(image, 0, 0, image.width, image.height);

        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const { data } = imageData;
        const colorCount = {};
        let dominantColor = { color: null, count: 0 };

        for (let i = 0; i < data.length; i += 4) {
            const r = data[i];
            const g = data[i + 1];
            const b = data[i + 2];
            const color = `rgb(${r},${g},${b})`;

            if (colorCount[color]) {
                colorCount[color]++;
            } else {
                colorCount[color] = 1;
            }

            if (colorCount[color] > dominantColor.count) {
                dominantColor = { color, count: colorCount[color] }
            }
        }

        const rgbToHex = (r, g, b) => 
            `#${((1 << 24) + (r << 16) + (g << 8) + b)
                .toString(16)
                .slice(1)
                .toUpperCase()}`

        const rgbValues = dominantColor.color.match(/\d+/g)
        const hexColor = rgbToHex(parseInt(rgbValues[0]), parseInt(rgbValues[1]), parseInt(rgbValues[2]))

        const colorNames = {
            "black": [0, 0, 0],
            "white": [255, 255, 255],
            "red": [255, 0, 0],
            "lime": [0, 255, 0],
            "blue": [0, 0, 255],
            "yellow": [255, 255, 0],
            "cyan": [0, 255, 255],
            "magenta": [255, 0, 255],
            "silver": [192, 192, 192],
            "gray": [128, 128, 128],
            "maroon": [128, 0, 0],
            "olive": [128, 128, 0],
            "green": [0, 128, 0],
            "purple": [128, 0, 128],
            "teal": [0, 128, 128],
            "navy": [0, 0, 128]
        };

        const getClosestColorName = (r, g, b) => {
            let closestColor = null
            let closestDistance = Infinity

            for (const [name, rgb] of Object.entries(colorNames)) {
                const distance = Math.sqrt(
                    Math.pow(r - rgb[0], 2) +
                    Math.pow(g - rgb[1], 2) +
                    Math.pow(b - rgb[2], 2)
                );

                if (distance < closestDistance) {
                    closestColor = name
                    closestDistance = distance
                }
            }

            return closestColor
        };

        const colorName = getClosestColorName(parseInt(rgbValues[0]), parseInt(rgbValues[1]), parseInt(rgbValues[2]))
        $('#inventory_color').val(ucFirst(colorName))
    })
</script>