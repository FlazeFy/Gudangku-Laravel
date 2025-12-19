<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>
            @php
                $path = request()->path();
                
                if ($path === '/' || $path === '') {
                    echo "GudangKu";
                } else {
                    $segments = explode('/', trim($path, '/'));
                    $titleParts = ["GudangKu"];

                    foreach ($segments as $segment) {
                        // Skip UUID
                        if (preg_match('/^[0-9a-fA-F-]{36}$/', $segment)) {
                            continue;
                        }

                        $clean = str_replace(['-', '_'], ' ', $segment);
                        $titleParts[] = ucwords($clean);
                    }

                    echo implode(' | ', $titleParts);
                }
            @endphp
        </title>
        <link rel="icon" type="image/png" href="{{asset('images/logo.png')}}"/>
        
        @php
            $fullUrl = url()->current(); 
            $cleanedUrl = str_replace("http://127.0.0.1:8000/", "", $fullUrl);
        @endphp

        <!-- Fonts & Icon -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        <script src="https://kit.fontawesome.com/328b2b4f87.js" crossorigin="anonymous"></script>

        <!-- Bootstrap 5 -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <!-- CSS Collection -->
        @include('components.css_import')

        <!-- Jquery -->
        <script type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>

        <?php if(preg_match('(stats|analyze|embed)', $cleanedUrl)): ?>
            <!--Apex Chart-->
            <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <?php endif; ?>

        <?php if(preg_match('(calendar)', $cleanedUrl)): ?>
            <!--Full calendar.-->
            <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
        <?php endif; ?>

        <!-- Swal -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <?php if(preg_match('(inventory/add|inventory/edit)', $cleanedUrl)): ?>
            <!-- Tenserflow -->
            <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
        <?php endif; ?>

        <!-- JS Collection -->
        <script src="{{ asset('/global_v1.0.js')}}"></script>
        <script src="{{ asset('/template_v1.0.js')}}"></script>

        <?php if(preg_match('(stats|analyze|embed)', $cleanedUrl)): ?>
            <script src="{{ asset('/chart_v1.0.js')}}"></script>
        <?php endif; ?>

        <?php if(preg_match('(doc|analyze)', $cleanedUrl)): ?>
            <!-- Richtext -->
            <link rel="stylesheet" href="{{ asset('/richtexteditor/rte_theme_default.css')}}" />
            <script type="text/javascript" src="{{ asset('/richtexteditor/rte.js')}}"></script>
            <script type="text/javascript" src="{{ asset('/richtexteditor/rte-upload.js')}}"></script>
            <script type="text/javascript" src="{{ asset('/richtexteditor/plugins/all_plugins.js')}}"></script>
        <?php endif; ?>

        <script>
            let token = '<?= session()->get("token_key"); ?>';
        </script>
    </head>
    <body class="antialiased">
        @include('others.detect_flazenapps')
        <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
            <div>
                <div style="min-height: 90vh;">
                    @yield('content')
                </div>
                <?php if(!preg_match('(embed)', $cleanedUrl)): ?>
                @include('components.footer')
                <?php endif; ?>
            </div>
        </div>
        @include('components.scroll_top')
        <script>
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl)
            })
        </script>
    </body>
    
    <!--Modal-->
    @include('components.modal.success')
    @include('components.modal.about')
    @include('components.modal.success_mini')
    @include('components.modal.failed')
</html>
