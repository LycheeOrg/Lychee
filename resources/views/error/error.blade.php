<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Error </title>

        <!-- Fonts -->
        <link rel="dns-prefetch" href="//fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                flex-flow: row wrap;;
                justify-content: center;
                align-content: center;
            }

            .position-ref {
                position: relative;
            }

            .code {
                font-size: 36px;
                border-right: 2px solid;
                padding: 0 15px 0 15px;
                text-align: center;
            }

            .message {
                font-size: 18px;
                text-align: left;
            }

            /* Inserting this collapsed row between two flex items will make 
             * the flex item that comes after it break to a new row */
            .break {
                flex-basis: 100%;
                height: 0; margin: 0; border: 0; 
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="code">{{ $code }}</div>

            <div class="message" style="padding: 10px;">{!! $message !!}</div>
        </div>
    </body>
</html>
