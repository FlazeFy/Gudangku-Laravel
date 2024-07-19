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
    <div class='no-image-picker' title='Change Image' id='no-image-picker'>
        <label for='file-input'>
            <img id='frame' title='Change Image' style='width: var(--spaceXLG);' src="<?= asset('images/change_image.png')?>"/>
            <a>No image has been selected</a>
        </label>
        <input id='file-input' type='file' accept='image/*' style='display: none;' onchange='setValueInventoryImage()'/>
    </div>
</div>
    <input hidden type="text" name="inventory_image" id="inventory_image_url" value="">

    <canvas id="imageCanvas" style="display: none;"></canvas>
<a class="btn btn-danger px-2 shadow" title="Reset to default image" onclick="clearImage()"><i class="fa-solid fa-trash-can"></i> Reset Image</a>

<script src="https://www.gstatic.com/firebasejs/6.0.2/firebase.js"></script>

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
        Swal.showLoading()
        document.getElementById('frame').src = "{{asset('images/default_inventory.jpg')}}";

        if(uploadedInventoryImageUrl && uploadedInventoryImageUrl != ""){
            let storageRef = firebase.storage();
            let desertRef = storageRef.refFromURL(uploadedInventoryImageUrl);

            desertRef.delete().then(() => {
                document.getElementById('inventory_image_url').value = null;
                Swal.hideLoading()
                Swal.fire({
                    title: "Success!",
                    text: "Success to remove the image",
                    icon: "success"
                });
                document.getElementById('header-progress').innerHTML = `Inventory image has been removed`;
                uploadedInventoryImageUrl = ""
            }).catch((error) => {
                Swal.hideLoading()
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
                Swal.fire({
                    title: "Success!",
                    text: "Success to upload the image",
                    icon: "success"
                });
                document.getElementById('frame').src = downloadUrl;
                document.getElementById('inventory_image_url').value = downloadUrl;
            });
        });
    }
</script>