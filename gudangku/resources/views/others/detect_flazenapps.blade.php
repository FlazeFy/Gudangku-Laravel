<style>
    :root {   
        --flazenPrimaryColor: #fcf2e5;
        --flazenSecondaryColor: #f6f6f6;
        --flazenDarkColor: #343146;
        --flazenGreyColor: #979797;
        --flazenDangerColor: #dc5987;
        --flazenWarningColor: #eac661;
        --flazenInfoColor: #845c98;
    }
    #alert-flazenapps {
        position: fixed;
        bottom: var(--spaceLG);
        left: var(--spaceLG);
        z-index: 999;
    }
    #alert-flazenapps .content {
        background: var(--flazenPrimaryColor);
        border-radius: var(--roundedLG);
        width:480px;
    }
    #alert-flazenapps h1 {
        font-size:calc(var(--textJumbo)*1.25);
        color: var(--flazenDarkColor) !important;
    }
    #alert-flazenapps p, #alert-flazenapps a {
        font-size:var(--textMD);
    }
    #alert-flazenapps .btn-primary {
        background:var(--flazenInfoColor) !important;
    }
    #alert-flazenapps .btn-danger {
        background:var(--flazenDangerColor) !important;
    }
    #alert-flazenapps .btn-link {
        background:var(--flazenWarningColor) !important;
        padding: var(--spaceMini) var(--spaceSM) !important;
    }
    #alert-flazenapps .btn-primary, #alert-flazenapps .btn-danger {
        border: none;
        padding: var(--spaceSM) var(--spaceMD) !important;
    }
</style>

<div id="alert-flazenapps"></div>

<script>
    const url_params = new URLSearchParams(window.location.search)
    const opened_from = url_params.get('opened_from')
    const is_from_flazenapps = sessionStorage.getItem('is_from_flazenapps')

    if (opened_from === 'flazenapps') {
        sessionStorage.setItem('is_from_flazenapps', 'true')

        const cleanUrl = window.location.origin + window.location.pathname
        window.location.href = cleanUrl
    } else if (is_from_flazenapps === 'true') {
        $('#alert-flazenapps').html(`
            <a class="btn btn-primary" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1">FlazenApps Was Here!</a>
            <div class="collapse multi-collapse mt-3" id="multiCollapseExample1">
                <div class="content">
                    <h2 class="mb-2">Hey FlazenApps Was Here!</h2>
                    <p class="text-dark mb-2">We know that you have opened <b>GudangKu</b> from <b>FlazenApps</b> platform, are you want to use our testing account so you can explore this apps easily?</p>
                    <a class="btn btn-primary me-2" id="login_test_acc">Yeah, Bring Me There!</a>
                    <a class="btn btn-danger" id="reject_test_acc">Maybe, Later</a>
                    <hr>
                    <p class="text-dark mb-2">Want to ask something? or you want to give us a feedback?</p>
                    <a class="btn btn-link me-2" href='https://t.me/flazenapps'><i class="fa-brands fa-telegram me-1"></i> t.me/flazenapps</a>
                    <a class="btn btn-link" href="mailto:flazen.edu@gmail.com?subject=Hello%20FlazenApps&body=I%20want%20to%20know%20more%20about%20GudangKu"><i class="fa-solid fa-envelope me-1"></i> flazen.edu@gmail.com</a>
                </div>
            </div>
        `)
    } else {
        $('#alert-flazenapps').remove()
    }

    $(document).on('click','#login_test_acc',function(){
        Swal.showLoading()
        $.ajax({
            url: '/api/v1/login',
            type: 'POST',
            data: {
                username: 'tester_flazenapps',
                password: 'nopass123'
            },
            dataType: 'json',
            success: function(response) {
                Swal.hideLoading()
                window.location.href = '/'
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                generateAPIError(response, true)
            }
        });
    })
    $(document).on('click','#reject_test_acc',function(){
        sessionStorage.removeItem('is_from_flazenapps')
        window.location.href = '/'        
    })
</script>
