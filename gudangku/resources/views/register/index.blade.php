@extends('components.layout')

@section('content')
    <div class="p-3 d-block mx-auto" style="width:1080px;">
        <br><br>
        @include('register.form')
    </div>
    <script>
        is_process = false
        window.addEventListener('beforeunload', function(event) {
            is_process = check_filling_status(['checkTerm','username','password','email','password_validation'])
            if(is_process == true){
                event.preventDefault()
                event.returnValue = ''
            }
        });
    </script>
@endsection
