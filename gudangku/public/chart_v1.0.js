const generate_pie_chart = (title, holder, data) => {
    $(`#${holder}`).before(`<h2 class='title-chart'>${ucEachWord(title)}</h2>`)

    if(data.length > 0){
        let keys = Object.keys(data[0])
        if(keys.length == 2 && (typeof data[0][keys[0]] === 'string' && Number.isInteger(data[0][keys[1]]) || typeof data[0][keys[1]] === 'string' && Number.isInteger(data[0][keys[0]]))){
            const totals = data.map(c => c[Number.isInteger(data[0][keys[1]]) ? keys[1] : keys[0]])
            const contexts = data.map(c => c[typeof data[0][keys[0]] === 'string' ? keys[0] : keys[1]])

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
            $(`#${holder}`).html(`
                <h6 class="text-center">Data is Not Valid</h6>
            `)
        }
    } else {
        $(`#${holder}`).html(`
            <img src="{{asset('images/nodata.png')}}" class="img nodata-icon">
            <h6 class="text-center">No Data</h6>
        `)
    }
}

const generate_bar_chart = (title, holder, data) => {
    $(`#${holder}`).before(`<h2 class='title-chart'>${ucEachWord(title)}</h2>`)

    if(data.length > 0){
        let keys = Object.keys(data[0])
        if(keys.length == 2 && (typeof data[0][keys[0]] === 'string' && Number.isInteger(data[0][keys[1]]) || typeof data[0][keys[1]] === 'string' && Number.isInteger(data[0][keys[0]]))){
            const totals = data.map(c => c[Number.isInteger(data[0][keys[1]]) ? keys[1] : keys[0]])
            const contexts = data.map(c => c[typeof data[0][keys[0]] === 'string' ? keys[0] : keys[1]])

            var options = {
                series: totals,
                chart: {
                    type: 'bar',
                },
                labels: contexts,
                colors: ['#F9DB00', '#009FF9', '#F78A00', '#42C9E7'],
                legend: {
                    position: 'bottom'
                },
                plotOptions: {
                    bar: {
                        horizontal: false
                    }
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
            $(`#${holder}`).html(`
                <h6 class="text-center">Data is Not Valid</h6>
            `)
        }
    } else {
        $(`#${holder}`).html(`
            <img src="{{asset('images/nodata.png')}}" class="img nodata-icon">
            <h6 class="text-center">No Data</h6>
        `)
    }
}

const generate_line_column_chart = (title, holder, data) => {
    $(`#${holder}`).before(`<h2 class='title-chart'>${ucEachWord(title)}</h2>`)

    if(data.length > 0){
        let keys = Object.keys(data[0])

        if(keys.length == 3){
            const total_1 = data.map(c => Number.isInteger(data[0][keys[1]]) ? c[keys[1]] : Number.isInteger(data[0][keys[2]]) ? c[keys[2]] : c[keys[0]])
            const total_2 = data.map(c => Number.isInteger(data[0][keys[2]]) ? c[keys[2]] : Number.isInteger(data[0][keys[0]]) ? c[keys[0]] : c[keys[1]])
            const title_1 = ucEachWord((Number.isInteger(data[0][keys[1]]) ? keys[1] : Number.isInteger(data[0][keys[2]]) ? keys[2] : keys[0]).replaceAll('_',' '))
            const title_2 = ucEachWord((Number.isInteger(data[0][keys[2]]) ? keys[2] : Number.isInteger(data[0][keys[0]]) ? keys[0] : keys[1]).replaceAll('_',' '))
            const sum_total_1 = total_1.reduce((acc, val) => acc + val, 0)
            const sum_total_2 = total_2.reduce((acc, val) => acc + val, 0)
            const context = data.map(c => c[typeof data[0][keys[0]] === 'string' ? keys[0] : typeof data[0][keys[1]] === 'string' ? keys[1] : keys[2]])

            var options = {
                series: [
                    {
                        name: sum_total_1 > sum_total_2 ? title_2 : title_1,
                        type: 'column',
                        data: sum_total_1 > sum_total_2 ? total_2 : total_1
                    }, 
                    {
                        name: sum_total_1 < sum_total_2 ? title_2 : title_1,
                        type: 'line',
                        data: sum_total_1 < sum_total_2 ? total_2 : total_1
                    }
                ],
                chart: {
                    height: 350,
                    type: 'line',
                    toolbar: {
                        show: false
                    },
                    zoom: false
                },
                stroke: {
                    width: [0, 4],
                    curve: 'smooth'
                },
                dataLabels: {
                    enabled: true,
                    enabledOnSeries: [1]
                },
                labels: context,
                yaxis: {
                    labels: {
                        formatter: function (value) {
                            return value >= 10000 ? (value / 1000).toFixed(1) + 'K' : value;
                        }
                    }
                }
            };

            let chart = new ApexCharts(document.querySelector(`#${holder}`), options)
            chart.render()
        } else {
            $(`#${holder}`).html(`
                <h6 class="text-center">Data is Not Valid</h6>
            `)
        }
    } else {
        $(`#${holder}`).html(`
            <img src="{{asset('images/nodata.png')}}" class="img nodata-icon">
            <h6 class="text-center">No Data</h6>
        `)
    }
}

const generate_table_context_total = (holder, data, key_currency) => {
    if(data.length > 0){
        let keys = Object.keys(data[0])
        let thead = `<thead style='background:var(--primaryColor);'><tr>`
        keys.forEach(dt => {
            thead += `<th>${ucEachWord(dt.replaceAll('_',' '))}</th>`
        });
        thead += `</tr></thead>`

        let tbody = ''
        data.forEach(el => {
            tbody += '<tr>'
            keys.forEach(key => {
                tbody += `<td>${key.includes('price') || (key.includes(key_currency) && key_currency) ? `Rp. ${number_format(el[key], 0, ',', '.')}` : el[key]}</td>`
            });
            tbody += '</tr>'
        });

        $(`#${holder}`).append(`
            <table class='table table-bordered text-center mt-4'>
                ${thead}
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
