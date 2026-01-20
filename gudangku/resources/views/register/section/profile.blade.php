<label>Username</label>
<input type="text" name="username" id="username" class="form-control"/>
<label>Email</label>
<input type="email" name="email" id="email" class="form-control"/>
<label>Password</label>
<input type="password" name="password" id="password" class="form-control"/>
<label>Re-Type Password</label>
<input type="password" name="password_validation" id="password_validation" class="form-control"/>
<a class="btn btn-success w-100 mt-4" id="btn-register-acc"><i class="fa-solid fa-paper-plane"></i> Register Account</a><br>
<div class="text-center mt-3 d-none section-form" id="token-section">
    <h3>Validate</h3><br>
    <h4 class="my-2 fw-bold" style="font-size:var(--textJumbo)" id="timer">15:00</h4>
    <label>Type the Token that has sended to your email</label><br>
    <div class="pin-code" id="pin-holder">
        <input type="text" maxlength="1" oninput="validatePin()" autofocus>
        <input type="text" maxlength="1" oninput="validatePin()">
        <input type="text" maxlength="1" oninput="validatePin()">
        <input type="text" maxlength="1" oninput="validatePin()">
        <input type="text" maxlength="1" oninput="validatePin()">
        <input type="text" maxlength="1" oninput="validatePin()">
    </div>
    <div id="token_validate_msg" class="msg-error-input mb-2" style="font-size:var(--textMD)"></div>
    <div class="d-inline-block mx-auto">
        <a class="btn btn-success rounded-pill px-3 mt-3" id="btn-regenerate-token">Don't receive the token. Send again!</a>
    </div>
</div>

<script>
    let pinContainer = document.querySelector(".pin-code")
    let pin_holder = document.getElementById('pin-holder')
    let timer = document.getElementById("timer")
    let remain = 900

    pinContainer.addEventListener('keyup', function (event) {
        var target = event.srcElement
        var maxLength = parseInt(target.attributes["maxlength"].value, 10)
        var myLength = target.value.length

        if (myLength >= maxLength) {
            var next = target
            while (next = next.nextElementSibling) {
                if (next == null) break
                if (next.tagName.toLowerCase() == "input") {
                    next.focus()
                    break
                }
            }
        }

        if (myLength === 0) {
            var next = target
            while (next = next.previousElementSibling) {
                if (next == null) break
                if (next.tagName.toLowerCase() == "input") {
                    next.focus()
                    break
                }
            }
        }
    }, false)

    const formatTime = (seconds) => {
        var minutes = Math.floor(seconds / 60)
        var remainingSeconds = seconds % 60
        return minutes + ':' + remainingSeconds.toString().padStart(2, '0')
    }

    const controlPin = (type) => {
        var pins = pin_holder.querySelectorAll('input')
        var result = ""

        pins.forEach(function(e) {
            if(type == "time_out"){
                e.disabled = true
                e.style = "background: var(--hoverBG)"
            } else if(type == "regenerate"){
                e.disabled = false
                e.value = ""
                e.style = "background: var(--whiteColor)"
            } else if(type == "invalid"){
                e.value = ""
                e.style = "border: 1.5px solid var(--warningBG)"
            } else if(type == "fetch"){
                result += e.value
            }
        })

        return result
    }

    const validatePin = () => {
        var pins = pin_holder.querySelectorAll('input')
        var is_empty = false

        pins.forEach(function(e) {
            if(e.value == "" || e.value == null){
                is_empty = true
                return
            }
        })

        if(is_empty == false){
            const token = controlPin('fetch')
            validateToken(token)
        }
    }

    const validateToken = (token) => {
        $.ajax({
            url: `/api/v1/register/account`,
            dataType: 'json',
            contentType: 'application/json',
            type: "POST",
            data: JSON.stringify({
                username: $('#username').val(),
                email: $('#email').val(),
                password: $('#password').val(),
                token: token
            }),
            beforeSend: function (xhr) {
                Swal.showLoading()
            }
        })
        .done(function (response) {            
            const data = response
            Swal.hideLoading()

            $('#start-browsing-holder-btn').html(`<a class='btn btn-success ms-1 mb-1' href='/'><i class="fa-solid fa-arrow-right"></i> Start Browsing</a>`)
            $('#token-section').html(`
                <h6 class="text-center mb-4">Account is validated. Welcome to GudangKu</h6>
                <a class='btn btn-success ms-1 mb-1' href='/'><i class="fa-solid fa-arrow-right"></i> Start Browsing</a>
            `).attr('style', 'height: auto !important; min-height: auto !important;')

            $('#service_section').toggleClass('d-none d-block')
            $('html, body').animate({
                scrollTop: $('#service_section').offset().top
            }, [])
            localStorage.setItem('token_key',response.token)

            $('#indicator-profile').removeClass('step-active').addClass('step-finish')
            $('#indicator-service').addClass('step-active')
            $('.step-mobile .title').text("Stay Updated!")
            $('.step-mobile .caption').text('Sync your account to another Platform. Like Telegram and Line')
            $('.progress-bar').css('width', '66%').attr('aria-valuenow', 66) 
            $('.step-mobile .caption').html(`
                <div class="d-flex flex-wrap gap-2 mt-2">
                    <a class="btn btn-success px-2 py-1" href='/'>
                        <i class="fa-solid fa-arrow-right"></i> Go to Dashboard
                    </a>
                    <a class="btn btn-success px-2 py-1" href='/login'>
                        <i class="fa-solid fa-house"></i> Back to Login
                    </a>
                    <a class="btn btn-primary px-2 py-1">
                        <i class="fa-solid fa-mobile-screen"></i> Get Mobile Version
                    </a>
                </div>
            `)
        
            Swal.fire("Success!", data.message, "success")
        })
        .fail(function (response, xhr, ajaxOptions, thrownError) {
            generateAPIError(response, true)
            var pins = pin_holder.querySelectorAll('input')
            var is_empty = false

            pins.forEach(function(e, index) {
                e.value = ""
                if (index === 0) {
                    e.focus()
                }
            })
        })
    }
    
    const startTimer = (duration) => {
        var remain = duration

        const updateTimer = () => {
            timer.innerHTML = formatTime(remain)

            if (remain > 0) {
                remain--
                setTimeout(updateTimer, 1000)

                if (remain <= 180) {
                    timer.style = "color: var(--warningBG)"
                }
            } else {
                token_msg.innerHTML = "<span class='text-danger'>Time's up, please try again</span>"
                controlPin("time_out")
            }
        }
        updateTimer()
    }

    pinContainer.addEventListener('keydown', function (event) {
        var target = event.srcElement
        target.value = ""
    }, false)

    $(document).ready(function() {
        $('#checkTerm').click(function() {
            if ($(this).is(':checked')) {

            } else {
                $('#username, #email, #password, #password_validation').val('')
            }
        })

        $('#btn-regenerate-token').on('click', function(){
            $.ajax({
                url: `/api/v1/register/regen_token`,
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify({
                    username:$('#username').val(),
                    email:$('#email').val()
                }), 
                type: "POST",
                beforeSend: function (xhr) {
                    Swal.showLoading()
                }
            })
            .done(function (response) {
                startTimer(900)
                $('html, body').animate({
                    scrollTop: $('#token-section').offset().top
                }, [])

                let data = response
                Swal.hideLoading()
                Swal.fire(`Token ${data.status}`, data.message, data.status)
            })
            .fail(function (response, textStatus, errorThrown) {
                generateAPIError(response, true)
            })
        })

        $('#btn-register-acc').on('click', function(){
            if(validateInput('text', 'username', 36, 6) && validateInput('text', 'password', 36, 6) && validateInput('text', 'email', 255, 10)){
                if($('#password').val() == $('#password_validation').val()){
                    if($('#email').val().includes("gmail")){
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
                                Swal.showLoading()
                            },
                            success: function(response) {
                                Swal.close()
                                $('#checkTerm').attr('disabled', true)
                                $('#username, #email, #password, #password_validation').attr('readonly',true)
                                $('#token-section').removeClass('d-none')
                                $('#btn-register-acc').remove()
                                $(this).attr('disabled', true)
                                startTimer(900)
                                $('html, body').animate({
                                    scrollTop: $('#token-section').offset().top
                                }, [])

                                let data = response
                                Swal.fire(`Token ${data.status}`, data.message, data.status)
                            },
                            error: function(response, jqXHR, textStatus, errorThrown) {
                                generateAPIError(response, true)
                            }
                        })
                    } else {
                        Swal.fire("Oops!", 'Email must be at @gmail format',"error")
                    }
                } else {
                    Swal.fire("Oops!", `Your password validation is not same`, "error")
                }
            } else {
                Swal.fire("Oops!", `Some field may not valid. Check again!`, "error")
            }
        })
    })
</script>