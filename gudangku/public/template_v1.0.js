const template_alert_container = (target, type, msg, btn_title, icon) => {
    $(`#${target}`).html(`
        <div class="container p-3" style="${type == 'no-data'? 'background-color:rgba(59, 131, 246, 0.2);':''}">
            <div class="d-flex justify-content-start">
                <div class="me-3">
                    <h1 style="font-size: 70px;">${icon}</h1>
                </div>
                <div>
                    <h4>${msg}</h4>
                    <a class="btn btn-primary mt-3"><i class="${type == 'no-data'? 'fa-solid fa-plus':''}"></i> ${ucEachWord(btn_title)}</a>
                </div>
            </div>
        </div>
    `)
}