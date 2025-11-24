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
                font-weight: 700 !important;
            }
            h6{
                font-size:14px;
                font-weight: 600 !important;
            }
            hr{
                margin-top: 10px;
                margin-bottom: 10px;
            }
        </style>
    </head>
    <body class="bg">
        <div class="container">
            <h5>Hello there, {{$username}}</h5>
            <p>You have successfully {{$context}}</p>
            <p><?php echo $body; ?></p>
            <hr>
            <div style="text-align: left;">
                <h6>Properties</h6>
                <h6>Created at : {{date("Y M d H:i:s")}}</h6>
            </div>
        </div>
    </body>
</html>