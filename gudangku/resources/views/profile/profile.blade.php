<form id="profile-form">
    <div id="login_status_box"></div>
    <label>Username</label>
    <input type="text" name="username" id="username_input" onkeydown="return submitOnEnter(event)" class="form-control"/><br>
    <label>Email</label>
    <input type="email" name="email" id="email_input" onkeydown="return submitOnEnter(event)" class="form-control"/><br>
    <div class="row align-items-center">
        <div class="col-md-6 col-sm-12">
            <p class="fst-italic text-secondary mb-0">Joined since <span id="created_at_holder"></span></p>
        </div>
        <div class="col-md-6 col-sm-12">
            <a class='btn btn-success mt-3 mt-md-0 w-100' onclick='update_profile()'><i class="fa-solid fa-floppy-disk" style="font-size:var(--textXLG);"></i> Save Changes</a>
        </div>
    </div>
</form>

<script>
    const get_my_profile = () => {
        Swal.showLoading()
        $.ajax({
            url: `/api/v1/user/my_profile`,
            type: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
            },
            success: function(response) {
                Swal.close()
                const data = response.data
                $('#username_input').val(data.username)
                $('#email_input').val(data.email)
                $('#telegram_user_id').val(data.telegram_user_id)
                $('#telegram_user_id_old').val(data.telegram_user_id)
                $('#created_at_holder').text(getDateToContext(data.created_at,'calendar'))

                const tele_data = response.telegram_data

                if(data.telegram_is_valid){
                    $('#label-validate-holder').html(`<label class="mt-3 text-success" style="font-weight:600;"><i class="fa-solid fa-check"></i> Validated!</label>`)
                } else {
                    if(tele_data){
                        $('#label-validate-holder').html(`
                            <form action="/profile/validate_telegram" method="POST">
                                @csrf
                                <button class="btn btn-danger" type="submit"><i class="fa-solid fa-triangle-exclamation"></i> Your ID is not validated. Validate Now!</button>
                            </form>
                        `)
                        $('#telegram_validation_status_box').html(`
                            <br>
                            <div class="box-danger">
                                <h6 class="fw-bold">You have pending Token Validation. Please validate it!</h6>
                                <label class="mt-2">Token Validation</label>
                                <form action="/profile/submit_telegram_validation" method="POST">
                                    @csrf
                                    <div class="d-flex justify-content-between">
                                        <input hidden value="${tele_data.id}" name="id">
                                        <input type="text" name="validate_token" class="form-control" required/><br>
                                        <button class="btn btn-success bg-success ms-2" style="width:240px;" type="submit">Validate Token</button>
                                    </div>
                                </form>
                                <a class="fst-italic" style="font-size:var(--textMD);">Requested at <span>${getDateToContext(tele_data.created_at,'calendar')}</span></a>
                            </div>
                        `)
                        $('#telegram_user_id').attr('disabled', true)
                    }
                }

                if(data.telegram_is_valid == 0 && data.telegram_user_id == null){
                    $('#telegram_user_id').after(`
                        <div class="alert alert-success w-100 mt-4"><i class="fa-solid fa-circle-info"></i> Sync your GudangKu account with your <b>Telegram ID</b> to use this apps straight at your Telegram Chat</div>
                    `)
                }

                if(data.is_google_sign_in){
                    $('#email_input').prop('readonly', true)
                    $('#login_status_box').html(`<div class="alert alert-success w-100 mt-4"><i class="fa-solid fa-circle-info"></i> Your account login using Google Sign In</div>`)
                    $('#change-pass-button-holder').empty()
                } else{
                    $('#login_status_box').html(`<div class="alert alert-success w-100 mt-4"><i class="fa-solid fa-circle-info"></i> Your account login using Basic Auth</div>`)
                    $('#change-pass-button-holder').html(`
                        <a class="btn btn-primary" href="/forgot">
                            <i class="fa-solid fa-key" style="font-size:var(--textXLG);"></i> Change Password
                        </a>`)
                } 
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                generate_api_error(response, true)
            }
        });
    }
    get_my_profile()

    const update_profile = () => {
        if($('#username_input').val() != "" && $('#email_input').val() != "") {
            $.ajax({
                url: '/api/v1/user/update_profile',
                type: 'PUT',
                data: $('#profile-form').serialize(),
                dataType: 'json',
                beforeSend: function (xhr) {
                    Swal.showLoading()
                    xhr.setRequestHeader("Accept", "application/json");
                    xhr.setRequestHeader("Authorization", `Bearer ${token}`);    
                },
                success: function(response) {
                    Swal.close()
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
                error: function(response, jqXHR, textStatus, errorThrown) {
                    generate_api_error(response, true)
                }
            });
        } else {
            Swal.fire({
                title: "Oops!",
                text: "Validation failed : username or email cant be empty",
                icon: "error"
            });
        }
    }
    const submitOnEnter = (event) => {
        if (event.keyCode === 13) { 
            event.preventDefault() 
            update_profile()
            return false 
        }
        return true 
    }
</script>