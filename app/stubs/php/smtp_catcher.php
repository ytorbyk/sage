#!/usr/bin/php
<?php

# create a filename for the emlx file
date_default_timezone_set("TIMEZONE");
list($ms, $time) = explode(' ', microtime());
$filename = dirname(__FILE__) . '/' . date('Y-m-d H.i.s.', $time) . substr($ms, 2, 3) . '.eml';

# write the email contents to the file
$email_contents = fopen('php://stdin', 'r');
$fstat = fstat($email_contents);
$sEmail = "";
while (!feof($email_contents)) { 
    $sEmail .= fread($email_contents, 1024); 
}
fclose($email_contents);
$lines = explode("\n", $sEmail);

// empty vars
$from = "";
$subject = "";
$headers = "";
$message = "";
$splittingheaders = true;
for ($i=0; $i < count($lines); $i++) {
    if ($splittingheaders) {
        // this is a header
        $headers .= $lines[$i]."\n";

        // look out for special headers
        if (preg_match("/^Subject: (.*)/", $lines[$i], $matches)) {
            $subject = $matches[1];
        }
        if (preg_match("/^From: (.*)/", $lines[$i], $matches)) {
            $from = $matches[1];
        }
        if (preg_match("/^To: (.*)/", $lines[$i], $matches)) {
            $to = $matches[1];
        }
    } else {
        // not a header, but message
        $message .= $lines[$i]."\n";
    }

    if (trim($lines[$i])=="") {
        // empty line, header section has ended
        $splittingheaders = false;
    }
}
$filename = 'MAIL_FOLDER' . DIRECTORY_SEPARATOR
    . date('Y.m.d-H.i\'s', $time)
    . '[' . substr($ms, 2, 3) . '].eml';
file_put_contents($filename, $fstat['size']."\n");
file_put_contents($filename, $sEmail, FILE_APPEND);

# open up the emlx file (using Apple Mail)
#exec('open '.escapeshellarg($filename));

?>
