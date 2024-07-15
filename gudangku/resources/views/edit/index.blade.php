@extends('components.layout')

@section('content')
    <div class="content" style="max-width:1440px;">
        <h2 class="text-white fw-bold mb-4" style="font-size:36px;">Edit Inventory</h2>
        <a class="btn btn-danger mb-3 me-2" href="/inventory"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> Back</a>
        @include('edit.form')
    </div>
@endsection
