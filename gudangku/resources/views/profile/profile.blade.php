<label>Username</label>
<input type="text" name="username" value="{{$profile->username}}" class="form-control mt-2"/><br>
<label>Email</label>
<input type="email" name="email" value="{{$profile->email}}" class="form-control mt-2"/><br>
<div class="d-flex justify-content-between">
    <label class="mt-3">Telegram User ID</label>
    <span id="label-validate-holder">
        @if($profile->telegram_is_valid)
            <label class="mt-3 text-success" style="font-weight:600;"><i class="fa-solid fa-check"></i> Telegram ID is Validated!</label>
        @else
            @if(!$validation_telegram)
                <form action="/profile/validate_telegram" method="POST">
                    @csrf
                    <button class="btn btn-danger" type="submit"><i class="fa-solid fa-triangle-exclamation"></i> Your ID is not validated. Validate Now!</button>
                </form>
            @endif
        @endif
    </span>
    <span id="update-tele-holder" style="display:none;"></span>
</div>
<input type="text" name="telegram_user_id" id="telegram_user_id" value="{{$profile->telegram_user_id}}" class="form-control mt-2" <?php if($validation_telegram){ echo "disabled"; } ?>/><br>
<input id="telegram_user_id_old" hidden value="{{$profile->telegram_user_id}}"/><br>

@if($validation_telegram)
    <div class="box-danger">
        <h6 class="fw-bold">You have pending Token Validation. Please validate it!</h6>
        <label class="mt-2">Token Validation</label>
        <form action="/profile/submit_telegram_validation" method="POST">
            @csrf
            <div class="d-flex justify-content-between">
                <input hidden value="{{$validation_telegram->id}}" name="id">
                <input type="text" name="validate_token" class="form-control mt-2" required/><br>
                <button class="btn btn-success bg-success ms-2" style="width:240px;" type="submit">Validate Token</button>
            </div>
        </form>
        <a class="fst-italic" style="font-size:var(--textMD);">Requested at<span class="date_holder">{{$validation_telegram->created_at}}</span></a>
    </div>
@endif

<label class="fst-italic">Joined since <span class="date_holder">{{$profile->created_at}}</span></label>

<script>
    const date_holder = document.querySelectorAll('.date_holder')

    date_holder.forEach(e => {
        const date = new Date(e.textContent);
        e.textContent = getDateToContext(e.textContent, "datetime")
    });

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
                                <p>Are you sure want to change the Telegram ID to <span id="telegram_user_id_new_final"></span>? All the message from this App are moved to your new Telegram Account and we must re-validate your ID</p>
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
        } else {
            $("#label-validate-holder").css({
                'display':'block'
            })
            $("#update-tele-holder").css({
                'display':'none'
            }).empty()
        }
    });

    function submit_telegram_id_change(){
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
                    location.reload()
                },
                error: function(response, jqXHR, textStatus, errorThrown) {
                    var errorMessage = "Unknown error occurred"
                    var allMsg = null
                    var icon = `<i class='fa-solid fa-triangle-exclamation'></i> `

                    if (response && response.responseJSON && response.responseJSON.hasOwnProperty('result')) {   
                        //Error validation
                        if(typeof response.responseJSON.result === "string"){
                            allMsg = response.responseJSON.result
                        } 
                        
                    } else if(response && response.responseJSON && response.responseJSON.hasOwnProperty('errors')){
                        allMsg = response.responseJSON.errors.result[0]
                    } else {
                        allMsg = errorMessage
                    }

                    //Set to html
                    if(allMsg){
                        $('#all_msg').html(icon + allMsg)
                    }
                }
            });
        });
    }
</script>