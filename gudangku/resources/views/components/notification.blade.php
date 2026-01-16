@php($role = session()->get('role_key'))
@if($role == 0)
    <a class="btn me-2" data-bs-toggle="popover" id='open-notification-btn' title="Notification" data-bs-placement="left" data-bs-html="true" 
        data-bs-content="
            <div id='reminder-holder'></div>
        "  
        style="background:var(--successBG) !important; float:right;">
        <i class="fa-solid fa-bell mx-1"></i>
    </a>

    <script>
        let page_reminder = 1

        $(document).on('click','#open-notification-btn', function(){
            $(document).ready(function() {
                const get_reminder_history = (page) => {
                    const item_holder = 'reminder-holder'

                    $.ajax({
                        url: `/api/v1/reminder/history?page=${page}`,
                        type: 'GET',
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader("Accept", "application/json")
                            xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
                        },
                        success: function(response) {
                            const data = response.data.data
                            const current_page = response.data.current_page
                            const total_page = response.data.last_page
                            const total_item = response.data.total
                            
                            $(`#${item_holder}`).empty()
                            data.forEach(el => {
                                $(`#${item_holder}`).append(`
                                    <button class='btn text-start container bordered mt-0' style='font-size:var(--textMD) !important;' title='See Inventory' onclick="window.location.href='/inventory/edit/${el.id}'">
                                        <div class='d-flex justify-content-start text-white'>
                                            <div class='container bordered mt-0 text-center me-2 p-3' style='width:50px; height:50px; border-radius: var(--roundedLG);'><i class="fa-solid fa-bell mx-0" style='font-size: var(--textJumbo);'></i></div>
                                            <h6>${el.reminder_desc}</h6> 
                                        </div>
                                        <p class='date-text mt-2' style='font-size:var(--textMD) !important;'>Received At : ${getDateToContext(el.last_execute,'calendar')}</p>
                                    </button>
                                `)
                            });
                        },
                        error: function(response, jqXHR, textStatus, errorThrown) {
                            if(response.status != 404){
                                generateAPIError(response, true)
                            } else {
                                templateAlertContainer(item_holder, 'no-data', "No notification to show", null, '<i class="fa-solid fa-rotate-left"></i>')
                            }
                        }
                    });
                }
                get_reminder_history(page_reminder) 
            });
        })
    </script>
@endif