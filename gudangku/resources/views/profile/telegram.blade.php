<div class="d-flex justify-content-between">
    <label class="mt-3">Telegram User ID</label>
    <span id="label-validate-holder"></span>
    <span id="update-tele-holder" style="display:none;"></span>
</div>
<input type="text" name="telegram_user_id" id="telegram_user_id" class="form-control mt-2"/><br>
<input id="telegram_user_id_old" hidden/>
<div id="telegram_validation_status_box"></div>

<script>
    function validate_telegram_change(){
        const tele_id_new = $("#telegram_user_id").val()

        $( document ).ready(function() {
            $("#telegram_user_id_new_final").text(tele_id_new)
        });
    }

    $("#telegram_user_id").on("input", function() {
        const tele_id_old = $("#telegram_user_id_old").val()
        const tele_id_new = $("#telegram_user_id").val()
        
        if(tele_id_old != tele_id_new){
            if(tele_id_new.length != 10){
                $("#label-validate-holder").css({
                    'display':'none'
                })
                $("#update-tele-holder").css({
                    'display':'block'
                }).empty().append(`
                    <a class="text-danger"><i class="fa-solid fa-triangle-exclamation"></i> Telegram ID not valid</a>
                `)
            } else {
                $("#label-validate-holder").css({
                    'display':'none'
                })
                $("#update-tele-holder").css({
                    'display':'block'
                }).empty().append(`
                    <a type="button" class="btn btn-primary" onclick="validate_telegram_change()" data-bs-toggle="modal" data-bs-target="#updateTelegramIdModal"><i class="fa-solid fa-pen-to-square"></i> Update Telegram ID</a>
                    <div class="modal fade" id="updateTelegramIdModal" tabindex="-1" aria-labelledby="updateTelegramIdModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="updateTelegramIdModalLabel">Update Telegram ID</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="form-update-telegram-id">
                                    <p>Are you sure want to change the Telegram ID to <span id="telegram_user_id_new_final" class='fw-bold'></span>? All the message from this App are moved to your new Telegram Account and we must re-validate your ID</p>
                                </form>
                                <span id="all_msg" class="text-danger"></span>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-success" onclick="submit_telegram_id_change()">I Agree</button>
                            </div>
                            </div>
                        </div>
                    </div>
                `)
            }
        } else {
            $("#label-validate-holder").css({
                'display':'block'
            })
            $("#update-tele-holder").css({
                'display':'none'
            }).empty()
        }
    });

    const submit_telegram_id_change = () => {
        $('#username_msg').html("")
        $('#pass_msg').html("")
        $('#all_msg').html("")

        $( document ).ready(function() {
            $.ajax({
                url: '/api/v1/user/update_telegram_id',
                type: 'PUT',
                data: {
                    telegram_user_id: $("#telegram_user_id").val()
                },
                dataType: 'json',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader("Accept", "application/json");
                    xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>");    
                },
                success: function(response) {
                    $('#updateTelegramIdModal').modal('hide')
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            get_my_profile()
                        }
                    });
                },
                error: function(response, textStatus, errorThrown) {
                    var errorMessage = "Unknown error occurred"
                    var allMsg
                    var icon = `<i class='fa-solid fa-triangle-exclamation'></i> `

                    if (response.responseJSON && response.responseJSON.hasOwnProperty('message')) {
                        allMsg = response.responseJSON.message
                    } else if (response.responseJSON && response.responseJSON.hasOwnProperty('result')) {
                        if (typeof response.responseJSON.result === "string") {
                            allMsg = response.responseJSON.result
                        } 
                    } else if (response.responseJSON && response.responseJSON.hasOwnProperty('errors')) {
                        allMsg = response.responseJSON.errors.result[0]
                    } else {
                        allMsg = errorMessage
                    }

                    if (allMsg) {
                        $('#all_msg').html(icon + allMsg);
                        Swal.fire({
                            title: "Oops!",
                            text: allMsg,
                            icon: "error"
                        });
                    }
                }
            });
        });
    }
</script>