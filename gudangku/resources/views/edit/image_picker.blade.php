<link rel="stylesheet" href="{{ asset('/usecases/manage_image_v1.0.0.css') }}"/>

<div class="img-holder" id='img_holder'></div>
<canvas id="imageCanvas" style="display: none;"></canvas>
<div id='reset_img_btn_handler'></div>

<script>
    $(document).ready(function() {
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
                    zoomableModal()
                };
                reader.readAsDataURL(file)
                update_image_url(true)
            }
        })

        $(document).on('click', '#reset-image-btn', function () {
            update_image_url(false)
        })
    })

    const update_image_url = (isNew) => {
        const formData = new FormData()
        const img = $("#file-input")[0] ? $("#file-input")[0].files[0] : null
        formData.append("inventory_image", img ? img : null)

        $.ajax({
            url: `/api/v1/inventory/edit_image/<?= $id ?>`,
            type: 'POST',
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

                if(!isNew){
                    $('#image-picker').removeClass('d-none')
                    $('#no-image-picker').addClass('d-none')
                    $('#file-input').val('')
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
                        get_detail_inventory("<?= $id ?>")
                    }
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                generate_api_error(response, true)
            }
        });
    }
</script>