<label>Username</label>
<input type="text" name="username" id="username" class="form-control mt-2"/><br>
<label>Email</label>
<input type="email" name="email" id="email" class="form-control mt-2"/><br>
<label>Password</label>
<input type="password" name="password" id="password" class="form-control mt-2"/><br>
<label>Re-Type Password</label>
<input type="password" name="password_validation" id="password_validation" class="form-control mt-2"/><br>
<a class="btn btn-success w-100" id="btn-register-acc"><i class="fa-solid fa-paper-plane"></i> Register Account</a>

<script>
    $(document).ready(function() {
        $('#checkTerm').click(function() {
            if ($(this).is(':checked')) {

            } else {
                $('#username, #email, #password, #password_validation').val('')
            }
        });

        $('#btn-register-acc').on('click', function(){
            if(validateInput('text', 'username', 36, 6) && validateInput('text', 'password', 36, 6) && validateInput('text', 'email', 255, 10) && $('#password').val() == $('#password_validation').val()){
                Swal.showLoading()
                $.ajax({
                    url: `/api/v1/register/token`,
                    dataType: 'json',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        username:$('#username').val(),
                        email:$('#email').val()
                    }), 
                    type: "POST",
                    beforeSend: function (xhr) {
                        // ...
                    }
                })
                .done(function (response) {
                    $('#checkTerm').attr('disabled', true)
                    $('#username, #email, #password, #password_validation').attr('readonly',true)

                    let data = response
                    Swal.hideLoading()
                    Swal.fire({
                        title: `Token ${data.status}`,
                        text: data.message,
                        icon: data.status
                    });
                })
                .fail(function (jqXHR, ajaxOptions, thrownError) {
                    Swal.fire({
                        title: "Oops!",
                        text: `Something error please call admin`,
                        icon: "error"
                    });
                });
                
            } else {
                Swal.fire({
                    title: "Oops!",
                    text: `Some field may not valid. Check again!`,
                    icon: "error"
                });
            }
        })
    });
</script>