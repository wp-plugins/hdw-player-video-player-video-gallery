<?php

/******************************************************************
/*Send Email
******************************************************************/

$to       = $_POST["to"];
$from     = $_POST["from"];
$url      = $_POST["url"];
$subject  = "You have received a video!";

$headers  = "From: "."<" . $_POST["from"] .">\r\n";
$headers .= "Reply-To: " . $_POST["from"] . "\r\n";
$headers .= "Return-path: " . $_POST["from"];

$message  = $_POST["note"] . "\n\n";
$message .= "Video URL: " . $url;

if(mail($to, $subject, $message, $headers)) {
	echo "sent";
} else {
	echo "error";
}

?>