<style>
    .autocomplete {
        position: relative;
        display: inline-block;
    }
    .autocomplete-items {
        position: absolute;
        border: 2px solid white;
        z-index: 99;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--darkColor);
        border-radius: var(--roundedLG);
    }
    .autocomplete-items div {
        padding: var(--spaceMD);
        cursor: pointer;
        background: transparent;
        color: var(--whiteColor);
    }
    .autocomplete-items div:hover {
        background: var(--primaryColor);
    }
    .autocomplete-active {
        color: #ffffff;
    }
</style>

<div class="modal fade" id="modalAddReport" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fw-bold" id="exampleModalLabel">Add Report</h2>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col">
                            <label>Title</label>
                            <input name="report_title" class="form-control" type="text" id="report_title" required>

                            <label>Description</label>
                            <textarea name="report_desc" class="form-control mt-2"></textarea>

                            <label>Category</label>
                            <select class="form-select mt-2" name="report_category" aria-label="Default select example">
                                @foreach($dct_cat as $dct)
                                    <option value="{{$dct['dictionary_name']}}">{{$dct['dictionary_name']}}</option>
                                @endforeach
                            </select>

                            <button class="btn btn-success mt-4 w-100" type="submit">Save</button>
                        </div>
                        <div class="col">
                            <label>Item</label>
                            <select class="form-select" id="report_item" onchange="browse_item(this.value)" aria-label="Default select example"></select>
                            <div id="item_form"></div>
                            <div id="item_holder"><div class="alert alert-danger w-100 mt-4"><i class="fa-solid fa-triangle-exclamation"></i> No item selected</div></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    get_list_inventory()
    function get_list_inventory() {
        $.ajax({
                url: "http://127.0.0.1:8000/api/v1/inventory/list",
                datatype: "json",
                type: "get",
                beforeSend: function (xhr) {
                    xhr.setRequestHeader("Accept", "application/json");
                    xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>");
                },
            })
            .done(function (response) {
                let data =  response.data
                $('#report_item').append(`<option selected>- Browse Inventory -</option>`)

                for (var i = 0; i < data.length; i++) {
                    let optionText = `${data[i]['inventory_name']}` +
                        (data[i]['inventory_vol'] != null ? ` @${data[i]['inventory_vol']} ${data[i]['inventory_unit']}` : '');

                    $('#report_item').append(`<option value="${data[i]}">${optionText}</option>`);
                }

                $('#report_item').append(`<option value="add_ext">- Add External Item -</option>`)
                $('#report_item').append(`<option value="copy_report">- Copy From Report -</option>`)
            })
            .fail(function (jqXHR, ajaxOptions, thrownError) {
                // Do someting
            });   
    }

    var countries = [<?php 
        foreach($report as $r){
            echo "'".$r->report_title."',";
        }
    ?>];

    function browse_item(val){
        if(val == 'add_ext'){
            $('#item_form').empty().append(`
                <div class="row">
                    <div class="col-lg-8">
                        <label>Item Name</label>
                        <input name="item_name" class="form-control" type="text" id="item_name">
                    </div>
                    <div class="col-lg-4">
                        <label>Qty</label>
                        <input name="item_qty" class="form-control" type="number" id="item_qty">
                    </div>
                </div>
                <label>Description</label>
                <textarea name="item_desc" id="item_desc" class="form-control mt-2"></textarea>
            `)
        } else if(val == 'copy_report'){
            $('#item_form').empty().append(`
                <label>Report Title</label><br>
                <div class="autocomplete" style="width:300px;">
                    <input id="report_title_template" class="form-control w-100" type="text" name="report_title_template">
                </div>
            `)
            $( document ).ready(function() {
                autocomplete(document.getElementById("report_title_template"), countries)
            });
        } else {
            $('#item_form').empty().append(`
                <div class="row">
                    <div class="col-lg-8">
                        <label>Description</label>
                        <textarea name="item_desc" class="form-control mt-2"></textarea>
                    </div>
                    <div class="col-lg-4">
                        <label>Qty</label>
                        <input name="item_qty" class="form-control" type="number" id="item_qty">
                    </div>
                </div>
            `)
        }
        $('#item_form').append('<a class="btn btn-success mt-3" onclick="add_item()">Add Item</a><hr>')
    }

    function add_item(){
        if($('#item_name').val() != ''){
            $('#item_holder').append(`
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Name, Description</th>
                            <th scope="col">Qty</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row">${$('#item_name').val()} ${$('#item_desc').val()}</th>
                            <td>${$('#item_qty').val()}</td>
                            <td><button class="btn btn-danger"><i class="fa-solid fa-trash"></i></button></td>
                        </tr>
                    </tbody>
                </table>
            `)
        }

        $('#item_name').val('')
        $('#item_qty').val('')
        $('#item_desc').val('')
    }

    function autocomplete(inp, arr) {
        var currentFocus

        inp.addEventListener("input", function(e) {
            var a, b, i, val = this.value

            closeAllLists()
            if (!val) { return false }
            currentFocus = -1

            a = document.createElement("DIV")
            a.setAttribute("id", this.id + "autocomplete-list")
            a.setAttribute("class", "autocomplete-items")
            this.parentNode.appendChild(a)

            for (i = 0; i < arr.length; i++) {
                if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
                    b = document.createElement("DIV")
                    b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>"
                    b.innerHTML += arr[i].substr(val.length)
                    b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>"

                    b.addEventListener("click", function(e) {
                        inp.value = this.getElementsByTagName("input")[0].value
                        closeAllLists()
                    })
                    a.appendChild(b)
                }
            }
        })

        inp.addEventListener("keydown", function(e) {
            var x = document.getElementById(this.id + "autocomplete-list")
            if (x) x = x.getElementsByTagName("div")
            if (e.keyCode == 40) {
                currentFocus++
                addActive(x)
            } else if (e.keyCode == 38) {
                currentFocus--
                addActive(x)
            } else if (e.keyCode == 13) {
                e.preventDefault()
                if (currentFocus > -1) {
                    if (x) x[currentFocus].click()
                }
            }
        })

        function addActive(x) {
            if (!x) return false
            removeActive(x)
            if (currentFocus >= x.length) currentFocus = 0
            if (currentFocus < 0) currentFocus = (x.length - 1)
            x[currentFocus].classList.add("autocomplete-active")
        }

        function removeActive(x) {
            for (var i = 0; i < x.length; i++) {
                x[i].classList.remove("autocomplete-active")
            }
        }

        function closeAllLists(elmnt) {
            var x = document.getElementsByClassName("autocomplete-items")
            for (var i = 0; i < x.length; i++) {
                if (elmnt != x[i] && elmnt != inp) {
                    x[i].parentNode.removeChild(x[i])
                }
            }
        }

        document.addEventListener("click", function (e) {
            closeAllLists(e.target)
        })
    }
</script>
