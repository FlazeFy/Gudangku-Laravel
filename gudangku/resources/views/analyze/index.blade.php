@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  

@section('content')
    <style>
        p {
            font-size:var(--textLG);
            font-weight:500;
        }
        h3 {
            font-size:var(--textXJumbo);
            font-weight:bold;
            margin-bottom:var(--spaceMD);
        }
    </style>
    <link rel="stylesheet" href="{{ asset('/room_v1.0.css') }}"/>

    <div class="content">
        @include('others.profile')
        @include('others.notification')
        <h2 class="text-white fw-bold mb-4" style="font-size:<?php if(!$isMobile){ echo "calc(var(--textXJumbo)*1.75)"; } else { echo "var(--textXJumbo)"; } ?>">Analyze : {{ucfirst($type)}} <b class='inventory_name text-primary'></b></h2>
        <div class="d-flex justify-content-start">
            <a class="btn btn-danger mb-3 me-2" href="/inventory/edit/{{$id}}"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Back @endif</a>
            <a class="btn btn-primary mb-3 me-2" onclick="generate_custom()"><i class="fa-solid fa-print" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Custom Print @endif</a>
        </div>
        <div id="render_area">
            <div class="row">
                <div class="col">
                    <br>
                    @include('analyze.inventory_price')
                    @include('analyze.inventory_category')
                </div>
                <div class="col">
                    <div id='price-pie-chart-holder'></div>
                </div>
            </div>
            <br><br>
            <div class="row">
                <div class="col">
                    <div id='price-line-chart-holder'></div>
                </div>
                <div class="col">
                    <br>
                    @include('analyze.inventory_unit_vol')
                    @include('analyze.inventory_room')
                    @include('analyze.inventory_merk')
                </div>
            </div><br><br>
            @include('analyze.inventory_history')
            <div class="row">
                <div class="col-lg-5 col-md-6 col-sm-12 col-12">
                    @include('analyze.inventory_activity')
                </div>
                <div class="col-lg-7 col-md-6 col-sm-12 col-12">
                    <div id='layout-holder'></div>
                </div>
            </div>
        </div>
        <div id="work_area" class='d-none'></div>
    </div>
    <script>
        const get_analyze = (id) => {
            Swal.showLoading()
            const year_sess = <?= session()->get('toogle_select_year') ?>;
            const year = year_sess ?? new Date().getFullYear()
            $.ajax({
                url: `/api/v1/inventory/analyze/${id}?year=${year}`,
                type: 'GET',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader("Accept", "application/json");
                    xhr.setRequestHeader("Authorization", "Bearer <?= session()->get("token_key"); ?>");    
                },
                success: function(response) {
                    Swal.close()
                    const data = response.data
                    $('.inventory_name').text(data.inventory_name)
                    $('.inventory_category').text(data.inventory_category)
                    $('.inventory_room').text(data.inventory_room)
                    $('.inventory_unit').text(data.inventory_unit)
                    $('.inventory_vol').text(data.inventory_vol)
                    $('.inventory_merk').text(data.inventory_merk)
                    $('#created_at').text(getDateToContext(data.created_at,'calendar'))
                    $('#days_exist').text(count_time(data.created_at,null,'day'))

                    if(data.updated_at){
                        $('#updated_at').html(` And the last updated on ${getDateToContext(data.updated_at,'calendar')} that about <b>${count_time(data.updated_at,null,'day')}</b> days ago.`)
                    }
                    const isExpensive = data.inventory_price > data.inventory_price_analyze.average_inventory_price
                    const capacityClass = isExpensive ? 'bg-danger' : 'bg-success'
                    const capacityText = isExpensive ? 'Expensive' : 'Cheap'
                    const capacityIcon = isExpensive ? '<i class="fa-solid fa-triangle-exclamation"></i>': '<i class="fa-solid fa-thumbs-up"></i>'

                    $('#expensiveness_holder').html(`<a class='${capacityClass} rounded-pill px-3 py-2 ms-3' style='font-size:var(--textXMD);'>${capacityIcon} ${capacityText}</a>`)

                    if(data.inventory_history_analyze){
                        let report_history = ' This item also had been used in report '
                        let tbody_report = ''

                        data.inventory_history_analyze.forEach(dt => {
                            report_history += `<b class='text-primary'>${dt.report_category} (${dt.total}x)</b>, `
                        });
                        data.inventory_report.forEach(dt => {
                            tbody_report += `
                                <tbody>
                                    <tr>
                                        <td>${dt.report_title}</td>
                                        <td class='text-center'>${dt.report_category}</td>
                                        <td class='text-center'>${getDateToContext(dt.created_at,'calendar')}</td>
                                    </tr>
                                </tbody>
                            `
                        });

                        $('#last_report_history').html(`${report_history}`)
                        $('#last_report_history_table').html(`
                            <h4 class='mt-3 fw-bold'>Showing ${data.inventory_report.length} Last Report where this inventory can be found</h4>
                            <table class='table table-bordered my-3'>
                                <thead class='text-center'>
                                    <tr>
                                        <th>Report Title</th>
                                        <th>Category</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>${tbody_report}</tbody>
                            </table>
                        `)
                        const totalUsedInReportYear = data.inventory_in_monthly_report.reduce((sum, dt) => {
                            return sum + dt.total
                        }, 0);
                        $('#whole_year_total_in_report').html(`<p>In whole year ${year}, there is about <b class='text-primary'>${totalUsedInReportYear} report</b> are using this inventory.</p>`)
                    }
                    if(data.inventory_capacity_unit && data.inventory_capacity_vol){
                        $('#capacity_holder').html(`
                            <h2 class='text-primary fw-bolder mt-2' style='font-size:calc(var(--textJumbo)*2.5);'>${data.inventory_capacity_vol} <span class='text-white' style='font-size:calc(var(--textLG)*1.75);'>${data.inventory_capacity_unit == 'percentage' ? '%' : data.inventory_capacity_unit} of remaining capacity</span></h2>
                        `)
                        const isLowCapacity = data.inventory_capacity_unit === 'percentage' && data.inventory_capacity_vol <= 30
                        const capacityClass = isLowCapacity ? 'bg-danger' : 'bg-success'
                        const capacityText = isLowCapacity ? 'At Low Capacity' : 'Normal Capacity'
                        const capacityIcon = isLowCapacity ? '<i class="fa-solid fa-triangle-exclamation"></i>': '<i class="fa-solid fa-thumbs-up"></i>'

                        $('#low_capacity_holder').html(`<a class='${capacityClass} rounded-pill px-3 py-2 ms-3' style='font-size:var(--textXMD);'>${capacityIcon} ${capacityText}</a>`)
                    }

                    $('.inventory_unit_vol').text(`${data.inventory_vol} ${data.inventory_unit}`)
                    $('.inventory_price').text(`Rp. ${number_format(data.inventory_price, 0, ',', '.')}`)
                    $('#inventory_price_max').text(`Rp. ${number_format(data.inventory_price_analyze.max_inventory_price, 0, ',', '.')}`)
                    $('#inventory_price_min').text(`Rp. ${number_format(data.inventory_price_analyze.min_inventory_price, 0, ',', '.')}`)
                    $('#diff_price_status').text(data.inventory_price_analyze.diff_status_average_to_price)
                    $('#diff_price_ammount').text(`Rp. ${number_format(Math.abs(data.inventory_price_analyze.diff_ammount_average_to_price), 0, ',', '.')}`)
                    $('#inventory_price_avg').text(`Rp. ${number_format(data.inventory_price_analyze.average_inventory_price, 0, ',', '.')}`)
                    $('#inventory_price_avg_category').text(`Rp. ${number_format(data.inventory_category_analyze.average_price, 0, ',', '.')}`)
                    $('#inventory_price_avg_merk').text(`Rp. ${number_format(data.inventory_merk_analyze.average_price, 0, ',', '.')}`)
                    $('#total_inventory_category').text(data.inventory_category_analyze.total)
                    $('#total_inventory_merk').text(data.inventory_merk_analyze.total)
                    $('#inventory_storage_rack').html(`${data.inventory_storage && `storage <span class='text-primary'>${data.inventory_storage}</span>`}${data.inventory_rack && `, rack <span class='text-primary'>${data.inventory_rack}</span>`}`)
                    $('#total_inventory_room').text(data.inventory_room_analyze.total)
                    $('#inventory_price_avg_room').text(`Rp. ${number_format(data.inventory_room_analyze.average_price, 0, ',', '.')}`)
                    $('#inventory_price_avg_unit').text(`Rp. ${number_format(data.inventory_unit_analyze.average_price, 0, ',', '.')}`)
                    $('#total_inventory_unit').text(data.inventory_unit_analyze.total)

                    const data_price_pie_chart = [
                        { context: data.inventory_name, total: data.inventory_price},
                        { context: 'Whole Inventory', total: data.inventory_price_analyze.sub_total - data.inventory_price}
                    ]
                    const data_price_avg_line_chart = [
                        { context: `By Category`, average_price: data.inventory_category_analyze.average_price, inventory_price: data.inventory_price},
                        { context: `By Unit`, average_price: data.inventory_unit_analyze.average_price, inventory_price: data.inventory_price},
                        { context: `By Room`, average_price: data.inventory_room_analyze.average_price, inventory_price: data.inventory_price}
                    ]
                    generate_pie_chart(`Price Comparison to All Inventory`,'price-pie-chart-holder',data_price_pie_chart)
                    generate_line_column_chart(`Average Price Comparison to All Inventory`,'price-line-chart-holder',data_price_avg_line_chart,60)
                    generate_bar_chart(`Inventory using In Report ${year}`,'monthly_report_history_table',data.inventory_in_monthly_report)

                    if(data.inventory_layout){
                        $('#layout-holder').html(`
                            <h3>8. The Room Layout</h3>
                            <p>You can find <span class='text-primary'>${data.inventory_name}</span> at storage <span class='text-primary'>${data.inventory_storage}</span>, layout <span class='text-primary'>${data.inventory_layout.layout}</span>.
                            This storage is created at ${getDateToContext(data.inventory_layout.created_at,'calendar')} about ${count_time(data.inventory_layout.created_at,null,'day')} days ago.
                            </p>
                            <div id='room_layout_map' class='mx-3 mt-2'></div>
                            <br>
                        `)
                        generate_map_room('#room_layout_map',[data.inventory_layout],false,data.inventory_room)
                    } else {
                        $('#layout-holder').html(`
                            <h3>8. The Room Layout</h3>
                            <p class='text-secondary fst-italic'>- This inventory is not assigned to room storage or the storage may not valid -</p>
                            <br>
                        `)
                    }

                    const data_inventory_activity_report = data.inventory_activity_report
                    const days = ["Sun","Sat","Fri","Thu","Wed","Tue","Mon"]
                    let data_heatmap_inventory_activity = []
                    days.forEach(dy => {
                        data_heatmap_inventory_activity.push(
                            { name: dy, data: data_inventory_activity_report.filter(el => el.day === dy).map(el => ({ x: el.context, y: el.total })) }
                        )
                    });
                    generate_heatmap_inventory_activity(data_heatmap_inventory_activity)
                },
                error: function(response, jqXHR, textStatus, errorThrown) {
                    Swal.close()
                    if(response.status != 404){
                        Swal.fire({
                            title: "Oops!",
                            text: "Something wrong. Please contact admin",
                            icon: "error"
                        });
                    } 
                }
            });
        }
        get_analyze("<?= $id ?>")

        let toggle_show_customize = false
        const generate_custom = () => {
            const header = `<?= Generator::generateDocTemplate('header') ?>`
            const footer = `<?= Generator::generateDocTemplate('footer') ?>`
            const style = `<?= Generator::generateDocTemplate('style') ?>`
            if(!toggle_show_customize){
                let editor = new RichTextEditor("#work_area")
                editor.setHTML(`<head>${style}<link rel="stylesheet" href="{{ asset('/room_v1.0.css') }}"/></head>${header}${$('#render_area').html()}${footer}`)
                toggle_show_customize = true
                $('#work_area').removeClass('d-none')
                $('#render_area').addClass('d-none')
            } else {
                $('#work_area').empty().removeClass().addClass('d-none')
                $('#render_area').removeClass('d-none')
                toggle_show_customize = false
            }
        }
    </script>
@endsection
