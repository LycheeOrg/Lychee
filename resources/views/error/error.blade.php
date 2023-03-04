<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">

        <title>Error</title>

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: sans-serif;
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
                flex-flow: row wrap;
                justify-content: center;
                align-content: center;
            }

            .position-ref {
                position: relative;
            }

            .code {
                font-size: 36px;
                padding: 0 15px 0 15px;
                text-align: center;
            }

            .message {
                font-size: 18px;
                border-left: 2px solid;
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

            <div class="message" style="padding: 10px;"><h1>{{ $type }}</h1><p>{{ $message }}</p></div>
        </div>
    </body>
</html>
