<style>
    .toast-body.rounded-bottom{
        background: var(--darkColor);
        color: var(--whiteColor);
    }
    .toast-header{
        background: var(--darkColor);
        color: var(--whiteColor);
    }
    .toast {
        border: 2.5px solid var(--primaryColor);
    }
</style>

@if(Session::has('success_mini_message'))
    <div class="position-fixed bottom-0 end-0 p-4" style="z-index: 11">
    <div id="success_toast" class="toast hide shadow rounded-top" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <img class="mx-2" src="{{asset('images/Success.png')}}" alt='success.png' style='width:22px;'>
            <h6 class="me-auto mt-1 ">Success</h6>
            <small>Recently</small>
            <button type="button" class="btn-danger py-1 px-2 ms-2" data-bs-dismiss="toast" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
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