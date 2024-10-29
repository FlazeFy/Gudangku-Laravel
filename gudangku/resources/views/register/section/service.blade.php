<div>
    <div class='position-relative'>
        <input type='text' class='form-control form-validated' name='telegram_user_id' id='telegram_user_id' maxlength='36'>
    </div>
    <div class='position-relative'>
        <input type='text' class='form-control form-validated' name='line_user_id' id='line_user_id' maxlength='145'>
    </div>
    <label>Discord</label>
</div>

<script>
    $( document ).ready(function() {
        $(document).on('input','#telegram_user_id', function(){
            if($(this).val().length == 10){
                $(this).after(`<a class='btn btn-success position-absolute' style='bottom:var(--spaceXSM); right:var(--spaceXSM); padding:var(--spaceXXSM) var(--spaceMD) !important; font-size:var(--textXMD); font-weight:600;' id='validate-telegram-id-btn'><i class="fa-solid fa-paper-plane"></i> Validate</a>`)
            } else {
                $('#validate-telegram-id-btn').remove()
            }
        })
        $(document).on('input','#line_user_id', function(){
            if($(this).val().length == 144){
                $(this).after(`<a class='btn btn-success position-absolute' style='bottom:var(--spaceXSM); right:var(--spaceXSM); padding:var(--spaceXXSM) var(--spaceMD) !important; font-size:var(--textXMD); font-weight:600;' id='validate-line-id-btn'><i class="fa-solid fa-paper-plane"></i> Validate</a>`)
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
                    xhr.setRequestHeader("Accept", "application/json");
                    xhr.setRequestHeader("Authorization", `Bearer ${token}`);    
                },
                success: function(response) {
                    Swal.hideLoading()
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success"
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#telegram_user_id').attr('readonly',true)
                            $('#telegram_user_id').after(`<input type='text' class='form-control form-validated' name='telegram_user_id' id='telegram_user_id' maxlength='36'>`)
                        }
                    });
                },
                error: function(response, jqXHR, textStatus, errorThrown) {
                    Swal.hideLoading()
                    if(response.status != 404){
                        Swal.fire({
                            title: "Oops!",
                            text: "Something wrong. Please contact admin",
                            icon: "error"
                        });
                    } else {
                        Swal.fire({
                            title: "Oops!",
                            text: response.responseJSON.message,
                            icon: "error"
                        });
                    }
                }
            });
        })
    });
</script>