<label>Username</label>
<input type="text" name="username" value="{{$profile->username}}" class="form-control mt-2"/><br>
<label>Email</label>
<input type="email" name="email" value="{{$profile->email}}" class="form-control mt-2"/><br>
<div class="d-flex justify-content-between">
    <label class="mt-3">Telegram User ID</label>
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
</div>
<input type="text" name="telegram_user_id" value="{{$profile->telegram_user_id}}" class="form-control mt-2" <?php if($validation_telegram){ echo "disabled"; } ?>/><br>
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
    const date_holder = document.querySelectorAll('.date_holder');

    date_holder.forEach(e => {
        const date = new Date(e.textContent);
        e.textContent = getDateToContext(e.textContent, "datetime");
    });
</script>