@extends('components.layout')

<!-- PHP Helpers -->
<?php
    use App\Helpers\Generator;
?>  
@php($isMobile = Generator::isMobileDevice())  

@section('content')
    <script>
        let page = 1
        let token = '<?= session()->get("token_key"); ?>'
    </script>
    <div class="content">
        @include('others.profile')
        @include('others.notification')
        <h2 class="text-white fw-bold mb-4" style="font-size:<?php if(!$isMobile){ echo "calc(var(--textXJumbo)*1.75)"; } else { echo "var(--textXJumbo)"; } ?>">User</h2>
        <a class="btn btn-danger mb-3 me-2" href="/"><i class="fa-solid fa-arrow-left" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Back @endif</a>
        <form action='/user/save_as_csv' method='POST' class='d-inline'>
            @csrf
            <button class="btn btn-primary mb-3 me-2" href=""><i class="fa-solid fa-print" style="font-size:var(--textXLG);"></i> @if(!$isMobile) Print @endif</button>
        </form>
        <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="container bordered" id="last_login-section">
                    <h1 class="fw-bold my-3" style="font-size:calc(2*var(--textLG));">Last Login</h1>
                    @include('user.last_login')
                </div>      
            </div>
        </div>
        <h1 class="fw-bold my-3" style="font-size:calc(2*var(--textLG));">All User</h1>
        @include('user.table')
    </div>
@endsection