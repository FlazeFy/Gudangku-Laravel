<style>
    .step {
        position: relative;
        min-height: 1em;
        color: gray;
    }
    .step-mobile {
        position: fixed;
        top: var(--spaceMD);
        background: var(--darkColor);
        border: 1.5px solid var(--whiteColor);
        padding: var(--spaceLG);
        z-index: 999;
        width: 92.5%;
        color: var(--primaryColor);
        border-radius: var(--roundedMD);
        display: block;
    }
    .step.step-finish .title {
        color: var(--successBG);
    }
    .step.step-finish .circle {
        background: var(--successBG);
    }

    .title {
        line-height: 1.5em;
    }
    .caption {
        font-size: 0.8em;
    }
    .step + .step {
        margin-top: 1.5em
    }
    .step > div:first-child {
        position: static;
        height: 0;
    }
    .step > div:not(:first-child) {
        margin-left: 1.5em;
        padding-left: 1em;
    }
    .circle {
        background: gray;
        position: relative;
        width: 1.5em;
        height: 1.5em;
        line-height: 1.5em;
        border-radius: 100%;
        box-shadow: 0 0 0 3px var(--whiteColor);
    }
    .circle.finish{
        background: var(--successBG);
    }
    .circle:after {
        content: ' ';
        position: absolute;
        display: block;
        top: 1px;
        right: 50%;
        bottom: 1px;
        left: 50%;
        height: 100%;
        width: 1px;
        transform: scale(1, 2);
        transform-origin: 50% -100%;
        background-color: rgba(0, 0, 0, 0.25);
        z-index: -1;
    }
    .step:last-child .circle:after {
        display: none
    }
    .step.step-active {
        color: #4285f4
    }
    .step.step-active .circle {
        background-color: #4285f4;
    }
    .section-form {
        border-top: 2px solid var(--shadowColor);
        padding-top: calc(var(--spaceJumbo)*1.25);
    }
    .pin-code{ 
        padding: 0; 
        margin: 0 auto; 
        display: flex;
        justify-content:center;
    } 
    .pin-code input { 
        border: 1.75px solid var(--whiteColor); 
        width: 48px;
        height:48px;
        font-size: 36px; 
        background-color: #F3F3F3;
        margin-right:5px;
    } 
    .pin-code input:focus { 
        border: 1px solid #573D8B;
        outline:none;
    } 
    input::-webkit-outer-spin-button, input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
</style>
<div class="row w-100 h-100">
    <div class="col-lg-5 col-md-5 col-sm-2 col-12">
        <div style="top:5vh; position:sticky;">
            <div class="step-mobile mb-4 d-md-none">
                <div class="progress mb-2">
                    <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="title">Hello There!</div>
                <div class="caption">Do you aggree with our terms & condition?</div>
            </div>
            <div class="d-flex d-inline-block">
                <a class='btn btn-danger me-2' href='/login'><i class="fa-solid fa-arrow-left"></i> Back to Login</a>
                <div id='start-browsing-holder-btn'></div>
            </div>
            <hr>
            <h1>Register</h1>
            <div class="d-none d-md-block">
                <div class="step step-active" id="indicator-tnc">
                    <div>
                        <div class="circle"><i class="fa fa-check"></i></div>
                    </div>
                    <div>
                        <div class="title">Hello There!</div>
                        <div class="caption">Do you aggree with our terms & condition?</div>
                    </div>
                </div>
                <div class="step" id="indicator-profile">
                    <div>
                        <div class="circle">2</div>
                    </div>
                    <div>
                        <div class="title">Let's Us to Know You</div>
                        <div class="caption">Fill this form to make your account</div>
                    </div>
                </div>
                <div class="step" id="indicator-service">
                    <div>
                        <div class="circle">3</div>
                    </div>
                    <div>
                        <div class="title">Stay Updated!</div>
                        <div class="caption">Sync your account to another Platform. Like Telegram and Line</div>
                    </div>
                </div>
                <div class="step">
                    <div>
                        <div class="circle">4</div>
                    </div>
                    <div>
                        <div class="title">Finish</div>
                        <div class="caption py-1 d-none">
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <a class="btn btn-success px-2 py-1" style="font-size:var(--textSM);" href='/'>
                                    <i class="fa-solid fa-arrow-right"></i> Go to Dashboard
                                </a>
                                <a class="btn btn-success px-2 py-1" style="font-size:var(--textSM);" href='/login'>
                                    <i class="fa-solid fa-house"></i> Back to Login
                                </a>
                                <a class="btn btn-primary px-2 py-1" style="font-size:var(--textSM);">
                                    <i class="fa-solid fa-mobile-screen"></i> Get Mobile Version
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-7 col-md-7 col-sm-10 col-12">
        <div id="tnc_section" class="section-form border-0 pt-0">
            <h2>Terms & Condition</h2>
            <div class="py-3 mb-3">
                @include('register.section.tnc')
            </div>
        </div>

        <div id="profile_section" class="section-form d-none">
            <h2>Profile</h2>
            <div class="py-3 mb-3">
                @include('register.section.profile')
            </div>
        </div>

        <div id="service_section" class="section-form d-none">
            <h2>Other Service</h2>
            <div class="py-3 mb-3">
                @include('register.section.service')
            </div>
        </div>

        <div id="add_inventory_section" class="section-form d-none">
            <h2>Add Inventory (Optional)</h2>
        </div>

        <div id="welcome_section" class="section-form d-none">
            <h2>Welcome</h2>
        </div>
    </div>
</div>