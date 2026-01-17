<div class="d-flex justify-content-between align-items-center">
    <h2>Reminder</h2>
    <span id="add_reminder-holder"></span>
</div>
<div id='reminder_holder' class="accordion"></div>

<script>
    $(document).ready(function() {
        $(document).on('click','.reminder-box',async function(){
            const reminder_type = $(this).data('reminder-type')
            const reminder_context = $(this).data('reminder-context')
            const index = $('.reminder-box').index(this)
            const $contextHolder = $('.reminder_context_holder').eq(index)

            await getDictionaryByContext('reminder_type',token,reminder_type,'.')  
            getReminderContextSelect(reminder_type,$contextHolder,reminder_context)
        })

        $(document).on('change','.reminder_type_holder',function(){
            const selected = $(this).val()
            const index = $('.reminder_type_holder').index(this)
            const $contextHolder = $('.reminder_context_holder').eq(index)

            if(selected !== "-"){
                getReminderContextSelect(selected,$contextHolder)
            } else {
                generateEmptyFieldError('reminder type')
            }
        })

        $(document).on('change','.reminder_context_holder',function(){
            const selected = $(this).val()

            if($(this).val() === "-"){
                generateEmptyFieldError('reminder context')
            }
        })

        $(document).on('click','.save_reminder-button',function(){
            const id = $(this).data('id')
            const inventory_id = $(this).data('inventory-id')
            const index = $('.save_reminder-button').index(this)

            updateReminderByID(id,inventory_id,{
                reminder_type: $('.reminder_type_holder').eq(index).val(),
                reminder_context: $('.reminder_context_holder').eq(index).val(),
                reminder_desc: $('.reminder_desc_holder').eq(index).val()
            })
        })
    })

    const getReminderLayout = (reminder, inventory_id) => {
        if(reminder){
            $('#reminder_holder').empty().addClass('pt-2')
            reminder.forEach(dt => {
                $('#reminder_holder').append(generateReminderBox(dt, inventory_id))
            })
            $('#add_reminder-holder').html(`
                <a class='btn btn-success' data-bs-toggle="modal" data-bs-target="#modalAddReminder"><i class="fa-solid fa-plus"></i> Add Reminder</a>
            `)
        } else {
            $('#reminder_holder').html(`
                <div class="container-fluid p-3" style="background-color:rgba(59, 131, 246, 0.2)">
                    <h6><i class="fa-regular fa-clock"></i> This inventory doesn't have reminder</h6>
                    <a class="btn btn-primary mt-3" data-bs-toggle='modal' data-bs-target='#modalAddReminder'><i class="fa-solid fa-plus"></i> Add New Reminder</a>
                </div>
            `)
        }
    }
</script>