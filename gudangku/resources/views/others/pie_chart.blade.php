<div class="text-center">
    @if(count($data) != 0)
        <h2 style="font-size:var(--textJumbo); font-weight:600;">{{ucwords(str_replace('_',' ',$ctx))}}</h2><br>
        <div id="Pie_{{$ctx}}"></div>
    @else
        <img src="{{asset('assets/nodata.png')}}" class="img nodata-icon">
        <h6 class="text-center">No Data</h6>
    @endif
</div>

<script type="text/javascript">
    var options = {
        series: [
            <?php 
                foreach($data as $c){
                    echo "$c->total,";
                }
            ?>
        ],
        chart: {
        width: '360',
        type: 'pie',
    },
    labels: [
        <?php 
            foreach($data as $c){
                echo "'$c->context',";
            }
        ?>
    ],
    colors: ['#F9DB00','#009FF9','#F78A00','#42C9E7'],
    legend: {
        position: 'bottom'
    },
    responsive: [{
        // breakpoint: 480,
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

    var chart = new ApexCharts(document.querySelector("#Pie_{{$ctx}}"), options);
    chart.render();
</script>