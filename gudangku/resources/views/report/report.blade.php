@foreach($report as $r)
    <button class="report-box">
        <div class="d-flex justify-content-between mb-2">
            <div>
                <h3 style="font-weight:500; font-size:var(--textJumbo);">{{$r->report_title}}</h3>
            </div>
            <div>
                <span class="bg-success text-white rounded-pill px-3 py-2">{{$r->report_category}}</span>
            </div>
        </div>
        @if($r->report_desc)
            <p class="mt-2">{{$r->report_desc}}</p>
        @else 
            <p class="text-secondary fst-italic mt-2">- No Description Provided -</p>
        @endif
        <br>
        <h3>Items : </h3>
        <p>{{$r->report_items}}</p>

        @if($r->report_category == 'Shopping Cart' || $r->report_category == 'Wishlist')
            <div class="d-flex justify-content-between mt-3">
                <div>
                    <h3 class="fw-bold" style="font-size:var(--textJumbo);">Total Price: Rp. {{number_format($r->item_price)}}</h3>
                </div>
                <div>
                    <h3 class="fw-bold" style="font-size:var(--textJumbo);">Total Item : {{$r->total_item}}</h3>
                </div>
            </div>
        @endif
    </button>
@endforeach