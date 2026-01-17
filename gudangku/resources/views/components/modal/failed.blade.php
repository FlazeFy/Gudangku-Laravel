<style>
    .modal-content {
        background: var(--darkColor);
        border: none;
        border: 2.5px solid var(--primaryColor);
    }
</style>

@if(Session::has('failed_message'))
    <div class="modal fade" id="error_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body text-center">
                <img src="{{asset('images/Failed.png')}}" alt='failed.png' class="d-block mx-auto" style="max-width:120px"><br>
                <h5 class="modal-title mt-1 fw-bold" id="exampleModalLabel">Failed</h5>
                <h7 class="m-2">{{ Session::get('failed_message') }}</h7>
                <hr>
                <button class="btn btn-danger rounded-pill px-4 mt-3" data-bs-dismiss="modal">Continue</button>
            </div>
        </div>
    </div>
    </div>
@endif

<script>
    $(window).on('load', function() {
        $('#error_modal').modal('show')
    })
</script>