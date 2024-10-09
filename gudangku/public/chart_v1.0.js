const generate_pie_chart = (title, holder, data) => {
    $(`#${holder}`).before(`<h2 class='title-chart'>${ucEachWord(title)}</h2>`)

    if(data.length > 0){
        const totals = data.map(c => c.total)
        const contexts = data.map(c => c.context)

        var options = {
            series: totals,
            chart: {
                width: '360',
                type: 'pie',
            },
            labels: contexts,
            colors: ['#F9DB00', '#009FF9', '#F78A00', '#42C9E7'],
            legend: {
                position: 'bottom'
            },
            responsive: [{
                options: {
                    chart: {
                        width: 160
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };

        let chart = new ApexCharts(document.querySelector(`#${holder}`), options)
        chart.render()
    } else {
        $(`#${holder}`).append(`
            <img src="{{asset('images/nodata.png')}}" class="img nodata-icon">
            <h6 class="text-center">No Data</h6>
        `)
    }
}
