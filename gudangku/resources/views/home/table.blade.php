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
            <tr <?php if($in['deleted_at'] != null){ echo 'style="background:rgba(221, 0, 33, 0.15);"';} ?>>
                <th scope="row" <?php if($in->reminder_id){ echo'rowspan="2"'; } ?>>{{$i}}</th>
                <td <?php if($in->reminder_id){ echo'rowspan="2"'; } ?>>
                    @if($in->inventory_image != null)
                        <button type="button" class="btn btn-image" data-bs-toggle="modal" data-bs-target="#zoom_image-{{$in->id}}"><img src="{{$in->inventory_image}}" title="{{$in->inventory_name}}"></button>

                        <!-- Modal -->
                        <div class="modal fade" id="zoom_image-{{$in->id}}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <div>
                                        <h4 class="modal-title fw-bold" id="staticBackdropLabel">{{$in->inventory_name}}</h4>
                                        <h5 class="modal-title" id="staticBackdropLabel">{{$in->inventory_category}}</h5>
                                    </div>
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                                </div>
                                <div class="modal-body">
                                    <img class="img img-fluid" style="border-radius: var(--roundedMD);" src="{{$in->inventory_image}}" title="{{$in->inventory_name}}">
                                </div>
                            </div>
                        </div>
                        </div>
                    @endif

                    @if($in['is_favorite'])
                        <i class="fa-solid fa-bookmark" style="color:var(--primaryColor);" title="Favorite"></i>
                    @endif
                    {{$in['inventory_name']}}
                </td>
                <td <?php if($in->reminder_id){ echo'rowspan="2"'; } ?>>{{$in['inventory_category']}}</td>
                <td <?php if($in->reminder_id){ echo'rowspan="2"'; } ?>>
                    @if($in['inventory_desc'] != null)
                        {{$in['inventory_desc']}} 
                    @else 
                        -
                    @endif
                </td>
                <td <?php if($in->reminder_id){ echo'rowspan="2"'; } ?>>
                    @if($in['inventory_merk'] != null)
                        {{$in['inventory_merk']}} 
                    @else 
                        -
                    @endif
                </td>
                <td <?php if($in->reminder_id){ echo'rowspan="2"'; } ?>>{{$in['inventory_room']}}</td>
                <td <?php if($in->reminder_id){ echo'rowspan="2"'; } ?>>
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
                <td <?php if($in->reminder_id){ echo'rowspan="2"'; } ?>>Rp. {{number_format($in['inventory_price'], 0, ',', '.')}}</td>
                <td <?php if($in->reminder_id){ echo'rowspan="2"'; } ?>>{{$in['inventory_vol']}} {{$in['inventory_unit']}}</td>
                <td <?php if($in->reminder_id){ echo'rowspan="2"'; } ?>>
                    @if($in['inventory_capacity_unit'] == 'percentage')
                        {{$in['inventory_capacity_vol']}}%
                    @elseif($in['inventory_capacity_unit'] == null)
                        -
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
                <td>
                    <form action="/inventory/favToggleInventory/{{$in['id']}}" method="POST">
                        @csrf
                        <input hidden name="is_favorite" value="<?php 
                            if($in['is_favorite'] == '1'){
                                echo '0';
                            } else {
                                echo '1';
                            }
                        ?>"/>
                        <input hidden name="inventory_name" value="{{$in['inventory_name']}}"/>
                        <button class="btn btn-danger" type="submit" <?php if($in['is_favorite'] == '1'){echo'style="background:var(--dangerBG) !important; border:none;"';}?>>
                        <i class="fa-solid fa-heart" style="font-size:var(--textXLG);"></i></button>
                    </form>
                </td>
                <td><button class="btn btn-success <?php if($in->reminder_id){ echo"bg-success border-0"; } ?>"><i class="fa-solid <?php if($in->reminder_id){ echo"fa-bell"; } else { echo"fa-bell-slash"; } ?>" style="font-size:var(--textXLG);"></i></button></td>
                <td>
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modal<?php
                        if($in['deleted_at'] != null){
                            echo "Recover"; 
                        } else {
                            echo "Edit";
                        }
                        ?>_{{$in->id}}">
                        @if($in['deleted_at'] == null)
                            <i class="fa-solid fa-pen-to-square" style="font-size:var(--textXLG);"></i>
                        @else 
                            <i class="fa-solid fa-rotate" style="font-size:var(--textXLG);"></i>
                        @endif
                    </button>
                    @if($in['deleted_at'] == null)

                    @else
                        <div class="modal fade" id="modalRecover_{{$in->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h2 class="modal-title fw-bold" id="exampleModalLabel">Recover</h2>
                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="/inventory/recoverInventory/{{$in['id']}}" method="POST">
                                            @csrf
                                            <input hidden name="inventory_name" value="{{$in['inventory_name']}}"/>
                                            <h2>Recover this item "{{$in['inventory_name']}}"?</h2>
                                            <button class="btn btn-success mt-4" type="submit">Yes, Recover</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </td>
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
                                    <h2 class="modal-title fw-bold" id="exampleModalLabel">Delete</h2>
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                                </div>
                                <div class="modal-body">
                                    <form action="/<?php 
                                        if($in['deleted_at'] == null){
                                            echo "inventory/deleteInventory/".$in['id'];
                                        } else {
                                            echo "inventory/destroyInventory/".$in['id'];
                                        }
                                        ?>" method="POST">
                                        @csrf
                                        <input hidden name="inventory_name" value="{{$in['inventory_name']}}"/>
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

            @if($in->reminder_id)
                <tr style="border-style : hidden!important;">
                    <td colspan="5">
                        <div class="box-reminder">
                            <h5 class="fw-bold mb-0">Reminder | {{ucwords(str_replace("_"," ",$in->reminder_type))}}</h5>
                            <p>{{$in->reminder_desc}}</p>
                            <p class="mt-2 mb-0">Time : {{ucwords(str_replace("_"," ",$in->reminder_context))}}</p>
                            <p class="my-0">Created At : {{date('Y-m-d H:i', strtotime($in->reminder_created_at))}}</p><hr class="my-2">

                            <button class="btn btn-danger me-2" data-bs-toggle="modal" data-bs-target="#modalDeleteReminder_{{$in->reminder_id}}" style="padding: var(--spaceMini) var(--spaceSM) !important;"> 
                                <i class="fa-solid fa-trash" style="font-size:var(--textSM);"></i>
                            </button>
                            <div class="modal fade" id="modalDeleteReminder_{{$in->reminder_id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h2 class="modal-title fw-bold" id="exampleModalLabel">Delete</h2>
                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="/inventory/destroyReminder/{{$in['reminder_id']}}" method="POST">
                                                @csrf
                                                <input hidden name="reminder_desc" value="{{$in['reminder_desc']}}"/>
                                                <h2><span class="text-danger">Permentally Delete</span>
                                                    this reminder "{{$in['reminder_desc']}}"?
                                                </h2>
                                                <button class="btn btn-danger mt-4" type="submit">Yes, Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button class="btn btn-warning me-2" data-bs-toggle="modal" data-bs-target="#modalEditReminder_{{$in->reminder_id}}" style="padding: var(--spaceMini) var(--spaceSM) !important;"> 
                                <i class="fa-solid fa-pen-to-square" style="font-size:var(--textSM);"></i>
                            </button>
                            <div class="modal fade" id="modalEditReminder_{{$in->reminder_id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h2 class="modal-title fw-bold" id="exampleModalLabel">Edit Reminder</h2>
                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="/inventory/editReminder/{{$in['reminder_id']}}" method="POST">
                                                @csrf
                                                <label>Description</label>
                                                <textarea name="reminder_desc" class="form-control mt-2">{{$in['reminder_desc']}}</textarea>

                                                <label>Type</label>
                                                <select class="form-select mt-2" name="reminder_type" aria-label="Default select example">
                                                    @foreach($dct_reminder_type as $dct)
                                                        <option value="{{$dct['dictionary_name']}}" <?php if($in['reminder_type'] == $dct['dictionary_name']){ echo'selected'; } ?>>{{$dct['dictionary_name']}}</option>
                                                    @endforeach
                                                </select>

                                                <label>Context</label>
                                                <select class="form-select mt-2" name="reminder_context" aria-label="Default select example">
                                                    @foreach($dct_reminder_context as $dct)
                                                        <option value="{{$dct['dictionary_name']}}" <?php if($in['reminder_context'] == $dct['dictionary_name']){ echo'selected'; } ?>>{{$dct['dictionary_name']}}</option>
                                                    @endforeach
                                                </select>

                                                <button class="btn btn-success mt-4" type="submit">Save Changes</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button class="btn btn-success" data-bs-toggle="modal" onclick="loadDatatableInventoryReminder('<?= $in->reminder_id; ?>')" data-bs-target="#modalCopyReminder_{{$in->reminder_id}}" style="padding: var(--spaceMini) var(--spaceSM) !important;">
                                <i class="fa-solid fa-copy" style="font-size:var(--textSM);"></i>
                            </button>
                            <div class="modal fade" id="modalCopyReminder_{{$in->reminder_id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h2 class="modal-title fw-bold" id="exampleModalLabel">Copy Reminder</h2>
                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="/inventory/copyReminder/{{$in['reminder_id']}}" method="POST">
                                                @csrf
                                                @php($tb=0)
                                                <input hidden value="{{$in->reminder_context}}" name="reminder_context">
                                                <input hidden value="{{$in->reminder_desc}}" name="reminder_desc">
                                                <input hidden value="{{$in->reminder_type}}" name="reminder_type">
                                                <table class="table" id="tb-inventory-name-{{$in->reminder_id}}">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">
                                                                <span id="checked_all_holder_btn">
                                                                    <a class="btn btn-primary" onclick="toogleCheck()" style="font-size:var(--textMD); padding: var(--spaceMini) var(--spaceSM) !important;">Check All</a>
                                                                </span>
                                                            </th>
                                                            <th scope="col">Inventory Name</th>
                                                            <th scope="col">Category</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($inventory_name as $inn)
                                                            @if($inn->inventory_name != $in->inventory_name)
                                                                <tr>
                                                                    <td>
                                                                        <div class="form-check">
                                                                            <input class="form-check-input check-inventory" type="checkbox" name="inventory_id[]" value="{{$inn->id}}" id="flexCheckDefault">
                                                                        </div>
                                                                    </td>
                                                                    <td>{{$inn['inventory_name']}}</td>
                                                                    <td>{{$inn['inventory_category']}}</td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    </tbody>
                                                </table><br>
                                                @php($tb++)
                                                <input hidden name="reminder_desc" value="{{$in['reminder_desc']}}"/>
                                                <h2>Are you sure to copy this reminder "{{$in['reminder_desc']}}" to inventory <span id="inventory_selected_name"></span>?</h2>
                                                <button class="btn btn-success mt-4" type="submit">Yes, Copy</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="15"></td>
                </tr>
            @endif
            @php($i++)
        @endforeach
    </tbody>
</table>

<script>
    let toogle_check = 0
    const date_holder = document.querySelectorAll('.date_holder')

    date_holder.forEach(e => {
        const date = new Date(e.textContent);
        e.textContent = getDateToContext(e.textContent, "datetime")
    });

    function loadDatatableInventoryReminder(id){
        $(`#tb-inventory-name-${id}`).DataTable({
            // columnDefs: [
            //     { targets: 0, orderable: true, searchable: true},
            //     { targets: 1, orderable: true, searchable: false },
            //     { targets: '_all', orderable: false, searchable: false}
            // ],
        });
    }

    function toogleCheck(){
        const checked_all_holder_btn = document.getElementById('checked_all_holder_btn')
        const inventoryCheck = document.querySelectorAll('.check-inventory')
        
        if(toogle_check % 2 != 0){
            inventoryCheck.forEach(el => {
                el.checked = false
            });
            checked_all_holder_btn.innerHTML = `<a class="btn btn-primary" onclick="toogleCheck()" 
                style="font-size:var(--textMD); padding: var(--spaceMini) var(--spaceSM) !important;">Check All</a>`
        } else {
            inventoryCheck.forEach(el => {
                el.checked = true
            });
            checked_all_holder_btn.innerHTML = `<a class="btn btn-danger" onclick="toogleCheck()" 
                style="font-size:var(--textMD); padding: var(--spaceMini) var(--spaceSM) !important;">Uncheck All</a>`
        }

        toogle_check++
    }
</script>