<a class="btn btn-primary" href="/login" style="background:var(--primaryColor) !important; float:right;">
    @if(session()->get('username_key') != null && session()->get('username_key') != '')
        <i class="fa-solid fa-user mx-1"></i> {{session()->get('username_key')}}
    @else 
        <i class="fa-solid fa-arrow-right-to-bracket mx-1"></i> Sign In
    @endif
</a>