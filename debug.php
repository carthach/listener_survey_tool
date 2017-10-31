<?php

	session_start();
	
	
	$questionCounter = $_SESSION['QUESTIONCOUNTER'];
	$questionDetails = $_SESSION['QUESTIONS'][$questionCounter];
		
	print_r($questionDetails);
?>