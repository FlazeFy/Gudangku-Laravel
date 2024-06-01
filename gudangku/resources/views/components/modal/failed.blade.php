<style>
    .modal-content{
        background-color: var(--whiteColor);
        border:none;
    }
</style>

@if(Session::has('failed_message'))
    <div class="modal fade" id="error_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body text-center">
                <img src="{{asset('images/Failed.png')}}" alt='failed.png' style='width:30%;'><br>
                <h5 class="modal-title mt-4" id="exampleModalLabel">Failed</h5>
                <h7 class="m-2">{{ Session::get('failed_message') }}</h7>
                <hr>
                <button class="btn btn-danger rounded-pill px-4" data-bs-dismiss="modal">Try again</button>
            </div>
        </div>
    </div>
    </div>
@endif

<script>
    //Modal setting.
    $(window).on('load', function() {
        $('#error_modal').modal('show');
    });
</script>