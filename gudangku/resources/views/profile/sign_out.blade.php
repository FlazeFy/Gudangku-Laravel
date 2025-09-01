<a class="btn btn-danger btn-main bottom" data-bs-toggle="modal" data-bs-target="#modalSignOut" id="sign_out_btn"><i class="fa-solid fa-right-from-bracket" style="font-size:var(--textXLG);"></i>@if(!$isMobile)  Sign Out @endif</a>
<div class="modal fade" id="modalSignOut" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fw-bold" id="exampleModalLabel">Delete</h2>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" id="close_sign_out_btn" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <form action="/profile/sign_out" method="POST" id="form-sign-out">
                    @csrf
                    <p>Are you sure want to leave this account?</p>
                    <a class="btn btn-danger mt-4" onclick="sign_out()" id="validation_sign_out_btn">Yes, Sign Out</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function sign_out() {
        $.ajax({
            url: "/api/v1/logout",
            type: "GET",
            headers: {
                "Content-Type": "application/json",
                "Authorization": "Bearer <?= session()->get("token_key"); ?>"
            },
            success: function(data, textStatus, jqXHR) {
                sessionStorage.clear()
                localStorage.clear()
                $('#form-sign-out').submit()
            },
            error: function(jqXHR, textStatus, errorThrown) {
                if (jqXHR.status == 401) {
                    sessionStorage.clear()
                    localStorage.clear()
                    window.location.href = "/login"
                }
            }
        });
    }
</script>