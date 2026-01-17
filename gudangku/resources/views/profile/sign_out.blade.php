<a class="btn btn-danger me-2" data-bs-toggle="modal" data-bs-target="#modalSignOut" id="sign_out_btn">
    <i class="fa-solid fa-right-from-bracket"></i>
    <span class="d-none d-md-inline"> Sign Out</span>
</a>
<div class="modal fade" id="modalSignOut" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="exampleModalLabel">Sign Out</h5>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" id="close_sign_out_btn" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <form action="/profile/sign_out" method="POST" id="form-sign-out">
                    @csrf
                    <p>Are you sure want to leave this account?</p>
                    <a class="btn btn-danger mt-4" onclick="signOut()" id="validation_sign_out_btn">Yes, Sign Out</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const signOut = () => {
        $.ajax({
            url: "/api/v1/logout",
            type: "POST",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${token}`
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