<style>
    h1 { font-size:calc(var(--textJumbo)*1.5); font-weight: bold; }
</style>

<div class="modal fade" id="aboutModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fw-bold" id="exampleModalLabel">About</h2>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body text-center">
                <h1 class="text-primary mb-3">About GudangKu</h1>
                <p class="mb-4">GudangKu revolutionizes inventory management, making it effortless to organize and track your belongings. 
                    Whether you're managing household items or business supplies, our app lets you create detailed lists with categories, images, prices, and more, 
                    so everything you need is just a tap away. With interactive visual layouts in 2D or 3D, you can map out storage spaces and instantly locate items, 
                    making organization intuitive and efficient.
                </p>
                <p>
                    Beyond basic inventory tracking, GudangKu offers powerful features like custom reporting, reminders, and advanced analytics. 
                    Set reminders for cleaning or restocking, generate tailored reports, and gain insights into your inventory through charts and data-driven analysis. 
                    With the ability to save and share data in CSV or PDF formats, GudangKu streamlines decision-making and enhances collaboration, making it the ultimate 
                    tool for smarter, hassle-free inventory management.
                </p><br>
                <h1 class="text-primary mb-3">About Creator</h1>
                <div class="bordered p-3 mx-auto" style="border-radius:var(--roundedLG); max-width:360px;">
                    <img class="img img-fluid rounded-circle mx-auto" style="width:240px;"  src="<?= asset('images/male.png')?>"/>
                    My Name is <b>Leo</b>, a Bachelor's degree graduate in Software Engineering from Telkom University
                    (2023). Focused on Web Development and Mobile Development. Enjoys exploring new knowledge and seeking challenges.
                    If you want to do collaboration or do you want to send me feedback? you can find me on :
                    <br>
                    <div style="font-size:calc(var(--textLG)*1.5);" class="mt-3">
                        <a href="https://www.instagram.com/leonardhorante_08/" class="me-3"><i class="fa-brands fa-instagram"></i></a>
                        <a href="https://www.linkedin.com/in/leonardho-rante-sitanggang-a5a752202/" class="me-3"><i class="fa-brands fa-linkedin"></i></a>
                        <a href="https://github.com/FlazeFy" class="me-3"><i class="fa-brands fa-github"></i></a>
                        <a href="mailto:flazen.edu@gmail.com"><i class="fa-solid fa-envelope"></i></a>
                    </div>
                </div><br>
                <h1 class="text-primary mb-3">About Stack</h1>
                <p>
                    For development of GudangKu apps, we use <b>Laravel</b> for the Web Apps, <b>Flutter</b> for the Mobile Apps, and <b>Python</b> for the Telegram Bot.
                </p>
            </div>
        </div>
    </div>
</div>