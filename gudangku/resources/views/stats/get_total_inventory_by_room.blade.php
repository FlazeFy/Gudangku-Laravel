@php($selected = session()->get('toogle_total_stats'))
@if($selected == 'item')
    @php($ctx = 'total_inventory_by_room')
@elseif($selected == 'price')
    @php($ctx = 'total_price_inventory_by_room')
@endif

@include('others.pie_chart', ['data'=>$total_inventory_by_room, 'ctx'=>$ctx])