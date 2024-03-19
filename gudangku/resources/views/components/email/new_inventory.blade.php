<!DOCTYPE html>
<html>
    <head>
        <style>
            .bg{
                background: var(--primaryColor);
                width: 100vh;
                padding: 30px 20px;
            }
            .container{
                display: block !important;
                margin-inline: auto !important;
                border-radius: var(--roundedXLG);
                width: 50vh;
                min-width: 300px !important;
                height: auto;
                padding: 15px;
                background: #FFFFFF;
                box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
                text-align: center;
                color: var(--darkColor);
            }
            h5{
                font-size:22px;
                margin: 0;
            }
            h6{
                font-size:14px;
                margin: 0;
                font-weight: 500 !important;
            }
            hr{
                margin-top: 10px;
                margin-bottom: 10px;
            }
        </style>
    </head>
    <body class="bg">
        <div class="container">
            <h5 style="margin-bottom:10px;">Hello there, {{$username}}</h5>

            <h6 style="color:#3b82f6;">You have successfully {{$context}} to inventory called {{$body['inventory_name']}}
                {{$body['inventory_vol']}} {{$body['inventory_unit']}} 
                with category as {{$body['inventory_category']}}</h6>
            @if($body['inventory_desc'] != null)
                <p>{{$body['inventory_desc']}}</p>
            @else 
                <p style="color: grey; font-style: italic;">This item doesnt provide description</p>
            @endif
        
            <div style="text-align: left;">
                <h6>Properties</h6>
                <h6>Located at : {{$body['inventory_room']}} Room 
                    @if($body['inventory_storage'] != null)
                        / {{$body['inventory_storage']}}
                    @endif
                    @if($body['inventory_rack'] != null)
                        / {{$body['inventory_rack']}}
                    @endif
                </h6>
                <h6>Price : Rp. {{number_format($body['inventory_price'], 0, ',', '.')}}</h6>
                <h6>Created at : {{date("Y M d H:i:s")}}</h6>
            </div>
        </div>
    </body>
</html>