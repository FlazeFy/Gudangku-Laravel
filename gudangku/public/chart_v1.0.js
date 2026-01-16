const generatePieChart = (title, holder, data) => {
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
            $(`#${holder}`).html(`<h6 class="text-center">Data is Not Valid</h6>`)
        }
    } else {
        $(`#${holder}`).html(`
            <img src="{{asset('images/nodata.png')}}" class="img nodata-icon">
            <h6 class="text-center">No Data</h6>
        `)
    }
}

const generateBarChart = (title, holder, data) => {
    $(`#${holder}`).before(`<h2 class='title-chart'>${ucEachWord(title)}</h2>`)

    if(data.length > 0){
        let keys = Object.keys(data[0])
        if(keys.length == 2 && (typeof data[0][keys[0]] === 'string' && Number.isInteger(data[0][keys[1]]) || typeof data[0][keys[1]] === 'string' && Number.isInteger(data[0][keys[0]]))){
            const totals = data.map(c => c[Number.isInteger(data[0][keys[1]]) ? keys[1] : keys[0]])
            const contexts = data.map(c => c[typeof data[0][keys[0]] === 'string' ? keys[0] : keys[1]])

            var options = {
                series: [{
                    name: title,
                    data: totals
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: {
                        show: true,        
                        tools: {
                            download: false 
                        }
                    }
                },
                colors: ['#F9DB00', '#009FF9', '#F78A00', '#42C9E7'],
                legend: {
                    position: 'bottom'
                },
                plotOptions: {
                    bar: {
                        horizontal: false
                    }
                },
                xaxis: {
                    categories: contexts,
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

            $(`#${holder}`).wrap(`<div style='overflow-x:auto;' class="p-2"><div style='min-width:560px;'></div></div>`)
            let chart = new ApexCharts(document.querySelector(`#${holder}`), options)
            chart.render()
        } else {
            $(`#${holder}`).html(`<h6 class="text-center">Data is Not Valid</h6>`)
        }
    } else {
        $(`#${holder}`).html(`
            <img src="{{asset('images/nodata.png')}}" class="img nodata-icon">
            <h6 class="text-center">No Data</h6>
        `)
    }
}

const generateLineColumnChart = (title, holder, data) => {
    $(`#${holder}`).before(`<h2 class='title-chart'>${ucEachWord(title)}</h2>`)

    if(data.length > 0){
        let keys = Object.keys(data[0])

        if(keys.length == 3 || keys.length == 2){
            const total_1 = data.map(c => Number.isInteger(data[0][keys[1]]) ? c[keys[1]] : Number.isInteger(data[0][keys[2]]) ? c[keys[2]] : c[keys[0]])
            const total_2 = keys.length == 3 ? data.map(c => Number.isInteger(data[0][keys[2]]) ? c[keys[2]] : Number.isInteger(data[0][keys[0]]) ? c[keys[0]] : c[keys[1]]) : null
            const title_1 = ucEachWord((Number.isInteger(data[0][keys[1]]) ? keys[1] : Number.isInteger(data[0][keys[2]]) ? keys[2] : keys[0]).replaceAll('_',' '))
            const title_2 = keys.length == 3 ? ucEachWord((Number.isInteger(data[0][keys[2]]) ? keys[2] : Number.isInteger(data[0][keys[0]]) ? keys[0] : keys[1]).replaceAll('_',' ')) : null
            const sum_total_1 = total_1.reduce((acc, val) => acc + val, 0)
            const sum_total_2 = keys.length == 3 ? total_2.reduce((acc, val) => acc + val, 0) : null
            const context = data.map(c => c[typeof data[0][keys[0]] === 'string' ? keys[0] : typeof data[0][keys[1]] === 'string' ? keys[1] : keys[2]])

            var options = {
                series: [
                    {
                        name: sum_total_1 > sum_total_2 && total_2 ? title_2 : title_1,
                        type: 'column',
                        data: sum_total_1 > sum_total_2 && total_2 ? total_2 : total_1
                    }, 
                    ...(total_2 !== null ? [{
                        name: sum_total_1 < sum_total_2 ? title_2 : title_1,
                        type: 'line',
                        data: sum_total_1 < sum_total_2 ? total_2 : total_1
                    }] : [])
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

            $(`#${holder}`).wrap(`<div style='overflow-x:auto;' class="p-2"><div style='min-width:560px;'></div></div>`)
            let chart = new ApexCharts(document.querySelector(`#${holder}`), options)
            chart.render()
        } else {
            $(`#${holder}`).html(`<h6 class="text-center">Data is Not Valid</h6>`)
        }
    } else {
        $(`#${holder}`).html(`
            <img src="{{asset('images/nodata.png')}}" class="img nodata-icon">
            <h6 class="text-center">No Data</h6>
        `)
    }
}

const generateGaugeChart = (title, holder, data, type_color = 'single_color') => {
    $(`#${holder}`).before(`<h2 class='title-chart'>${ucEachWord(title)}</h2>`)

    if (data.length > 0) {
        let keys = Object.keys(data[0])
        if (keys.length === 2 && ((typeof data[0][keys[0]] === 'string' && Number.isInteger(data[0][keys[1]])) || (typeof data[0][keys[1]] === 'string' && Number.isInteger(data[0][keys[0]])))) {
            const totals = data.map(item => item[Object.keys(item).find(k => Number.isInteger(item[k]))])
            const contexts = data.map(item => item[Object.keys(item).find(k => typeof item[k] === 'string')])

            const sum = totals.reduce((a, b) => a + b, 0)
            const percentages = totals.map(val => Math.round((val / sum) * 100))

            let selectedIndex = 0
            let percentage = percentages[selectedIndex];
            let fillColor = "var(--successBG)"

            if(type_color != 'single_color'){
                if (percentage < 30) {
                    fillColor = type_color == 'low_best' ? "var(--successBG)" : "var(--dangerBG)"
                } else if (percentage < 70) {
                    fillColor = "var(--warningBG)"
                } else {
                    fillColor = type_color == 'low_best' ? "var(--dangerBG)" : "var(--successBG)"
                }
            } else {
                fillColor = "var(--primaryColor)"
            }

            let options = {
                series: [percentages[selectedIndex]],
                chart: {
                    height: 350,
                    type: "radialBar",
                    sparkline: {
                        enabled: true
                    }
                },
                plotOptions: {
                    radialBar: {
                        startAngle: -90,
                        endAngle: 90,
                        hollow: {
                            margin: 0,
                            size: "70%",
                        },
                        track: {
                            background: "var(--greyColor)",
                            strokeWidth: "100%",
                        },
                        dataLabels: {
                            name: {
                                show: true,
                                offsetY: 20,
                                fontSize: "var(--textXLG)",
                                color: "#333",
                                formatter: () => contexts[selectedIndex]
                            },
                            value: {
                                show: true,
                                offsetY: -30, 
                                fontSize: "calc(1.5*var(--textXJumbo))",
                                fontWeight: 600,
                                formatter: (val) => `${val}%`
                            }
                        }
                    }
                },
                fill: {
                    colors: [fillColor],
                },
                labels: [contexts[selectedIndex]]
            }

            let chart = new ApexCharts(document.querySelector(`#${holder}`), options)
            chart.render()
        } else {
            $(`#${holder}`).html(`<h6 class="text-center">Data is Not Valid</h6>`)
        }
    } else {
        $(`#${holder}`).html(`
            <img src="{{asset('images/nodata.png')}}" class="img nodata-icon">
            <h6 class="text-center">No Data</h6>
        `)
    }
}


const generateTableContextTotal = (holder, data, key_currency) => {
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
                tbody += `<td>${key.includes('price') || (key.includes(key_currency) && key_currency) ? `Rp. ${el[key].toLocaleString()}` : el[key]}</td>`
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
        exportButtonData(holder,data)
    } 
}

const exportButtonData = (holder, data) => {
    if(data.length > 0){
        $(`#${holder}`).append(`
            <button id='download-btn-data-${holder}' class='btn btn-primary w-100'>
                <i class="fa-solid fa-download"></i> Download CSV
            </button>
        `);

        $(`#download-btn-data-${holder}`).on('click', function() {
            const csvData = convertToCSV(data)
            downloadCSV(csvData, `data_export_${holder}.csv`)
            Swal.fire({
                title: "Downloaded!",
                text: `You have downloaded ${ucEachWord(holder.replaceAll('_',' '))} data`,
                icon: "success"
            });
        });
    } 
}

const convertToCSV = (data) => {
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

const downloadCSV = (csvData, filename) => {
    const blob = new Blob([csvData], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.setAttribute('href', url)
    a.setAttribute('download', filename)
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
}
