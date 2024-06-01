<style>
    .toast-body.rounded-bottom{
        background:var(--toast2);
        color:var(--text);
    }
    .toast-header{
        background:var(--toast1);
        color:var(--text);
    }
</style>

@if(Session::has('success_mini_message'))
    <div class="position-fixed bottom-0 end-0 p-4" style="z-index: 11">
    <div id="success_toast" class="toast hide shadow rounded-top" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <img class="mx-2" src="{{asset('images/Success.png')}}" alt='success.png' style='width:22px;'>
            <h6 class="me-auto mt-1 ">Success</h6>
            <small>Recently</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body rounded-bottom">
            {{ Session::get('success_mini_message') }}
        </div>
    </div>
    </div>
@endif

<script>
    //Modal setting.
    $(window).on('load', function() {
        $('#success_toast').toast('show');
    });
</script>