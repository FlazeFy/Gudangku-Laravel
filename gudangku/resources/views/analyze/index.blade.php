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
    <div class="content" style="width:1280px;">
        <h2 class="text-white fw-bold mb-4" style="font-size:<?php if(!$isMobile){ echo "calc(var(--textXJumbo)*1.75)"; } else { echo "var(--textXJumbo)"; } ?>">Analyze : {{ucfirst($type)}} <b class='inventory_name text-primary'></b></h2>
        <div class="d-flex justify-content-start">
            <a class="btn btn-danger mb-3 me-2" href="/inventory/edit/{{$id}}"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Back @endif</a>
        </div>
        @include('analyze.inventory_price')
        @include('analyze.inventory_category')
        @include('analyze.inventory_unit_vol')
        @include('analyze.inventory_room')
        @include('analyze.inventory_history')
    </div>
    <script>
        const get_analyze = (id) => {
            Swal.showLoading()
            $.ajax({
                url: `/api/v1/inventory/analyze/${id}`,
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
                    $('#created_at').text(getDateToContext(data.created_at,'calendar'))
                    $('#days_exist').text(count_time(data.created_at,null,'day'))
                    if(data.updated_at){
                        $('#updated_at').html(` And the last updated on ${getDateToContext(data.updated_at,'calendar')} that about <b>${count_time(data.updated_at,null,'day')}</b> days ago.`)
                    }
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

                        $('#report_history').html(`${report_history}`)
                        $('#report_history_table').html(`
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
                    }
                    $('.inventory_unit_vol').text(`${data.inventory_vol} ${data.inventory_unit}`)
                    $('.inventory_price').text(`Rp. ${number_format(data.inventory_price, 0, ',', '.')}`)
                    $('#inventory_price_max').text(`Rp. ${number_format(data.inventory_price_analyze.max_inventory_price, 0, ',', '.')}`)
                    $('#inventory_price_min').text(`Rp. ${number_format(data.inventory_price_analyze.min_inventory_price, 0, ',', '.')}`)
                    $('#diff_price_status').text(data.inventory_price_analyze.diff_status_average_to_price)
                    $('#diff_price_ammount').text(`Rp. ${number_format(Math.abs(data.inventory_price_analyze.diff_ammount_average_to_price), 0, ',', '.')}`)
                    $('#inventory_price_avg').text(`Rp. ${number_format(data.inventory_price_analyze.average_inventory_price, 0, ',', '.')}`)
                    $('#inventory_price_avg_category').text(`Rp. ${number_format(data.inventory_category_analyze.average_price, 0, ',', '.')}`)
                    $('#total_inventory_category').text(data.inventory_category_analyze.total)
                    $('#inventory_storage_rack').html(`${data.inventory_storage && `storage <span class='text-primary'>${data.inventory_storage}</span>`}${data.inventory_rack && `, rack <span class='text-primary'>${data.inventory_rack}</span>`}`)
                    $('#total_inventory_room').text(data.inventory_room_analyze.total)
                    $('#inventory_price_avg_room').text(`Rp. ${number_format(data.inventory_room_analyze.average_price, 0, ',', '.')}`)
                    $('#inventory_price_avg_unit').text(`Rp. ${number_format(data.inventory_unit_analyze.average_price, 0, ',', '.')}`)
                    $('#total_inventory_unit').text(data.inventory_unit_analyze.total)
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
    </script>
@endsection
