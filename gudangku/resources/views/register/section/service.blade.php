<div>
    <div class='position-relative container bordered mb-4'>
        <input type='text' class='form-control form-validated' name='telegram_user_id' id='telegram_user_id' maxlength='36'>
    </div>
    <div class='position-relative container bordered mb-4'>
        <input type='text' class='form-control form-validated' name='line_user_id' id='line_user_id' maxlength='145'>
    </div>
</div>

<script>
    $( document ).ready(function() {
        $(document).on('input','#telegram_user_id', function(){
            if($(this).val().length == 10){
                $(this).after(`<a class='btn btn-success position-absolute' style='bottom:var(--spaceXLG); right:var(--spaceXLG); padding:var(--spaceXXSM) var(--spaceMD) !important; font-size:var(--textXMD); font-weight:600;' id='validate-telegram-id-btn'><i class="fa-solid fa-paper-plane"></i> Validate</a>`)
            } else {
                $('#validate-telegram-id-btn').remove()
            }
        })
        $(document).on('input','#line_user_id', function(){
            if($(this).val().length == 144){
                $(this).after(`<a class='btn btn-success position-absolute' style='bottom:var(--spaceXLG); right:var(--spaceXLG); padding:var(--spaceXXSM) var(--spaceMD) !important; font-size:var(--textXMD); font-weight:600;' id='validate-line-id-btn'><i class="fa-solid fa-paper-plane"></i> Validate</a>`)
            } else {
                $('#validate-line-id-btn').remove()
            }
        })
        $(document).on('click','#validate-telegram-id-btn', function(){
            const token = localStorage.getItem('token_key')
            $.ajax({
                url: '/api/v1/user/update_telegram_id',
                type: 'PUT',
                data: {
                    telegram_user_id: $('#telegram_user_id').val()
                },
                dataType: 'json',
                beforeSend: function (xhr) {
                    Swal.showLoading()
                    xhr.setRequestHeader("Accept", "application/json");
                    xhr.setRequestHeader("Authorization", `Bearer ${token}`);    
                },
                success: function(response) {
                    Swal.hideLoading()
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#telegram_user_id').attr('readonly',true)
                            $('#validate-telegram-id-btn').remove()
                            $('#telegram_user_id').after(`
                                <div class="box-danger mt-3" id="telegram-validation-box">
                                    <h6 class="fw-bold">You have pending Token Validation. Please validate it!</h6>
                                    <label>Token Validation</label>
                                    <input type='text' class='form-control' name=telegram_token_validation' id='telegram_token_validation' maxlength='7'>
                                </div>
                            `)
                            $('.progress-bar').css('width', '100%').attr('aria-valuenow', 100) 
                            $('.step-mobile .title').text("Finish!")
                        }
                    });
                },
                error: function(response, jqXHR, textStatus, errorThrown) {
                    generateAPIError(response, true)
                }
            });
        })
        $(document).on('input','#telegram_token_validation', function(){
            if($(this).val().length == 6){
                const token = localStorage.getItem('token_key')
                $.ajax({
                    url: '/api/v1/user/validate_telegram_id',
                    type: 'PUT',
                    data: {
                        request_context: $(this).val()
                    },
                    dataType: 'json',
                    beforeSend: function (xhr) {
                        Swal.showLoading()
                        xhr.setRequestHeader("Accept", "application/json");
                        xhr.setRequestHeader("Authorization", `Bearer ${token}`);    
                    },
                    success: function(response) {
                        Swal.hideLoading()
                        Swal.fire({
                            title: "Success!",
                            text: response.message,
                            icon: "success",
                            allowOutsideClick: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#telegram-validation-box').removeClass('box-danger').html(`<span class='text-success'><i class="fa-solid fa-check"></i> Telegram is validated!</span>`)
                            }
                        });
                    },
                    error: function(response, jqXHR, textStatus, errorThrown) {
                        generateAPIError(response, true)
                    }
                });
            }
        })
    });
</script>