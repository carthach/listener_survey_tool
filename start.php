<?php
	ob_start();
	
	if($_POST) {
		session_start();
		
		$questionsFile = 'questions.csv';
		
		//New PHP
// 		$rows = array_map('str_getcsv', file($questionsFile));
// 
// 		$header = array_shift($all_rows);
// 		$questions = array();
// 		foreach ($all_rows as $row) {
// 			$questions[] = array_combine($header, $row);
// 		}
		
		//Old PHP
		$file = fopen($questionsFile, 'r');
		$questions = array();
		$header = null;
		while ($row = fgetcsv($file)) {
			if ($header === null) {
				$header = $row;
				continue;
			}
			$questions[] = array_combine($header, $row);
		}
		
		//Populate each question in the array with its files
		for($i=0; $i < count($questions); $i++) {
			$rootDir = $questions[$i]['dir'];
		
			$targetFile = "";
			if($questions[$i]['hasTarget'] == 'true') {
				$targetFile = glob($rootDir . "/target/*.wav");	
				$targetFile = $targetFile[0]; //Stupid hack for PHP bug on server
			}

			//Randomise audio files
			$patternFiles = glob($rootDir . "/patterns/*.wav");
			
			shuffle($patternFiles);
			
			$questions[$i]['targetFile'] = $targetFile;
			$questions[$i]['patternFiles'] = $patternFiles;			
		}
		
// 		//User details into session
// 		$_SESSION['name'] = $_POST['name'];
// 		$_SESSION['email'] = $_POST['email'];
		
		//Create the CSV file
// 		$_SESSION['results'] .= "Name, Email, Gender, Age, Listen, Instrument, Percussion, Read\n";	
// 		$_SESSION['results'] .= $_POST['name'] . ',' . $_POST['email'] . ',' . $_POST['gender'] . ',' . $_POST['age'] . ',';
		
		//The user's details
		$_SESSION['results'] .= "Gender, Age, casualListen,focusListen, Instrument, Percussion, Read, Rock_Preference, Electronic_Preference, 'Latin_Preference', 'Complex_Rhythms'\n";	
		$_SESSION['results'] .= $_POST['gender'] . ',' . $_POST['age'] . ',';
		$_SESSION['results'] .= $_POST['casualListen'] . ',' . $_POST['focusListen'] . ',' . $_POST['instrument'] . ',' . $_POST['percussion'] . ',' . $_POST['read'] . ',';
		$_SESSION['results'] .= $_POST['rockPreference'] . ',' . $_POST['electronicPreference'] . ',' . $_POST['latinPreference'] . ',' . $_POST['complexRhythms']  . "\n\n";
		
		//The answers to the questions
		$_SESSION['results'] .= "Question Tag, Question, Target File, Time";

		//How many questions to show per page
		$_SESSION['filesPerPage'] = 5;
		
		for($i=0; $i < $_SESSION['filesPerPage']; $i++)
		{
			$_SESSION['results'] .= ",fileName_" . $i;
			$_SESSION['results'] .= ",fileResponseType_" . $i;
			$_SESSION['results'] .= ",fileResponse_" . $i;
			$_SESSION['results'] .= ",fileResponseType_" . $i;
			$_SESSION['results'] .= ",fileResponse_" . $i;
			$_SESSION['results'] .= ",fileResponseType_" . $i;
			$_SESSION['results'] .= ",fileResponse_" . $i;
		}
		
		$_SESSION['results'] .= "\n";
		
		//Store in session variables
		$_SESSION['questions'] = $questions;
		$_SESSION['questionCounter'] = 0;
		$_SESSION['fileCounter'] = 0;
		$_SESSION['controlCounter'] = 0;		
				
		//Reset the array and redirect to form.php				
		$_POST = array();
		
		
		header('Location: '. "form.php");
	}
	else {
	
		// Unset all of the session variables.
	$_SESSION = array();

	// If it's desired to kill the session, also delete the session cookie.
	// Note: This will destroy the session, and not just the session data!
	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
		);
	}

	// Finally, destroy the session.
	session_destroy();

?>

<!DOCTYPE html>
<html>

<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<!-- <script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.js"></script> -->
<script>
// 	$(document).ready(function(){
// 		$("form[name=myForm]").bind('submit',function(){
// 			if($(".rockRule").val() == $(".latinRule").val() ||
// 				$(".rockRule").val() == $(".electronicRule").val() ||
// 				$(".latinRule").val() == $(".electronicRule").val()) {
// 				alert("Genre preferences must be different");
// 		   		return false;
// 		    } else {
// 		   		return true;
// 		   	}
// 		});
// 	});
</script>
<link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
<!-- Start Form Text -->

	<h1>Rhythmic Pattern Generation Evaluation</h1>
	<p>Thanks for taking the time to take part in this evaluation survey</p>
	<p>For best results we recommend you use the Chrome browser. NOTE: Safari browser works but it doesn't validate options so please use Chrome/Mozilla if possible</p>
	<p>Please use an appropriate listening environment, i.e. reasonably quiet room with adequate speakers and headphones</p>	

<!-- 
	This survey comprises two parts:
	
	<ol>
		<li>
			In this part you will be asked to listen to one drum pattern (the target),then to another one (the generated). You will then be requested to rate the similarity of the generated drum pattern compared to a target pattern.
		</li>
		<li>
			In this part you will be asked to listen to a drum pattern (usually made by interleaving 2 sub patterns as A-B-A-B) then rate its "interestingness". When evaluating the "interestingness" of the generated consider these two aspects:-
			<ul>
				<li>Flow - how the subpatterns alternate. </li>
				<li>Development - how the pattern expands, contrasts and builds</li>
			</ul>
		</li>
    </ol>
 -->
 	
	
 	<h2>Instructions</h2>
	<p>		
		In this survey you will be asked to listen to one loop (the target),then to a series of other loops generated by a computer algorithm using different samples to the target. The generated loops may or may not preserve some similarities with the target.
		<br />
		
		You will then be requested to rate the similarity of the generated loops compared to a target loop in terms of its rhythmic pattern and its timbre as well your subjective impression of the loop.
		
		<br />
		Here are some things to think about when you consider pattern and timbre.
		
		<ul>
			<li> <b>Pattern</b> - The organisation or the arrangement of sounds (any sounds in time). <br />For instance compare a standard rock drum pattern to patterns in latin music. Here's two examples of different patterns with identical timbres 
				<ol>
					<li><audio controls preload="none" src="examples/pattern_1.wav"></audio></li>
					<li><audio controls preload="none" src="examples/pattern_2.wav"></audio></li>
				</ol>			
			</li>

			<li> 
				<b>Timbre</b> - the characteristics of the sounds themselves. 
				<br /> (Wikipedia definition) - In simple terms, timbre is what makes a particular musical sound different from another, even when they have the same pitch and loudness. 
				<br /> For instance here's two examples of different timbres but identical patterns (an electronic drum machine versus acoustic drums).
				<ol>
					<li><audio controls preload="none" src="examples/timbre_1.wav"></audio></li>
					<li><audio controls preload="none" src="examples/timbre_2.wav"></audio></li>
				</ol>			
			</li>				
		</ul>
	</p> 
	
	<br />
		
	<p> You will listen to just over 40 files in total, some pages might have more than 4 files; this is normal. The test will take around 15-20 mins </p>
	
	<p>
		Some other things to note:-
	</p>

	<ul>
		<li> You can listen to the sounds as many times you like.</li> 
		<li> Hit the checkbox to enable looping of sounds, unfortunately there'll be a slight gap due to a HTML5 bug.</li> 
		<li> You are allowed to review your ratings at any time (until you hit "Next")</li>
	</ul>

	<hr />
	
	<form id="myForm" name="myForm" action="start.php" method="post"  onsubmit="">
	
	<h2>Personal Details</h2>
	<h4>(All information will be treated anonymously and confidentially)</h4>

	<table border='0' class="likertTable">
	
		<col width="75px" />
	    
	    <!-- Likert widths -->
    	<col width="75px" />
		<col width="75px" />
	    <col width="75px" />
    	<col width="75px" />
		<col width="75px" />
		
		
		<tr>
			 <td></td>
			 <td>Male</td>
			 <td>Female</td>			 			 
		</tr>
		
		<tr>
			 <td style='text-align:left'>Gender</td>
			 <td><input class="rad" type="radio" name="gender" value="Male" required  /></td>			 
			 <td><input class="rad" type="radio" name="gender" value="Female" required  /></td>
		</tr>
		
				<tr><td><br/></td></tr>	
		
		<tr>
			 <td></td>
			 <td>15-24</td>
			 <td>25-34</td>
			 <td>35-44</td>
			 <td>45-54</td>
			 <td>55-64</td>
			 <td>65+</td>			 
		</tr>
		
		<tr>
			 <td style='text-align:left'>Age</td>
			 <td><input class="rad" type="radio" name="age" value="15-24" required  /></td>
			 <td><input class="rad" type="radio" name="age" value="25-34" required  /></td>
			 <td><input class="rad" type="radio" name="age" value="35-44" required  /></td>
			 <td><input class="rad" type="radio" name="age" value="45-54" required  /></td>
			 <td><input class="rad" type="radio" name="age" value="55-64" required  /></td>
			 <td><input class="rad" type="radio" name="age" value="65+" required  /></td>		 					 			 
		</tr>
		
	</table>
	
	<br />
	<hr>
		<h2>User Details</h2>
	<br />

	<table border='0' class="likertTable">
	
	    <col width="500px" />
	    
	    <!-- Likert widths -->
    	<col width="80px" />
		<col width="80px" />
	    <col width="80px" />
    	<col width="80px" />
		<col width="80px" />
		
		<tr>
			 <td></td>
			 <td>Never</td>  	 
			 <td>Almost Never</td>
			 <td>Sometimes</td>
			 <td>Often</td>
			 <td>Very often</td>			 
		</tr>
		
		<tr>
			 <td style='text-align:left'>How often do you casually listen to music (on in the background or while working)?</td>
			 <td><input type="radio" name="casualListen" value="1" required /></td>
			 <td><input type="radio" name="casualListen" value="2" required /></td>
			 <td><input type="radio" name="casualListen" value="3" required /></td>
			 <td><input type="radio" name="casualListen" value="4" required /></td>
			 <td><input type="radio" name="casualListen" value="5" required /></td>
		</tr>

		<tr><td><br/></td></tr>		
		
		<tr>
			 <td style='text-align:left'>How often do you "focus" listen to music (without distraction)?</td>
			 <td><input type="radio" name="focusListen" value="1" required /></td>
			 <td><input type="radio" name="focusListen" value="2" required /></td>
			 <td><input type="radio" name="focusListen" value="3" required /></td>
			 <td><input type="radio" name="focusListen" value="4" required /></td>
			 <td><input type="radio" name="focusListen" value="5" required /></td>
		</tr>
		
		<tr><td><br/></td></tr>
		<tr><td><br/></td></tr>			
		
		<tr>
			 <td></td>
			 <td>Yes</td>
			 <td>No</td>			 			 
		</tr>
		
		<tr>
			 <td style='text-align:left'>Do you play an instrument?</td>
			 <td><input class="rad" type="radio" name="instrument" value="Yes" required  /></td>			 
			 <td><input class="rad" type="radio" name="instrument" value="No" required  /></td>			 			 
		</tr>
		
		<tr><td><br/></td></tr>
		
		<tr>
			 <td></td>
			 <td>Yes</td>
			 <td>No</td>			 			 
		</tr>
		
		<tr>
			 <td style='text-align:left'>Do you play a percussive instrument like a drum?</td>
			 <td><input class="rad" type="radio" name="percussion" value="Yes" required  /></td>			 
			 <td><input class="rad" type="radio" name="percussion" value="No" required  /></td>			 			 
		</tr>
		
		<tr><td><br/></td></tr>
		
		<tr>
			 <td></td>
			 <td>Yes</td>
			 <td>No</td>			 			 
		</tr>
		
		<tr>
			 <td style='text-align:left'>Do you read music?</td>
			 <td><input class="rad" type="radio" name="read" value="Yes" required  /></td>			 
			 <td><input class="rad" type="radio" name="read" value="No" required  /></td>			 			 
		</tr>


<!-- 				
		<tr><td><br/></td></tr>
		<tr><td><br/></td></tr>				

		<tr>
			 <td></td>
			 <td>Rock</td>
			 <td>Electronic</td>
			 <td>Latin</td>			 
		</tr>
		

		<tr>
			 <td style='text-align:left'>Rate these genres in terms of preference from 1 to 3 (with 1 being the most preferred).</td>
			 
    		 <td>
				 <select name="rockPreference" class="rockRule">
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
				</select>			 				 
			 </td> 
			 
			 <td>
				 <select name="electronicPreference" class="electronicRule">
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
				</select>			 				 
			 </td> 

			 <td>
				 <select name="latinPreference" class="latinRule">
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
				</select>			 				 
			 </td>
			 
			<tr><td><br/></td></tr>	
			<tr><td><br/></td></tr>				 
			 
		<tr>
			 <td></td>
			 <td>Strongly dislike</td>  	 
			 <td>Dislike</td>
			 <td>Neutral</td>
			 <td>Like</td>
			 <td>Strongly like</td>			 
		</tr>
		
		<tr>
			 <td style='text-align:left'>In general, do you like or dislike music with complex, intricate rhythms?</td>
			 <td><input type="radio" name="complexRhythms" value="1" required /></td>
			 <td><input type="radio" name="complexRhythms" value="2" required /></td>
			 <td><input type="radio" name="complexRhythms" value="3" required /></td>
			 <td><input type="radio" name="complexRhythms" value="4" required /></td>
			 <td><input type="radio" name="complexRhythms" value="5" required /></td>
		</tr>
 -->

		<tr><td><br/></td></tr>	
			 
		<td>
			 
		</td>
			 
		</tr>
		
		</table>
		
		<hr />
		<br />
		
		<table>
			<tr>
				<td>
					<input type="submit" value="Start" style="font-size: 25px;" >
				</td>
			</tr>
		</table>
	</form>
	<script>
// 		$("#myForm").validate({
// 			errorPlacement: function(error, element) {
// // 		    	error.appendTo(element.parent("td"));
// 		    	error.appendTo(element.parent("td"));
// 		  	}
// 		});
	</script>
</body>
</html>
<?php 
}
ob_end_flush();
?>