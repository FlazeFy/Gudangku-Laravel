<div class='container bordered'>
    <div id="stats_total_report_spending_per_month"></div>
</div>
<script>
    const get_total_report_spending_per_month = () => {
        const year = 2024
        Swal.showLoading()
        $.ajax({
            url: `/api/v1/stats/report/total_spending_per_month/${year}`,
            type: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
                xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>")    
            },
            success: function(response) {
                Swal.close()
                const data = response.data
                generate_line_column_chart('Total Report Spending Per Month','stats_total_report_spending_per_month',data.map(({ total_item, ...rest }) => rest))
                generate_table_context_total('stats_total_report_spending_per_month',data)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                Swal.fire({
                    title: "Oops!",
                    text: "Failed to get the stats",
                    icon: "error"
                });
            }
        });
    }
    get_total_report_spending_per_month()
</script>