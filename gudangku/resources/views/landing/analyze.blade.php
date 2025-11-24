<style>
    .bubble {
        background-color: var(--darkColor);
        border-radius: var(--roundedMD);
        padding: var(--spaceMD);
        margin-top: var(--spaceJumbo);
        position: relative;
        font-weight:normal;
        border: 1px solid var(--whiteColor);
    }
    .bubble.bot {
        border-left: 10px solid var(--warningBG);
        text-align: left;
    }
    .bubble.bot::after {
        content: '';
        display: block;
        position: absolute;
        bottom: -30px;
        left: 0;
        border-width: 30px 0 0 30px;
        border-style: solid;
        border-color: var(--warningBG) transparent;
        width: 2px;
        border-radius: 0 0 40px 0;
    }
    .bubble.me {
        border-right: 10px solid var(--successBG);
        text-align: right;
    }
    .bubble.me::after {
        content: '';
        display: block;
        position: absolute;
        bottom: -30px;
        right: 0;
        border-width: 30px 30px 0 0;
        border-style: solid;
        border-color: var(--successBG) transparent;
        width: 2px;
        border-radius: 0 0 0 40px;
    }
</style>
<link rel="stylesheet" href="{{ asset('/usecases/manage_image_v1.0.0.css') }}"/>

<div class="btn-feature fixed mb-3">
    <div class="position-relative">
        @if($isMobile)
            <h2 style="font-size:var(--textJumbo);"><i class="fa-solid fa-robot me-2"></i> Analyze Document</h2>
        @else
            <i class="fa-solid fa-robot" style="font-size:100px"></i>
            <h2 class="mt-3" style="font-size:var(--textJumbo);">Analyze Document</h2>
        @endif
        <div class="position-absolute" style="right:-20px; bottom:-20px;">
            <a id="nav_analyze_btn" title="Open Analyze Section"><i class="fa-solid fa-expand fa-xl"></i></a>
        </div>
    </div>
    <div id='chat-section' class="ms-4 d-none text-start">
        <a class="btn btn-danger" id="nav_analyze_close_btn" title="Close Analyze Section"><i class="fa-solid fa-xmark" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Close @endif</a>
        <div style="font-size:var(--textLG);" class="bubble bot">Do you have a GudangKu generated document and feel lazy to find what inventory contain in the report?
            or maybe your friend share their report and you want to import the inventory found on it? or you want to just copy the inventory?
        </div>
        <div style="font-size:var(--textLG);" class="bubble me" id="upload-analyze-section">
            <form id="analyze_form">
                <div class="img-holder" style="min-height: 80px;">
                    <div class='no-image-picker' title='Change Image' id='image-picker'>
                        <label for='file-input'>
                            <img id='frame' title='Change Image' style='width: var(--spaceXLG);' src="<?= asset('images/change_image.png')?>"/>
                            <a>No document has been selected</a>
                        </label>
                        <input id='file-input' type='file' accept='.png, .jpg, .jpeg, .gif, .pdf, .csv'  name="file" class='d-none'/>
                    </div>
                    <div class='no-image-picker d-none' title='Change Image' id='no-image-picker'></div>
                </div>        
            </form>
        </div>
    </div>
</div>

<script>
    $(document).on('change', '#file-input', function () {
        const file = this.files[0]
        if(file){
            const allowedTypes = ['image/png', 'image/jpeg', 'image/gif', 'application/pdf', 'text/csv']

            if (!allowedTypes.includes(file.type)) {
                Swal.fire({
                    title: "Error!",
                    text: "Please select a valid file (image, PDF, or CSV only)",
                    icon: "error"
                });
                this.value = ''
                return
            }

            const reader = new FileReader()
            reader.onload = function (e) {
                let filePreview = ''
                let analyze_type = ''
                if (file.type.startsWith('image/')) {
                    analyze_type = 'image'
                    filePreview = `<img src="${e.target.result}" alt="Image Preview" class="img-thumbnail mb-2" style="max-width: 200px;" /><p>${file.name}</p>`
                } else if (file.type === 'application/pdf') {
                    analyze_type = 'document'
                    filePreview = `<embed src="${e.target.result}" type="${file.type}" style="width: 100%; height: 500px;" class="mb-2"/><p>${file.name}</p>`
                } else if (file.type === 'text/csv') {
                    analyze_type = 'sheet'
                    filePreview = `<p>Uploaded CSV File: <strong>${file.name}</strong></p>`
                }

                $('#chat-section').append(`<div style="font-size:var(--textLG);" class="bubble me">Can you analyze this ${analyze_type}?<br><br>${filePreview}</div>`)
                $('#chat-section').append(`<div style="font-size:var(--textLG);" class="bubble bot">Okay, give me a minute to read the ${analyze_type} and sync it with your data</div>`)
                analyze()
            };

            if (file.type.startsWith('image/') || file.type === 'application/pdf') {
                reader.readAsDataURL(file)
            } else {
                reader.onload()
            }
        }
    });

    $(document).ready(function () {
        let is_show_analyze = false
        $(document).on('click','#nav_analyze_btn',function(){
            if(is_show_analyze == false){
                $('#col-analyze').addClass('col-lg-12 col-md-12 col-12').removeClass('col-lg-4 col-md-6')
                $('#chat-section').addClass('d-block').removeClass('d-none')
                $('#nav_analyze_btn').addClass('d-none').closest('.btn-feature').addClass('d-flex justify-content-start')
                is_show_analyze = true
            } 
        })
        $(document).on('click','#nav_analyze_close_btn', function(){
            if(is_show_analyze == true){
                $('#col-analyze').addClass('col-lg-4 col-md-6').removeClass('col-lg-12 col-md-12 col-12')
                $('#chat-section').addClass('d-none').removeClass('d-block')
                $('#nav_analyze_btn').removeClass('d-none').closest('.btn-feature').removeClass('d-flex justify-content-start')
                is_show_analyze = false
            }
        })
    })

    const analyze = () => {
        const form = $('#analyze_form')[0]
        const formData = new FormData(form)
        $.ajax({
            url: '/api/v1/analyze/report',
            type: 'POST',
            data: formData,
            processData: false, 
            contentType: false,
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
                Swal.showLoading()
            },
            success: function(response) {
                Swal.close()
                $('#upload-analyze-section').addClass('d-none')

                const data = response.data
                const inventory = data.found_inventory_data
                let inventory_element = ``
                inventory.forEach((el,idx) => {
                    inventory_element += `
                        <button class='container bordered text-start bg-dark' onclick='window.location.href="/inventory/edit/${el.id}"'>
                            <div class='d-flex justify-content-between'>
                                <h2 class='fw-bold' style='font-size:var(--textXLG);'>${el.inventory_name}</h2>
                                <div class='d-flex justify-content-between'>
                                    <span class='bg-success rounded-pill px-3 py-1 me-2'>${el.inventory_category}</span>
                                    <span class='bg-primary rounded-pill px-3 py-1'>${ucFirst(el.status)}</span>
                                </div>
                            </div>
                            <p>${el.inventory_desc ?? '<span class="text-secondary fst-italic">- No Description Provided -<span>'}</p>
                            <h6 class='mt-2'>Unit : ${el.inventory_vol} ${el.inventory_unit}</h6>
                            <h6>Placement (Room / Storage) : ${el.inventory_room} / ${el.inventory_storage ?? '-'}</h6>
                            <h6>Price : Rp. ${number_format(el.inventory_price, 0, ',', '.')}</h6>
                        </button>
                    `
                });
                $('#chat-section').append(`
                    <div style="font-size:var(--textLG);" class="bubble bot">
                        Hey, i have found <b>${data.found_total_item}</b> item in your inventory that may similar with items in this report, this report is generated <b>${data.generated_at}</b>. Here's the list inventory\n
                        ${inventory_element}
                    </div>
                `)

                let not_found_item_element = ''
                if(data.not_existing_item){
                    const items = data.not_existing_item
                    const not_existing_item = items.join(", ")
                    not_found_item_element = `<br>Item not found ${items.length > 1 ? 'are':'is'} ${not_existing_item}`
                }

                $('#chat-section').append(`
                    <div style="font-size:var(--textLG);" class="bubble bot">
                        Also, from the inventory I found. The total price for all item is <b>Rp. ${number_format(data.found_total_price, 0, ',', '.')}</b>, and the average per item is <b>Rp. ${number_format(data.found_avg_price, 0, ',', '.')}</b>.
                        From the category, we got this distribution :\n<br>
                        <div id='category_distribution'></div>${not_found_item_element}
                    </div>
                `)
                
                $( document ).ready(function() {
                    generate_pie_chart(`Category Distribution`,'category_distribution',data.found_inventory_category)
                });

                $('#chat-section').append(`
                    <div style="font-size:var(--textLG);" class="bubble bot">Is there any action do you want me to do with this document?</div>
                    <div style="font-size:var(--textLG);" class="bubble me">
                        Hmmm, I want to <span id='selected-action'>... <br></span>
                        <div class="mt-2" id='action-list'>
                            <button class="btn btn-primary py-0" onclick="make_report()">Make same Report</button>
                        </div>
                    </div>
                `)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                $('#upload-analyze-section').addClass('d-none')

                if (response.status === 422) {
                    let msg = response.responseJSON.message
                    
                    if(typeof msg != 'string'){
                        const allMsg = Object.values(msg).flat()
                        if(is_list_format){
                            msg = '<ol>'
                            allMsg.forEach((dt) => {
                                msg += `<li>- ${dt.replace('.','')}</li>`
                            })
                            msg += '</ol>'
                        } else {
                            msg = allMsg.join(', ').replace('.','')
                        }
                    }

                    $('#chat-section').append(`<div style="font-size:var(--textLG);" class="bubble bot">${msg}</div>`)
                } else {
                    $('#chat-section').append(`<div style="font-size:var(--textLG);" class="bubble bot">${response.responseJSON?.message || "Something went wrong"}</div>`)
                    if(response.status === 404){
                        const items = response.responseJSON.data.not_existing_item
                        const not_existing_item = items.join(", ")
                        $('#chat-section').append(`
                            <div style="font-size:var(--textLG);" class="bubble bot">Item not found ${items.length > 1 ? 'are':'is'} ${not_existing_item}</div>
                            <div style="font-size:var(--textLG);" class="bubble bot">Is there any action do you want me to do with this document?</div>
                            <div style="font-size:var(--textLG);" class="bubble me">
                                Hmmm, I want to <span id='selected-action'>... <br></span>
                                <div class="mt-2" id='action-list'>
                                    <button class="btn btn-primary py-0 me-2" onclick="make_report()">Make same Report</button>
                                    <button class="btn btn-primary py-0" onclick="add_inventory_via_url('${not_existing_item}')">Add Inventory</button>
                                </div>
                            </div>
                        `)
                    }
                }
            }
        });
    }

    const make_report = () => {
        $('#selected-action').empty().html('make same report')
        $('#action-list').empty()
        $('#chat-section').append(`
            <div style="font-size:var(--textLG);" class="bubble bot">Okay, wait some moment</div>
        `)
        add_report()
    }

    const add_report = () => {
        const form = $('#analyze_form')[0]
        const formData = new FormData(form)
        $.ajax({
            url: '/api/v1/analyze/report/new',
            type: 'POST',
            data: formData,
            processData: false, 
            contentType: false,
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
                Swal.showLoading()
            },
            success: function(response) {
                Swal.close()
                const id = response.data.id

                $('#chat-section').append(`
                    <div style="font-size:var(--textLG);" class="bubble bot">
                        The new report has been created, if you want to see it now you can click this button <br>
                        <div class="mt-2">
                            <a class="btn btn-primary py-0" href="/report/detail/${id}">See Detail</a>
                        </div>
                    </div>
                `)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                generate_api_error(response, true)
            }
        });
    }

    const add_inventory_via_url = (items) => {
        const list_items = items.split(", ")
        list_items.forEach(el => {
            window.open(`/inventory/add?inventory_name=${el}`, "_blank")
        });
    }
</script>