<?php
session_start();
date_default_timezone_set('Europe/Madrid');

//Create the CSV string for the email
function create_csv_string($data) {

  // Open temp file pointer
  if (!$fp = fopen('php://temp', 'w+')) return FALSE;

  // Loop data and write to file pointer
  foreach ($data as $line) fputcsv($fp, $line);

  // Place stream pointer at beginning
  rewind($fp);

  // Return the data
  return stream_get_contents($fp);

}

//Send an email with CSV attachment
function send_csv_mail ($csvData, $body, $subject = 'Survey Results', $to = 'carthach.onuanain@upf.edu', $from = 'carthach.onuanain@upf.edu') {

  // This will provide plenty adequate entropy
  $multipartSep = '-----'.md5(time()).'-----';

  // Arrays are much more readable
  $headers = array(
    "From: $from",
    "Reply-To: $from",
    "Content-Type: multipart/mixed; boundary=\"$multipartSep\""
  );

  // Make the attachment
//   $attachment = chunk_split(base64_encode(create_csv_string($csvData))); 
  $attachment = chunk_split(base64_encode($csvData));
  
  //Create filename
  $timestamp = date("Y-m-d_H-i-s");
  $filename = "rhythmcat_results_" . $timestamp . ".csv";
  
  $subject = $subject . $timestamp;

  // Make the body of the message
  $body = "--$multipartSep\r\n"
        . "Content-Type: text/plain; charset=ISO-8859-1; format=flowed\r\n"
        . "Content-Transfer-Encoding: 7bit\r\n"
        . "\r\n"
        . "$body\r\n"
        . "--$multipartSep\r\n"
        . "Content-Type: text/csv\r\n"
        . "Content-Transfer-Encoding: base64\r\n"
        . "Content-Disposition: attachment; filename=\"$filename\"\r\n"
        . "\r\n"
        . "$attachment\r\n"
        . "--$multipartSep--";

   // Send the email, return the result
   return @mail($to, $subject, $body, implode("\r\n", $headers)); 
}
?>

<html>
<head>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

<?php

//Send the form results and display the comment form and thank you
if($_SESSION['results']) {
	$array = $_SESSION['results'];
	
	$name = $_SESSION['name'];

	$text = "Name: " . $_SESSION['name'] . "\n";
	$text .= "Email: " . $_SESSION['email'] . "\n";
	
	$subject = "Survey Results (RhythmCAT): " . $_SESSION['name'];

	send_csv_mail($array, $text, $subject);
	
	$_SESSION = array();
	session_destroy();
?>

<p>Thank you, the survey is now complete and the results have been sent!</p>
<p>If you have any comments or feedback please enter them below, otherwise just close this page</p>
<form name="commentForm" action="finish.php" method="post">
	<p>
		<textarea rows="4" cols="50" name="comments"></textarea>
	</p>
	<p>
		<input type="submit" value="Submit" />
		<input type="hidden" value="<?php echo $name; ?>" name="name" />
	</p>
</form>

<?php

//We've received comments
} else if($_POST['comments'] && $_POST['comments'] != '') {
		// The message
		$message = $_POST['comments'];

		// In case any of our lines are larger than 70 characters, we should use wordwrap()
		$message = wordwrap($message, 70, "\r\n");
		
		$to = "carthach.onuanain@upf.edu";
		$subject = "Comments: " . $_POST['name'];
		$headers = "From: carthach.onuanain@upf.edu" . "\r\n" .
		"CC: carthach.onuanain@upf.edu";

		if(mail($to,$subject,$message,$headers))
			echo "<p>Mail Sent!</p>";
		else
			echo "<p>There was an error with the mail</p>";

		echo "<p>Thanks</p>";
} else {
	//We've nothing
	echo "<p>Nothing more to see here folks, move along!</p>";
}
?>
</body>
</html>