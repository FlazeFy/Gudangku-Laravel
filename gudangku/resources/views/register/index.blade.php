@extends('components.layout')

@section('content')
    <style>
        .pt-custom {
            padding-top: 0;
        }
        @media screen and (max-width: 767px) {
            .pt-custom {
                padding-top: 10vh !important;
            }
        }
    </style>
    <div class="p-3 d-block mx-auto pt-custom" style="max-width:1080px">
        <br><br>
        @include('register.form')
    </div>
    
    <script>
        is_process = false
        window.addEventListener('beforeunload', function(event) {
            is_process = checkFillingStatus(['checkTerm','username','password','email','password_validation'])
            if(is_process == true){
                event.preventDefault()
                event.returnValue = ''
            }
        })
        $(document).ready(function() {
            formValidation('Account')
        })
    </script>
@endsection
