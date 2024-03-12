<table class="table">
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
                <td><button class="btn btn-primary"><i class="fa-solid fa-circle-info" style="font-size:var(--textXLG);"></i></button></td>
                <td><button class="btn btn-primary"><i class="fa-solid fa-heart" style="font-size:var(--textXLG);"></i></button></td>
                <td><button class="btn btn-success"><i class="fa-solid fa-bell" style="font-size:var(--textXLG);"></i></button></td>
                <td><button class="btn btn-warning"><i class="fa-solid fa-pen-to-square" style="font-size:var(--textXLG);"></i></button></td>
                <td><button class="btn btn-danger"><i class="fa-solid fa-trash" style="font-size:var(--textXLG);"></i></button></td>
            </tr>
        @endforeach
    </tbody>
</table>