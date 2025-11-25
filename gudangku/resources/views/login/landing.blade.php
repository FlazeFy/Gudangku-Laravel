<style>
    .carousel-indicators button {
        width: var(--spaceMini) !important;
        height: var(--spaceMini) !important;
    }
</style>

<div class='d-inline-block mx-auto my-4 w-100 text-center'>
    <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="3" aria-label="Slide 4"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="4" aria-label="Slide 5"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active py-4">
                <br>
                <img src="{{asset('images/logo.png')}}" class='d-block mx-auto' style='width:160px;'>
                <h2 class="fw-bold" style='font-size:var(--spaceXLG);'>Welcome to Gudangku</h2><hr>
                <p class='mt-3 text-secondary'>Manage your home inventory</p>
                <br><br>
            </div>
            <div class="carousel-item py-4">
                <br>
                <img src="{{asset('images/inventory.png')}}" class='d-block mx-auto' style='max-width:160px;'>
                <h2 class="fw-bold" style='font-size:var(--spaceXLG);'>Warehouse in Your Pocket</h2><hr>
                <p class='mt-3 text-secondary'>Effortlessly manage all your inventory in one app. Organize items, monitor stock levels, and stay in control with ease!</p>
                <br><br>
            </div>
            <div class="carousel-item py-4">
                <br>
                <img src="{{asset('images/reminder.png')}}" class='d-block mx-auto' style='max-width:160px;'>
                <h2 class="fw-bold" style='font-size:var(--spaceXLG);'>Smart Reminders</h2><hr>
                <p class='mt-3 text-secondary'>Stay on top of daily tasks! Get timely reminders for cleaning, stock checks, and restocking—never miss a beat.</p>
                <br><br>
            </div>
            <div class="carousel-item py-4">
                <br>
                <img src="{{asset('images/document.png')}}" class='d-block mx-auto' style='max-width:160px;'>
                <h2 class="fw-bold" style='font-size:var(--spaceXLG);'>Digital Document Hub</h2><hr>
                <p class='mt-3 text-secondary'>A powerful digital archive for all your reports, layouts, and inventory details. Let our BOT analyze documents for you in seconds!</p>
                <br><br>
            </div>
            <div class="carousel-item py-4">
                <br>
                <img src="{{asset('images/layout.png')}}" class='d-block mx-auto' style='max-width:160px;'>
                <h2 class="fw-bold" style='font-size:var(--spaceXLG);'>Map Your Space</h2><hr>
                <p class='mt-3 text-secondary'>Forget where you stored that item? No problem. Our intuitive mapping feature helps you locate items instantly with coordinates and storage details.</p>
                <br><br>
            </div>
        </div>
    </div>
</div>
