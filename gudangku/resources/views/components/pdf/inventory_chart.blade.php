<!DOCTYPE html>
<html>
<head>
    <title>GudangKu</title>
    <style>
        body { font-family: Helvetica; }
        table { border-collapse: collapse; font-size:10px; width:100%; }
        td, th { border: 1px solid #dddddd; text-align: left; padding: 8px; }
        th { text-align:center; }
        .date-text { font-style:italic; font-weight:normal; color:grey; font-size:11px; }
        thead { background-color:rgba(59, 131, 246, 0.75); }
    </style>
</head>
<body>
    <div style='text-align:center;'>
        <h1 style='color:#3b82f6; margin:0;'>GudangKu</h1>
        <h4 style='color:#212121; margin:0; font-style:italic;'>Smart Inventory, Easy Life</h4><br>
    </div>
    <hr>

    <h3 style='margin:0 0 6px 0;'>Username : {{$username}}</h3>
    <p style='font-size:13px; text-align: justify;'>
        At {{$date}}, the system just run weekly audit for your inventory. Here you can see the stats in this document :
    </p>  

    @foreach($charts as $chart)
        <div>
            <img src="{{ storage_path('app/public/' . $chart) }}" alt="Bar Chart">
        </div>
    @endforeach

    <br><hr>
    <div>
        <h6 class='date-text' style='margin: 0;'>Parts of FlazenApps</h6>
        <h6 class='date-text' style='margin: 0; float:right; margin-top:-12px;'>Generated at {{$datetime}} by <span style='color:#3b82f6;'>https://gudangku.leonardhors.com</span></h6>
    </div>
</body>
</html>
