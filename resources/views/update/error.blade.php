<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">

        <title>Error - Database File Versions mismatch</title>

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
                border-right: 2px solid;
                padding: 0 15px 0 15px;
                text-align: center;
            }

            .message {
                font-size: 18px;
                text-align: left;
            }

            .form {
                width: 100%;
                padding-top: 18px;
                text-align: center;
            }

            input {
                border: #646b6f 1px solid;
                border-radius: 5px;
                -webkit-transition: all 0.30s ease-in-out;
                -moz-transition: all 0.30s ease-in-out;
                -ms-transition: all 0.30s ease-in-out;
                -o-transition: all 0.30s ease-in-out;
                outline: none;
            }

            input:hover, input:focus {
                border: #aaa;
                box-shadow: 0 0 5px rgba(81, 203, 238, 1);
                border: 1px solid rgba(81, 203, 238, 1);
            }

            button {
                border: #646b6f 1px solid;
                background: #fff;
                border-radius: 5px;
                padding-left: 10px;
                padding-right: 10px;
            }

            button:hover {
                color: #fff;
                background: #646b6f;
                cursor: pointer;
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
        <div class="form">
            <form method="POST" action="{{ route('migrate') }}">
                @csrf
                <input name="username" type="text" placeholder="admin username">
                <input name="password" type="password" placeholder="admin password">
                <button type="submit" class="submit">Migrate!</button>
            </form>
        </div>
    </div>
	<!-- Do not change even a single character in the block below without
	     also updating the checksum in config/secure-headers.php! -->
	<script>
	document.addEventListener("DOMContentLoaded", function(event) {
		document.querySelector("form").addEventListener("submit", function(e){
			document.querySelector("form").hidden = true;
			var text = document.createElement("div");
			text.innerHTML = "Migration started. <b>DO NOT REFRESH THE PAGE</b>.";
			document.querySelector(".form").appendChild(text);
			// e.preventDefault();    //stop form from submitting
		});
	});
	</script>
</body>
</html>
