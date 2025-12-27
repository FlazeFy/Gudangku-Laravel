<div class="mb-3 d-flex flex-wrap gap-2">
    @include('components.back_button', ['route' => '/'])
    @include('home.toogle_view')
    @if($role == 0)
        <a class="btn btn-primary" href="/inventory/add" id="add_inventory-button"><i class="fa-solid fa-plus"></i> Inventory</a>
    @endif
    <a class="btn btn-primary" href="/stats"><i class="fa-solid fa-chart-pie"></i> @if(!$isMobile) Stats @endif</a>
    @if($role == 0)
        <a class="btn btn-primary" href="/calendar"><i class="fa-solid fa-calendar"></i><span class="d-none d-md-inline"> Calendar</span></a>
        <a class="btn btn-primary" href="/room/2d"><i class="fa-solid fa-layer-group"></i> 2D Room</a>
        <a class="btn btn-primary" href="/room/3d"><i class="fa-solid fa-cube"></i> 3D Room</a>
    @endif
    <span id="toolbar-button-section"></span>
</div>