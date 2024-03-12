<table class="table" id="inventory_tb">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Name</th>
            <th scope="col">Category</th>
            <th scope="col">Description</th>
            <th scope="col">Merk</th>
            <th scope="col">Room</th>
            <th scope="col">Storage / Rack</th>
            <th scope="col">Price</th>
            <th scope="col">Unit</th>
            <th scope="col">Capacity</th>
            <th scope="col">Info</th>
            <th scope="col">Favorite</th>
            <th scope="col">Reminder</th>
            <th scope="col">Edit</th>
            <th scope="col">Delete</th>
        </tr>
    </thead>
    <tbody>
        @php($i = 1)
        @foreach($inventory as $in)
            <tr>
                <th scope="row">{{$i}}</th>
                <td>{{$in['inventory_name']}}</td>
                <td>{{$in['inventory_category']}}</td>
                <td>
                    @if($in['inventory_desc'] != null)
                        {{$in['inventory_desc']}} 
                    @else 
                        -
                    @endif
                </td>
                <td>{{$in['inventory_merk']}}</td>
                <td>{{$in['inventory_room']}}</td>
                <td>
                    @if($in['inventory_storage'] != null)
                        {{$in['inventory_storage']}} 
                    @else 
                        -
                    @endif
                    / 
                    @if($in['inventory_rack'] != null)
                        {{$in['inventory_rack']}}
                    @else 
                        -
                    @endif
                </td>
                <td>Rp. {{number_format($in['inventory_price'], 0, ',', '.')}}</td>
                <td>{{$in['inventory_volume']}} {{$in['inventory_unit']}}</td>
                <td>
                    @if($in['inventory_capacity_unit'] == 'percentage')
                        {{$in['inventory_capacity_vol']}}%
                    @endif
                </td>
                <td>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalInfoProps_{{$in->id}}"><i class="fa-solid fa-circle-info" style="font-size:var(--textXLG);"></i></button>
                    <div class="modal fade" id="modalInfoProps_{{$in->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h2 class="modal-title fw-bold" id="exampleModalLabel">Properties</h2>
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                                </div>
                                <div class="modal-body">
                                    <h2>Created At</h2>
                                    <h6 class="date_holder">{{$in['created_at']}}</h6>
                                    <br><h2>Updated At</h2>
                                    @if($in['updated_at'] != null)
                                        <h6 class="date_holder">{{$in['updated_at']}}</h6>
                                    @else 
                                        <h6>-</h6>
                                    @endif
                                    <br><h2>Deleted At</h2>
                                    @if($in['deleted_at'] != null)
                                        <h6 class="date_holder">{{$in['deleted_at']}}</h6>
                                    @else 
                                        <h6>-</h6>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
                <td><button class="btn btn-primary"><i class="fa-solid fa-heart" style="font-size:var(--textXLG);"></i></button></td>
                <td><button class="btn btn-success"><i class="fa-solid fa-bell" style="font-size:var(--textXLG);"></i></button></td>
                <td><button class="btn btn-warning"><i class="fa-solid fa-pen-to-square" style="font-size:var(--textXLG);"></i></button></td>
                <td>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalDelete_{{$in->id}}">
                        @if($in['deleted_at'] == null)
                            <i class="fa-solid fa-trash" style="font-size:var(--textXLG);"></i>
                        @else 
                            <i class="fa-solid fa-fire" style="font-size:var(--textXLG);"></i>
                        @endif
                    </button>
                    <div class="modal fade" id="modalDelete_{{$in->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h2 class="modal-title fw-bold" id="exampleModalLabel">Properties</h2>
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                                </div>
                                <div class="modal-body">
                                    <form action="/<?php 
                                        if($in['deleted_at'] == null){
                                            echo "deleteInventory/".$in['id'];
                                        } else {
                                            echo "destroyInventory/".$in['id'];
                                        }
                                        ?>" method="POST">
                                        @csrf
                                        <h2>
                                            @if($in['deleted_at'] == null)
                                                Delete
                                            @else 
                                                <span class="text-danger">Permentally Delete</span>
                                            @endif
                                             this item "{{$in['inventory_name']}}"?
                                        </h2>
                                        <button class="btn btn-danger mt-4" type="submit">Yes, Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<script>
    const date_holder = document.querySelectorAll('.date_holder');

    date_holder.forEach(e => {
        const date = new Date(e.textContent);
        e.textContent = getDateToContext(e.textContent, "datetime");
    });
</script>