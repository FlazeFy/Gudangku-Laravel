<form id="profile-form">
    <div id="login_status_box"></div>
    <label>Username</label>
    <input type="text" name="username" id="username_input" onkeydown="return submitOnEnter(event)" class="form-control"/>
    <label>Email</label>
    <input type="email" name="email" id="email_input" onkeydown="return submitOnEnter(event)" class="form-control"/>
    <div class="row align-items-center mt-3">
        <div class="col-md-6 col-sm-12">
            <p class="fst-italic text-secondary mb-0">Joined since <span id="created_at_holder"></span></p>
        </div>
        <div class="col-md-6 col-sm-12">
            <a class='btn btn-success mt-3 mt-md-0 w-100' onclick='update_profile()'><i class="fa-solid fa-floppy-disk"></i> Save Changes</a>
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

                if (data.telegram_is_valid) {
                    $('#label-validate-holder').html(`<label class="mt-3 text-success"><i class="fa-solid fa-check"></i> Validated!</label>`)
                } else {
                    if (tele_data) {
                        $('#telegram_validation_status_box').html(`
                            <div class="box-danger">
                                <p class="mb-0">You have pending Token Validation. Please validate it!</p>
                                <label class="mt-2">Token Validation</label>
                                <div class="d-flex justify-content-between">
                                    <input type="text" name="validate_token" id="validate_token" class="form-control" required/><br>
                                    <button class="btn btn-success ms-2" id="validate_token_submit-btn" style="width:240px" type="submit">Validate Token</button>
                                </div>
                                <a class="text-danger">Requested at <span>${getDateToContext(tele_data.created_at,'calendar')}</span></a>
                            </div>
                        `)
                        $('#telegram_user_id').attr('disabled', true)
                    }
                }

                if (data.telegram_is_valid == 0 && data.telegram_user_id == null) {
                    $('#telegram_user_id').after(`
                        <div class="alert alert-success w-100 mt-4"><i class="fa-solid fa-circle-info"></i> Sync your GudangKu account with your <b>Telegram ID</b> to use this apps straight at your Telegram Chat</div>
                    `)
                }

                if (data.is_google_sign_in) {
                    $('#email_input').prop('readonly', true)
                    $('#login_status_box').html(`<div class="alert alert-success w-100 mt-4"><i class="fa-solid fa-circle-info"></i> Your account login using Google Sign In</div>`)
                    $('#change-pass-button-holder').empty()
                } else{
                    $('#login_status_box').html(`<div class="alert alert-success w-100 mt-4"><i class="fa-solid fa-circle-info"></i> Your account login using Basic Auth</div>`)
                    $('#change-pass-button-holder').html(`<a class="btn btn-primary" href="/forgot"><i class="fa-solid fa-key"></i> Change Password</a>`)
                } 
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                generateAPIError(response, true)
            }
        })
    }
    get_my_profile()

    $(document).on('click','#validate_token_submit-btn',function(){
        validate_token_telegram()
    })

    const validate_token_telegram = () => {
        const reqContext = $('#validate_token').val().trim()
        if (reqContext != "") {
            $.ajax({
                url: '/api/v1/user/validate_telegram_id',
                type: 'PUT',
                data: {
                    request_context: reqContext
                },
                dataType: 'json',
                beforeSend: function (xhr) {
                    Swal.showLoading()
                    xhr.setRequestHeader("Accept", "application/json")
                    xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
                },
                success: function(response) {
                    Swal.close()
                    Swal.fire("Success!", response.message, "success").then((result) => {
                        if (result.isConfirmed) {
                            get_my_profile()
                            $('#telegram_validation_status_box').empty()
                            $("#label-validate-holder").css('display','block')
                            $("#update-tele-holder").css('display','none')
                            $('#telegram_user_id').attr('disabled', false)
                        }
                    })
                },
                error: function(response, jqXHR, textStatus, errorThrown) {
                    if (response.status === 404) return Swal.fire("Oops!", response.responseJSON.message, "error")
                    generateAPIError(response, true)
                }
            })
        } else {
            Swal.fire("Oops!","Validation failed : token cant be empty","error")
        }
    }

    const update_profile = () => {
        if ($('#username_input').val() != "" && $('#email_input').val() != "") {
            $.ajax({
                url: '/api/v1/user/update_profile',
                type: 'PUT',
                data: $('#profile-form').serialize(),
                dataType: 'json',
                beforeSend: function (xhr) {
                    Swal.showLoading()
                    xhr.setRequestHeader("Accept", "application/json")
                    xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
                },
                success: function(response) {
                    Swal.close()
                    Swal.fire("Success!", response.message, "success").then((result) => {
                        if (result.isConfirmed) {
                            get_my_profile()
                        }
                    })
                },
                error: function(response, jqXHR, textStatus, errorThrown) {
                    generateAPIError(response, true)
                }
            })
        } else {
            Swal.fire("Oops!","Validation failed : username or email cant be empty","error")
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