<h2>6. The History</h2>
<div class="row">
    <div class="col-lg-3 col-md-4 col-sm-12">
        <form action="/stats/toogleYear" method="POST" id="toogle_year_select">
            @csrf
            <label>Select Year</label>
            <select class="form-select" id="toogle_year" name="toogle_year"></select>
        </form>
    </div>
    <div class="col-lg-9 col-md-8 col-sm-12">
        <p><b class='inventory_name'></b> have exist in your inventory since <span id="created_at"></span> about <b id='days_exist'></b> days ago.
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
    getAvailableYear(token,'toogle_year',<?= session()->get('toogle_select_year') ?>)
</script>