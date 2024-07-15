<style>
    .dashboard-title, .dashboard-subtitle {
        text-align: center;
    }
    .dashboard-title {
        font-size: calc(var(--textXJumbo) * 3) !important; 
        font-weight: bold;
    }
    .dashboard-subtitle {
        font-size: var(--textXJumbo) !important; 
        font-weight: 600;
    }
</style>

<div class="row mb-3">
    <div class="col-lg-4 col-md-6 col-sm-12">
        <h1 class="dashboard-title">{{$total_item->total}} @if($isMobile) <span style="font-size:var(--textJumbo)">Item</span> @endif</h1>
        @if(!$isMobile)
            <h2 class="dashboard-subtitle">Item</h2>
        @endif
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12">
        <h1 class="dashboard-title">{{$total_fav->total}} @if($isMobile) <span style="font-size:var(--textJumbo)">Favorite Item</span> @endif</h1>
        @if(!$isMobile)
            <h2 class="dashboard-subtitle">Favorite Item</h2>
        @endif
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12">
        <h1 class="dashboard-title">{{$total_low->total}} @if($isMobile) <span style="font-size:var(--textJumbo)">Low Capacity</span> @endif</h1>
        @if(!$isMobile)
            <h2 class="dashboard-subtitle">Low Capacity</h2>
        @endif
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12 pt-4">
        @if($isMobile)
            <h6 class="dashboard-subtitle" style="font-size:var(--textJumbo) !important;">Last Added</h6>
        @endif
        <h2 class="text-center fw-bold" style="font-size: calc(var(--textXJumbo) * 1.2) !important;">{{$last_added->inventory_name}}</h2>
        @if(!$isMobile)
            <h2 class="dashboard-subtitle">Last Added</h2>
        @endif
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12 pt-4">
        @if($isMobile)
            <h6 class="dashboard-subtitle" style="font-size:var(--textJumbo) !important;">Most Category</h6>
        @endif
        <h2 class="text-center fw-bold" style="font-size: calc(var(--textXJumbo) * 1.2) !important;">({{$most_category->total}}) {{$most_category->context}}</h2>
        @if(!$isMobile)
            <h2 class="dashboard-subtitle">Most Category</h2>
        @endif
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12 pt-4">
        @if($isMobile)
            <h6 class="dashboard-subtitle" style="font-size:var(--textJumbo) !important;">The Highest Price</h6>
        @endif
        <h2 class="text-center fw-bold" style="font-size: calc(var(--textXJumbo) * 1.2) !important;">{{$highest_price->inventory_name}}</h2>
        @if(!$isMobile)
            <h2 class="dashboard-subtitle">The Highest Price</h2>
        @endif
    </div>
</div><br>