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
                <button class="btn btn-danger d-block mx-auto"><i class="fa-solid fa-trash mx-2"></i></button>
            </div>
        </div>
    </div>
@endforeach