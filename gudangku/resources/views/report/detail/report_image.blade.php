<div class="d-flex flex-wrap gap-2 align-items-center mb-3 justify-content-between">
    <h3 class="mb-0">Attached Image</h3>
    <div class="d-flex gap-2" id="report_image_button-holder">
        <a class="btn btn-success py-1" id="add_report_image-button"><i class="fa-solid fa-image"></i><span class="d-none d-md-inline"> Add Image</span></a>
    </div>
</div>
<div id="report_img_holder" class="row mx-1"></div>

<script>
    $(document).ready(function() {
        $(document).on('click', '#clear_report_image-button', function(){
            $('#clear_report_image-button').remove()
            $('#save_report_image-button').remove()
            if($('#report_img_holder .img-zoomable-modal').length > 0) {
                $('#report_img_holder').children().not(':has(.img-zoomable-modal)').remove()
            } else {
                $('#report_img_holder').html(`
                    <div class="no-image">
                        <h6 class="text-center text-secondary fst-italic">- No Image Attached -</h6>
                    </div>
                `)
            }
        })

        $(document).on('click', '#add_report_image-button', function () {
            $("#report_img_holder .no-image").remove()

            if ($("#report_img_holder .report-image-holder").length > 9) {
                Swal.fire({
                    title: "Error!",
                    text: "You can only add one image",
                    icon: "error"
                })
                return
            }

            if($('#clear_report_image-button').length === 0){
                $('#report_image_button-holder').prepend(`
                    <a class="btn btn-danger py-1" id="clear_report_image-button">
                        <i class="fa-solid fa-circle-xmark"></i><span class="d-none d-md-inline"> Clear</span>
                    </a>
                `)
            }

            $("#report_img_holder").append(`
                <div class="col-lg-4 col-md-6 col-sm-12 col-12">
                    <div class="report-image-holder mt-2">
                        <input type="file" class="other_images form-control" accept="image/jpeg,image/png,image/gif">
                        <img class="image-preview mt-1 d-none" style="max-width: 200px;">
                    </div>
                </div>
            `)
        })

        $(document).on('change', '.other_images', function(e) {
            const file = e.target.files[0]
            const $preview = $(this).siblings('.image-preview')

            if (!file) return
            const maxSize = 5 * 1024 * 1024
            if (file.size > maxSize) {
                failedMsg('File too large. Maximum file size is 5 MB')
                $(this).val('')
                $preview.addClass('d-none').attr('src', '')
                return
            }

            const reader = new FileReader()
            reader.onload = function (event) {
                $preview.attr('src', event.target.result).removeClass('d-none')
            }
            reader.readAsDataURL(file)

            if($('#save_report_image-button').length === 0){
                $('#report_image_button-holder').append(`
                    <a class="btn btn-success py-1" id="save_report_image-button">
                        <i class="fa-solid fa-floppy-disk"></i><span class="d-none d-md-inline"> Save Image</span>
                    </a>
                `)
            }
        })

        $(document).on('click', '#save_report_image-button', function () {
            const id = '<?= $id ?>'
            const fd = new FormData()
            
            let totalFiles = 0
            $(".other_images").each(function() {
                const files = this.files
                if (!files.length) return

                totalFiles += files.length;
                for (let i = 0; i < files.length; i++) {
                    fd.append("report_image[]", files[i])
                }
            });

            if (totalFiles > 10) {
                failedMsg("You can only upload up to 10 other images")
                return
            }

            $.ajax({
                url: `/api/v1/report/report_image/${id}`,
                type: 'POST', 
                processData: false,
                contentType: false,
                data: fd,
                beforeSend: function (xhr) {
                    Swal.showLoading()
                    xhr.setRequestHeader("Accept", "application/json")
                    xhr.setRequestHeader("Authorization", `Bearer ${token}`)
                },
                success: function(response) {
                    Swal.fire("Success!", response.message, "success").then(() => window.location.href=`/report/detail/${id}` )
                },
                error: function(response, jqXHR, textStatus, errorThrown) {
                    generate_api_error(response, true);
                }
            });
        })
    })
</script>