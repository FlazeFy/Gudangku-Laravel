<h3>6. The History</h3>
<div class="row">
    <div class="col-lg-3 col-md-4 col-sm-12">
        <form action="/stats/toogleYear" method="POST" id="toogle_year_select">
            @csrf
            <label>Select Year</label>
            <select class="form-select" id="toogle_year" name="toogle_year"></select>
        </form>
    </div>
    <div class="col-lg-9 col-md-8 col-sm-12">
        <p><span class='inventory_name text-primary'></span> have exist in your inventory since <span id="created_at"></span> about <b id='days_exist'></b> days ago.
        <span id="updated_at"></span><span id="whole_year_total_in_report"></span><span id='report_history'></span></p>
    </div>
</div>
<div id="last_report_history_table"></div>
<div class='mt-3'id="monthly_report_history_table"></div>
<br>

<script>
    $(document).on('change','#toogle_year',function(){
        const keys = ['total_inventory_created_per_month_temp','total_report_created_per_month_temp','total_report_spending_per_month_temp','total_report_used_per_month_temp']
        keys.forEach(dt => {
            localStorage.removeItem(dt)
            localStorage.removeItem(`last-hit-${dt}`) 
        });
        $('#toogle_year_select').submit()
    })
    
    const get_available_year = () => {
        Swal.showLoading()
        $.ajax({
            url: `/api/v1/user/my_year`,
            type: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
            },
            success: function(response) {
                Swal.close()
                const data = response.data
                const selected_year = <?= session()->get('toogle_select_year') ?>;

                data.forEach(el => {
                    $('#toogle_year').append(`<option value="${el.year}" ${selected_year == el.year ? 'selected' :''}>${el.year}</option>`) 
                });
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                Swal.fire({
                    title: "Oops!",
                    text: "Failed to get available year",
                    icon: "error"
                });
            }
        });
    }
    get_available_year()
</script>