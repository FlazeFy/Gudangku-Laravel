<form id="profile-form">
    <label>Username</label>
    <input type="text" name="username" id="username_input" class="form-control mt-2"/><br>
    <label>Email</label>
    <input type="email" name="email" id="email_input" class="form-control mt-2"/><br>
    <div class="d-flex justify-content-between">
        <label class="fst-italic">Joined since <span id="created_at_holder"></span></label>
        <a class='btn btn-success' onclick='update_profile()'><i class="fa-solid fa-floppy-disk" style="font-size:var(--textXLG);"></i> Save Changes</a>
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
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
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
                                        <input type="text" name="validate_token" class="form-control mt-2" required/><br>
                                        <button class="btn btn-success bg-success ms-2" style="width:240px;" type="submit">Validate Token</button>
                                    </div>
                                </form>
                                <a class="fst-italic" style="font-size:var(--textMD);">Requested at <span>${getDateToContext(tele_data.created_at,'calendar')}</span></a>
                            </div>
                        `)
                        $('#telegram_user_id').attr('disabled', true)
                    }
                }
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                Swal.fire({
                    title: "Oops!",
                    text: "Failed to get the user data",
                    icon: "error"
                });
            }
        });
    }
    get_my_profile()

    const update_profile = () => {
        if($('#username_input').val() != "" && $('#email_input').val() != "") {
            Swal.showLoading()
            $.ajax({
                url: '/api/v1/user/update_profile',
                type: 'PUT',
                data: $('#profile-form').serialize(),
                dataType: 'json',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader("Accept", "application/json");
                    xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>");    
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
                    Swal.close()
                    if(response.status != 404 && response.status != 409){
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
        } else {
            Swal.fire({
                title: "Oops!",
                text: "Validation failed : username or email cant be empty",
                icon: "error"
            });
        }
    }
</script>