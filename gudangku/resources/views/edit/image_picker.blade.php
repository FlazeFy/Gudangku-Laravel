<style>
    .inventory-image {
        position: relative;
        margin-top: 6px;
        margin-bottom: 6px; 
    }
    .inventory-image, .no-image-picker {
        height: 260px;
    }
    .no-image-picker {
        border: 2px dashed var(--whiteColor);
        width: 100%;
        border-radius: var(--roundedMD);
        padding: var(--textLG);
        margin: var(--spaceMD) 0;
        text-align: center;
        display: flex;
        justify-content: center;
        align-items: center; 
        cursor: pointer;
        -webkit-transition: all 0.4s !important;
        -o-transition: all 0.4s !important;
        transition: all 0.4s !important;
    }
    .no-image-picker:hover {
        transform: scale(1.01);
    }
    .no-image-picker a {
        vertical-align:middle;
    }
    .inventory-image-holder .inventory-image{
        margin-inline: auto;
        display: block;
        border-radius: var(--roundedSM) !important;
        background-position: center;
        background-repeat:no-repeat;
        position: relative;
        background-size: cover;
        background-color: var(--darkColor);
        height:200px;
        box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
    }
    .image-upload{
        position: absolute;
        bottom: 3px;
        right: 10px;
    }
    .image-upload>input {
        display: none;
    }
    .btn.change-image{
        width:40px; 
        height:40px; 
        -webkit-transition: all 0.4s;
        -o-transition: all 0.4s;
        transition: all 0.4s;
        background:var(--primaryColor);
        display: block;
        margin-inline: auto;
    }
    .inventory-image-holder .btn-icon-reset-image{
        position: absolute; 
        bottom: 10px; 
        left: 10px;
        background: var(--dangerBG) !important;
        color:var(--whiteColor) !important;
        -webkit-transition: all 0.4s;
        -o-transition: all 0.4s;
        transition: all 0.4s;
    }
    .inventory-image-holder .status-holder{
        position: absolute; 
        bottom: 10px; 
        left: 60px;
    }
</style>

<div class="img-holder">
    <?php 
        if($inventory->inventory_image){
            echo "
                <div class='no-image-picker' title='Change Image' id='no-image-picker'>
                    <label for='file-input'>
                        <img id='frame' class='m-2 inventory-image' title='Change Image' src='$inventory->inventory_image' />
                    </label>
                    <input id='file-input' type='file' accept='image/*' style='display: none;' onchange='setValueInventoryImage()'/>
                </div>
            ";
        } else {
            echo "
                <div class='no-image-picker' title='Change Image' id='no-image-picker'>
                    <label for='file-input'>
                        <img id='frame' class='m-2' title='Change Image' style='width: var(--spaceXLG);' src='".asset('images/change_image.png')."' />
                        <a>No image has been selected</a>
                    </label>
                    <input id='file-input' type='file' accept='image/*' style='display: none;' onchange='setValueInventoryImage()'/>
                </div>
            ";
        }
    ?>
    </div>
    <input hidden type="text" name="inventory_image" id="inventory_image_url" value="">

    <canvas id="imageCanvas" style="display: none;"></canvas>
<script src="https://www.gstatic.com/firebasejs/6.0.2/firebase.js"></script>
<a class="btn btn-danger px-2 shadow" title="Reset to default image" onclick="clearImage()"><i class="fa-solid fa-trash-can"></i> Reset Image</a>

<script>
    const firebaseConfig = {
        apiKey: "AIzaSyBhHYBCkpHJmP2tmjjpf4W79SB4zvHbx4o",
        authDomain: "gudangku-94edc.firebaseapp.com",
        projectId: "gudangku-94edc",
        storageBucket: "gudangku-94edc.appspot.com",
        messagingSenderId: "463946849302",
        appId: "1:463946849302:web:a579a84fd5eb471551a937",
        measurementId: "G-5KX38B42YR"
    }
    firebase.initializeApp(firebaseConfig)

    let uploadedInventoryImageUrl = ""
    function clearImage() {
        document.getElementById('formFileImg').value = null;
        document.getElementById('frame').src = "{{asset('images/default_inventory.jpg')}}";
        document.getElementById('inventory_image_url').value = "{{asset('images/default_inventory.jpg')}}";

        if(uploadedInventoryImageUrl && uploadedInventoryImageUrl != ""){
            let storageRef = firebase.storage();
            let desertRef = storageRef.refFromURL(uploadedInventoryImageUrl);

            desertRef.delete().then(() => {
                document.getElementById('header-progress').innerHTML = `Inventory image has been removed`;
                uploadedInventoryImageUrl = ""
            }).catch((error) => {
                Swal.fire({
                    title: "Oops!",
                    text: "Failed to deleted the image",
                    icon: "error"
                });
                document.getElementById('header-failed').innerHTML = `Failed to deleted the image`;
            });
        }        
    }

    function setValueInventoryImage(){
        Swal.showLoading()
        let cheader_file_src = document.getElementById('file-input').files[0];
        let filePath = 'inventory/<?= session()->get('id_key') ?>_<?= session()->get('username_key') ?>/' + getUUID();

        //Set upload path
        let storageRef = firebase.storage().ref(filePath);
        let uploadTask = storageRef.put(cheader_file_src);

        //Do upload
        uploadTask.on('state_changed',function (snapshot) {
            let progress = Math.round((snapshot.bytesTransferred/snapshot.totalBytes)*100);
            document.getElementById('header-progress').innerHTML = `File upload is ${progress}% done`;
        }, 
        function (error) {
            Swal.hideLoading()
            Swal.fire({
                title: "Oops!",
                text: "Something error! File upload is error",
                icon: "error"
            });
            document.getElementById('header-failed').innerHTML = `File upload is ${error.message}`;
            let cheader_url = null;
        }, 
        function () {
            uploadTask.snapshot.ref.getDownloadURL().then(function (downloadUrl) {
            
                document.getElementById('no-image-picker').innerHTML = `<img class="inventory-image" src="${downloadUrl}">`;
                document.getElementById('inventory_image_url').value = downloadUrl;
                uploadedInventoryImageUrl = downloadUrl;

                update_image_url(uploadedInventoryImageUrl, '<?= $inventory->id ?>')
            });
        });
    }

    const update_image_url = (inventory_image, id) => {
        $.ajax({
            url: '/api/v1/inventory/edit_image/'+id,
            type: 'PUT',
            data: {
                inventory_image: inventory_image
            },
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json");
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>");    
            },
            success: function(response) {
                Swal.hideLoading()
                Swal.fire({
                    title: "Success!",
                    text: "Success to upload the image",
                    icon: "success"
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.hideLoading()
                Swal.fire({
                    title: "Oops!",
                    text: "Failed to upload the image",
                    icon: "error"
                });
            }
        });
    }

    $( document ).ready(function() {
        const image = document.getElementById('frame')
        const canvas = document.getElementById('imageCanvas')
        const ctx = canvas.getContext('2d');
        canvas.width = image.width;
        canvas.height = image.height;
        ctx.drawImage(image, 0, 0, image.width, image.height);

        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const { data } = imageData;
        const colorCount = {};
        let dominantColor = { color: null, count: 0 };

        for (let i = 0; i < data.length; i += 4) {
            const r = data[i];
            const g = data[i + 1];
            const b = data[i + 2];
            const color = `rgb(${r},${g},${b})`;

            if (colorCount[color]) {
                colorCount[color]++;
            } else {
                colorCount[color] = 1;
            }

            if (colorCount[color] > dominantColor.count) {
                dominantColor = { color, count: colorCount[color] }
            }
        }

        const rgbToHex = (r, g, b) => 
            `#${((1 << 24) + (r << 16) + (g << 8) + b)
                .toString(16)
                .slice(1)
                .toUpperCase()}`

        const rgbValues = dominantColor.color.match(/\d+/g)
        const hexColor = rgbToHex(parseInt(rgbValues[0]), parseInt(rgbValues[1]), parseInt(rgbValues[2]))

        const colorNames = {
            "black": [0, 0, 0],
            "white": [255, 255, 255],
            "red": [255, 0, 0],
            "lime": [0, 255, 0],
            "blue": [0, 0, 255],
            "yellow": [255, 255, 0],
            "cyan": [0, 255, 255],
            "magenta": [255, 0, 255],
            "silver": [192, 192, 192],
            "gray": [128, 128, 128],
            "maroon": [128, 0, 0],
            "olive": [128, 128, 0],
            "green": [0, 128, 0],
            "purple": [128, 0, 128],
            "teal": [0, 128, 128],
            "navy": [0, 0, 128]
        };

        const getClosestColorName = (r, g, b) => {
            let closestColor = null
            let closestDistance = Infinity

            for (const [name, rgb] of Object.entries(colorNames)) {
                const distance = Math.sqrt(
                    Math.pow(r - rgb[0], 2) +
                    Math.pow(g - rgb[1], 2) +
                    Math.pow(b - rgb[2], 2)
                );

                if (distance < closestDistance) {
                    closestColor = name
                    closestDistance = distance
                }
            }

            return closestColor
        };

        const colorName = getClosestColorName(parseInt(rgbValues[0]), parseInt(rgbValues[1]), parseInt(rgbValues[2]))
        $('#inventory_color').val(ucFirst(colorName))
    })
</script>