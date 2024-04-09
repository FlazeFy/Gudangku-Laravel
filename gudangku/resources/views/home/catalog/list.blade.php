<h2 class="text-white fw-bold mb-4 mt-3" style="font-size:var(--textXJumbo);">By {{ucwords($view)}} - {{ucwords($context)}}</h2>
<div class="row"> 
@foreach($inventory as $in)
    <div class="col-lg-4 col-md-6 col-sm-12">
        <button class="btn-feature mb-4 position-relative" <?php if($in['deleted_at'] != null){ echo 'style="background:rgba(221, 0, 33, 0.15) !important;"';} ?>>
            @if($in['is_favorite'] == '1')
                <span style="background: var(--dangerBG); top:-15px; left:-20px;" class="p-3 me-1 rounded-circle position-absolute"><i class="fa-solid fa-heart mx-1"></i></span>
            @endif
            @if($in['reminder_id'])
                <span style="background: var(--successBG); top:-15px; left:45px;" class="p-3 me-1 rounded-circle position-absolute"><i class="fa-solid fa-bell mx-1"></i></span>
            @endif
            @if($in->inventory_image == null)
                <i class="fa-solid fa-box" style="font-size:90px;"></i>
            @else 
                <img class="img img-fluid" style="border-radius: var(--roundedMD);" src="{{$in->inventory_image}}" title="{{$in->inventory_name}}">
            @endif
            <h2 class="mt-3" style="font-size:var(--textXLG);">{{$in['inventory_name']}}</h2>
            <div class="mt-3 d-flex justify-content-center">
                <span style="background: var(--successBG);" class="py-1 px-2 me-1 rounded">Rp. {{number_format($in['inventory_price'], 0, ',', '.')}}</span>
                <span style="background: var(--primaryColor);" class="py-1 px-2 me-1 rounded">{{$in['inventory_vol']}} {{$in['inventory_unit']}}</span>
                @if($in['inventory_capacity_unit'] == 'percentage')
                    <span style="background: <?php if($in['inventory_capacity_vol'] > 30){ echo 'var(--infoBG)'; } else { echo 'var(--dangerBG)'; }?>;" class="py-1 px-2 me-1 rounded">{{$in['inventory_capacity_vol']}}%</span>
                @endif
                @if($in['reminder_id'])
                    <span style="background: var(--successBG);" class="py-1 px-2 me-1 rounded"><i class="fa-solid fa-bell"></i> {{ucwords(str_replace("_"," ",$in->reminder_type))}}</span>
                @endif
            </div>
        </button>
    </div>
@endforeach
</div>