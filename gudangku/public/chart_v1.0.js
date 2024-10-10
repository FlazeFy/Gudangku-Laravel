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

const generate_table_context_total = (holder, data) => {
    if(data.length > 0){
        tbody = ''
        data.forEach(el=> {
            tbody += `
                <tr>
                    <td>${el.context}</td>
                    <td>${el.total}</td>
                </tr>
            `
        })
        $(`#${holder}`).append(`
            <table class='table table-bordered text-center mt-4'>
                <thead style='background:var(--primaryColor);'>
                    <tr>
                        <th>Context</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${tbody}
                </tbody>
            </table>
        `)
        export_button_data(holder,data)
    } 
}

const export_button_data = (holder, data) => {
    if(data.length > 0){
        $(`#${holder}`).append(`
            <button id='download-btn-data-${holder}' class='btn btn-primary'>
                <i class="fa-solid fa-download"></i> Download CSV
            </button>
        `);

        $(`#download-btn-data-${holder}`).on('click', function() {
            const csvData = convert_to_csv(data)
            download_csv(csvData, `data_export_${holder}.csv`)
            Swal.fire({
                title: "Downloaded!",
                text: `You have downloaded ${ucEachWord(holder.replaceAll('_',' '))} data`,
                icon: "success"
            });
        });
    } 
}

const convert_to_csv = (data) => {
    const headers = Object.keys(data[0])
    const csvRows = []
    csvRows.push(headers.join(','))

    data.forEach(row => {
        const values = headers.map(header => {
            const escapeValue = String(row[header]).replace(/"/g, '""')
            return `"${escapeValue}"`
        });
        csvRows.push(values.join(','))
    });

    return csvRows.join('\n')
}

const download_csv = (csvData, filename) => {
    const blob = new Blob([csvData], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.setAttribute('href', url)
    a.setAttribute('download', filename)
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
}
