<?php
    if(in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'], true)) {
        phpinfo();
        exit;
    }
?>
<html>
    <head>
        <title></title>
    </head>
    <body>
        <div>
            <img style="margin: 8% auto 0; display: block;" src="no-entry.jpg" />
        </div>
    </body>
</html>
