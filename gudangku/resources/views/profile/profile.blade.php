<label>Username</label>
<input type="text" name="username" value="{{$profile->username}}" class="form-control mt-2"/><br>
<label>Email</label>
<input type="email" name="email" value="{{$profile->email}}" class="form-control mt-2"/><br>
<label class="fst-italic">Joined since <span class="date_holder">{{$profile->created_at}}</span></label>

<script>
    const date_holder = document.querySelectorAll('.date_holder');

    date_holder.forEach(e => {
        const date = new Date(e.textContent);
        e.textContent = getDateToContext(e.textContent, "datetime");
    });
</script>