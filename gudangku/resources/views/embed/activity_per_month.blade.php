@extends('components.layout')

@section('content')
<div class='container bordered' style="min-width:720px;">
    <div id="stats_total_activity_per_month"></div>
</div>
<script>
    const get_total_activity_per_month = (year) => {
        Swal.showLoading()
        const title = `Total Activity Per Month (${year})`
        const ctx = `total_activity_per_month_temp-${year}`
        const ctx_holder = "stats_total_activity_per_month"

        const failedMsg = () => {
            Swal.fire({
                title: "Oops!",
                text: `Failed to get the stats Total ${title}`,
                icon: "error"
            });
        }
        const fetchData = () => {
            $.ajax({
                url: `/api/v1/stats/history/total_activity_per_month/${year}`,
                type: 'GET',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader("Accept", "application/json")
                },
                success: function(response) {
                    Swal.close()
                    const data = response.data
                    localStorage.setItem(ctx,JSON.stringify(data))
                    localStorage.setItem(`last-hit-${ctx}`,Date.now())
                    generate_line_column_chart(title,ctx_holder,data)
                    generate_table_context_total(ctx_holder,data)
                },
                error: function(response, jqXHR, textStatus, errorThrown) {
                    Swal.close()
                    if(response.status != 404){
                        failedMsg()
                    } else {
                        template_alert_container(ctx_holder, 'no-data', "No report found for this context to generate the stats", 'add a report', '<i class="fa-solid fa-warehouse"></i>','/report/add')
                        $(`#${ctx_holder}`).prepend(`<h2 class='title-chart'>${ucEachWord(title)}</h2>`)
                    }
                }
            });
        }

        if(ctx in localStorage){
            const lastHit = parseInt(localStorage.getItem(`last-hit-${ctx}`))
            const now = Date.now()

            if(((now - lastHit) / 1000) < statsFetchRestTime){
                const data = JSON.parse(localStorage.getItem(ctx))
                if(data){
                    generate_line_column_chart(title,ctx_holder,data)
                    generate_table_context_total(ctx_holder,data)
                    Swal.close()
                } else {
                    Swal.close()
                    failedMsg()
                }
            } else {
                fetchData()
            }
        } else {
            fetchData()
        }
    }
    const year = "<?= $year ?>"
    get_total_activity_per_month(year)
</script>
@endsection