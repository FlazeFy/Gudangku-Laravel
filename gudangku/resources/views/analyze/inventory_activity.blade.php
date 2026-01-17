<h2>7. The Report Activity</h2>
<p>Here you can see total report found using <b class='inventory_name'></b> inventory for the last 31 days</p>
<div class='mt-3' id="inventory_activity_heatmap"></div>
<br>

<script type="text/javascript">
    const generateHeatmapInventoryActivity = (series) => {
        var options = {
            series: series,
            plotOptions: {
                heatmap: {
                    shadeIntensity: 0.45,
                    colorScale: {
                        ranges: [{
                            from: 0,
                            to: 0,
                            color: '#3b82f6'
                        }]
                    },
                    stroke: {
                        width: 2, 
                        colors: ['#000000'] 
                    }
                }
            },
            legend: {
                show: false
            },
            chart: {
                height: 260,
                type: 'heatmap',
                toolbar: {
                    show: false
                },
            },
            dataLabels: {
                enabled: false
            },
            colors: ["#3b82f6"],
        };

        var chart = new ApexCharts(document.querySelector("#inventory_activity_heatmap"), options);
        chart.render();
    }
</script>