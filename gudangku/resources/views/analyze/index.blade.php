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
                    $('.inventory_price').text(`Rp. ${number_format(data.inventory_price, 0, ',', '.')}`)
                    $('#inventory_price_max').text(`Rp. ${number_format(data.inventory_price_analyze.max_inventory_price, 0, ',', '.')}`)
                    $('#inventory_price_min').text(`Rp. ${number_format(data.inventory_price_analyze.min_inventory_price, 0, ',', '.')}`)
                    $('#diff_price_status').text(data.inventory_price_analyze.diff_status_average_to_price)
                    $('#diff_price_ammount').text(`Rp. ${number_format(Math.abs(data.inventory_price_analyze.diff_ammount_average_to_price), 0, ',', '.')}`)
                    $('#inventory_price_avg').text(`Rp. ${number_format(data.inventory_price_analyze.average_inventory_price, 0, ',', '.')}`)
                    $('#inventory_price_avg_category').text(`Rp. ${number_format(data.inventory_category_analyze.average_price, 0, ',', '.')}`)
                    $('#total_inventory_category').text(data.inventory_category_analyze.total)
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
