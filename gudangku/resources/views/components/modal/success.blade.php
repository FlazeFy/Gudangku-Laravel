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

@if(Session::has('success_message'))
    <div class="modal fade" id="success_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body text-center">
                <img src="{{asset('images/Success.png')}}" alt='success.png' style='width:30%;'><br>
                <h5 class="modal-title mt-4" id="exampleModalLabel">Success</h5>
                <h7 class="m-2 text-secondary">{{ Session::get('success_message') }}</h7>
                <hr>
                <button class="btn btn-success rounded-pill px-4" data-bs-dismiss="modal">Continue</button>
            </div>
        </div>
    </div>
    </div>
@endif

<script>
    //Modal setting.
    $(window).on('load', function() {
        $('#success_modal').modal('show');
    });
</script>