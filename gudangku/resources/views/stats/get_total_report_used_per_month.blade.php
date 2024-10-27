<div class='container bordered'>
    <div id="stats_total_report_used_per_month"></div>
</div>
<script>
    const get_total_report_used_per_month = (year) => {
        Swal.showLoading()
        const title = 'Total Report Used Per Month'
        const ctx_holder = "stats_total_report_used_per_month"

        $.ajax({
            url: `/api/v1/stats/report/total_used_per_month/${year}`,
            type: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
            },
            success: function(response) {
                Swal.close()
                const data = response.data
                generate_line_column_chart(title,ctx_holder,data)
                generate_table_context_total(ctx_holder,data)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                if(response.status != 404){
                    Swal.fire({
                        title: "Oops!",
                        text: `Failed to get the stats Total Report Used Per Month in ${year}`,
                        icon: "error"
                    });
                } else {
                    template_alert_container(ctx_holder, 'no-data', "No inventory found for this context to generate the stats", 'add a inventory', '<i class="fa-solid fa-warehouse"></i>')
                    $(`#${ctx_holder}`).prepend(`<h2 class='title-chart'>${ucEachWord(title)}</h2>`)
                }
            }
        });
    }
    get_total_report_used_per_month(year)
</script>