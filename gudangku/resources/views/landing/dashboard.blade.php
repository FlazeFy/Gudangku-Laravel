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
        <h1 class="dashboard-title">{{$total_item->total}}</h1>
        <h2 class="dashboard-subtitle">Item</h2>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12">
        <h1 class="dashboard-title">{{$total_fav->total}}</h1>
        <h2 class="dashboard-subtitle">Favorite Item</h2>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12">
        <h1 class="dashboard-title">{{$total_low->total}}</h1>
        <h2 class="dashboard-subtitle">Low Capacity</h2>
    </div>
</div><br>