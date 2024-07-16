<style>
    .history-box {
        padding: var(--spaceXMD);
        margin-bottom: var(--spaceXMD);
        border-radius: var(--roundedMD);
        border: 1.5px solid var(--primaryColor);
    }
</style>

@foreach($history as $h)
    <div class="history-box">
        <div class="d-flex justify-content-between">
            <div class="">
                <h2>{{$h->history_type}} from item called {{$h->history_context}}</h2>
            </div>
            <div class="pe-2 ps-3">
                <button class="btn btn-danger d-block mx-auto" data-bs-toggle="modal" data-bs-target="#modalDelete_{{$h->id}}"><i class="fa-solid fa-trash mx-2"></i></button>
                <div class="modal fade" id="modalDelete_{{$h->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2 class="modal-title fw-bold" id="exampleModalLabel">Delete</h2>
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                            <div class="modal-body">
                                <form action="/history/delete/{{$h->id}}" method="POST">
                                    @csrf
                                    <h2>Delete this history about {{$h->history_type}} from item called {{$h->history_context}}?</h2>
                                    <button class="btn btn-danger mt-4" type="submit">Yes, Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach

<hr>
<div class="my-3">
    {{ $history->links() }}
</div>

