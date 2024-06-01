<label>Username</label>
<input type="text" name="username" value="{{$profile->username}}" class="form-control mt-2"/><br>
<label>Email</label>
<input type="email" name="email" value="{{$profile->email}}" class="form-control mt-2"/><br>
<div class="d-flex justify-content-between">
    <label class="mt-3">Telegram User ID</label>
    @if($profile->telegram_is_valid)
        <label class="mt-3 text-success" style="font-weight:600;"><i class="fa-solid fa-check"></i> ID is Validated!</label>
    @else
        <form action="/profile/validate_telegram" method="POST">
            @csrf
            <button class="btn btn-danger" type="submit"><i class="fa-solid fa-triangle-exclamation"></i> Your ID is not validated. Validate Now!</button>
        </form>
    @endif
</div>
<input type="number" name="telegram_user_id" value="{{$profile->telegram_user_id}}" class="form-control mt-2"/><br>
<label class="fst-italic">Joined since <span class="date_holder">{{$profile->created_at}}</span></label>

<script>
    const date_holder = document.querySelectorAll('.date_holder');

    date_holder.forEach(e => {
        const date = new Date(e.textContent);
        e.textContent = getDateToContext(e.textContent, "datetime");
    });
</script>