<?php
	session_start();
	
	//Turn on and off to provide default radio button selection
	$defaultsOn = true;
	
	//--------------------------------------------------------------------
	//Here we populate the form with the question details
	
	$questionCounter = $_SESSION['questionCounter'];
	$question = $_SESSION['questions'][$questionCounter];
		
	$folder = $question['dir'];

	$descriptions[0] = $question['questionType'];	
	$descriptions[1] = "Highly " . $question['likertLow'];
	$descriptions[2] = $question['likertLow'];
	$descriptions[3] = "Neutral";
	$descriptions[4] = $question['likertMax'];
	$descriptions[5] = "Highly " . $question['likertMax'];
	
	$color = $question['questionColour'];
	$questionTag = $question['questionTag'];
	$questionText = $question['questionText'];
	$targetFile = $question['targetFile'];
	$patternFiles = $question['patternFiles'];
	
	$totalPatternFiles = count($patternFiles);
	$filesPerPage = $_SESSION['filesPerPage'];
				
	//--------------------------------------------------------------------
	//Here we handle the form
	if($_POST)
	{		
		$resultFiles = $_POST['sound'];
		
		$endTime = microtime(true);
		$time = $endTime - $_SESSION['startTime'];
		unset($_SESSION['startTime']);
		
		$resultsOut = "$questionTag, $questionText, $targetFile, $time,";
		
		foreach ($resultFiles as $resultFilesKey => $resultFilesValue)
		{	
			$resultsOut .= $resultFilesKey . ",";
			foreach ($resultFilesValue as $key => $value) 
			{
				$resultsOut .= $key . ",";
				
				foreach($value as $response)
				{
					$resultsOut .= $response . ",";
				}
			}
		}
		
		$_SESSION['results'] .= $resultsOut ."\n";
		
		$_POST = array();


		if($_SESSION['fileCounter'] < count($patternFiles)) {
			header('Location: '. "form.php");
		} else {
			$_SESSION['questionCounter']++;
			$_SESSION['fileCounter'] = 0;
			$_SESSION['results'] .= "\n";
			
			if($_SESSION['questionCounter'] == count($_SESSION['questions']))
				header('Location: '. "finish.php");
			else
				header('Location: ' . "form.php");
		}
		
	} else {
	//--------------------------------------------------------------------
	//Start timer and show the form
	if(!$_SESSION['startTime']) {
		$_SESSION['startTime'] = microtime(true);
	}
?>
<!DOCTYPE html>
<html>

<head>
<link rel="stylesheet" type="text/css" href="style.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.js"></script>
<script>
//Use javascript to get a random colour
	$(document).ready(function(){
// 		var colors = ['#993333', '#339933', '#FF9933', '#3399CC'];
// 		
// 		var lastColor = localStorage.getItem("lastColor");
// 		
// 		var random_color = colors[Math.floor(Math.random() * colors.length)];
// 				
// 		while(random_color == lastColor)
// 			random_color = colors[Math.floor(Math.random() * colors.length)];
// 		
// 		$('#question').css('color', random_color);
// 		localStorage.setItem("lastColor", random_color);
		$("audio").on("play", function(){
			var _this = $(this);
			$("audio").each(function(i,el){
				if(!$(el).is(_this))
					$(el).get(0).pause();
			});
		});
	});

	$('myForm').submit(function(){
		$('input[type=submit]', this).attr('disabled', 'disabled');
	});

	function loopChecked() {
		$("audio").attr("loop",document.getElementById("loopCheck").checked);
	}
</script>

</head>

<body>
	<form name="myForm" action="form.php" method="post"  onsubmit="">
	<table class="likertTable">
	

		
		<col width="15px" />
	    <col width="100px" />
	    
	    <!-- Likert widths -->
    	<col width="75px" />
		<col width="75px" />
	    <col width="75px" />
    	<col width="75px" />
		<col width="75px" />

		<!-- Question Text -->    	
		<tr><td><br/></td></tr>		
		<tr>
			 <td style="width:125px;font-weight:bold;">
			 Question <?php echo ($_SESSION['questionCounter'] + 1);?>
			 <br /> <?php
// 				echo "Page: " . (($_SESSION['fileCounter']/$_SESSION['filesPerPage']) + 1) . '/' . ($totalPatternFiles/$_SESSION['filesPerPage']);
// 					echo "Page: " . (($_SESSION['questionCounter'] + 1) . '/' . 2);
					?>
			 </td>
			 <td colspan='5' id='question' style='font-weight:bold; color: <?php echo $color ?> '><?php echo $questionText; ?></td>			 
		</tr>
		<tr><td><br/></td></tr>		
		<tr><td><br/></td></tr>
		
		<?php
		
			//Populate target file if it exists
			if($question['hasTarget'] == 'true') {
		?>
				<tr>
					 <td>Target Sound:</td>
					 <td style="float:left"><audio controls id="a1" preload="auto" src=<?php echo "\"$targetFile\""; ?>></audio></td>
					 <td colspan=5 style="text-align:left"> Loop Sound: <input type="checkbox" id="loopCheck" onclick="loopChecked()">   (expect a slight gap due to HTML bug)</td>
				</tr>
				<tr><td><br/></td></tr>   
				<tr><td><br/></td></tr>
		<?php
			}
		?>
		
		<!-- Likert Descriptions -->
		<tr>
			 <td></td>
			 <td>Generated Sound</td>  	 
			 <?php
			 	for($i=0; $i<6; $i++)
			 	{
					echo "<td>$descriptions[$i]</td>";			 		
			 	}
			 ?>
		</tr>
		
		<?php
		//Determine how many files we process per page
		$pageCounterMax = $_SESSION['fileCounter'];
		
		if(($totalPatternFiles - $_SESSION['fileCounter']) < $filesPerPage)
			$pageCounterMax += $totalPatternFiles - $_SESSION['fileCounter'];
		else
			$pageCounterMax += $filesPerPage;
			
		//Populate the form with the audio files			
		for(;$_SESSION['fileCounter'] < $pageCounterMax; $_SESSION['fileCounter']++) {
			$num++;
			$file = $patternFiles[$_SESSION['fileCounter']];
			
			$isControl = false;
			
			if (strpos($file, '_control') !== false)
			{
				$isControl = true;
			}
			
			if($isControl)
			{				
				if($_SESSION['controlCounter'] == 0)
				{
					?>
					<tr>
						<td><?php echo $num ?></td>
						<td style="float:left"><audio controls id="a1" preload="none" src=<?php echo "$file"; ?>></audio></td>
						 <td style="white-space: nowrap;text-align:left">The pattern is similar?</td>
						 <td><input class="rad" id="radGuiltyStart" type="radio" name=<?php echo "\"sound[$file]['pattern'][]\""; ?> value="1" required /></td>
						 <td><input class="rad" type="radio" name=<?php echo "\"sound[$file]['pattern'][]\""; ?> value="2" required /></td>
						 <td><input class="rad" type="radio" name=<?php echo "\"sound[$file]['pattern'][]\"";
							if ($defaultsOn == true) {
								echo "checked=\"checked\"";
							}
						  ?> value="3" required /></td>
						 <td><input class="rad" type="radio" name=<?php echo "\"sound[$file]['pattern'][]\""; ?> value="4" required  /></td>
						 <td><input class="rad" id="radGuiltyEnd" type="radio" name=<?php echo "\"sound[$file]['pattern'][]\""; ?> value="5" required  /></td>
						 <input type="hidden" name="filename" value <?php echo "$file"; ?> />
					</tr>
					<?php
				}
				else if($_SESSION['controlCounter'] == 1)
				{
				?>
					<tr>
						<td><?php echo $num ?></td>
						<td style="float:left"><audio controls id="a1" preload="none" src=<?php echo "$file"; ?>></audio></td>
						 <td style="text-align:left">The timbre is similar?</td>
						 <td><input class="rad" id="radGuiltyStart" type="radio" name=<?php echo "\"sound[$file]['timbre'][]\""; ?> value="1" required /></td>
						 <td><input class="rad" type="radio" name=<?php echo "\"sound[$file]['timbre'][]\""; ?> value="2" required /></td>
						 <td><input class="rad" type="radio" name=<?php echo "\"sound[$file]['timbre'][]\"";
							if ($defaultsOn == true) {
								echo "checked=\"checked\"";
							}
						  ?> value="3" required /></td>
						 <td><input class="rad" type="radio" name=<?php echo "\"sound[$file]['timbre'][]\""; ?> value="4" required  /></td>
						 <td><input class="rad" id="radGuiltyEnd" type="radio" name=<?php echo "\"sound[$file]['timbre'][]\""; ?> value="5" required  /></td>
						 <input type="hidden" name="filename" value <?php echo "$file"; ?> />
					</tr>
					<tr></tr>
					<tr></tr>
				<?php
				}
				else if($_SESSION['controlCounter'] == 2)
				{
				?>			
					<tr>
						<td><?php echo $num ?></td>
						<td style="float:left"><audio controls id="a1" preload="none" src=<?php echo "$file"; ?>></audio></td>
						 <td style="text-align:left">Did you like the loop?</td>
						 <td><input class="rad" id="radGuiltyStart" type="radio" name=<?php echo "\"sound[$file]['like'][]\""; ?> value="1" required /></td>
						 <td><input class="rad" type="radio" name=<?php echo "\"sound[$file]['like'][]\""; ?> value="2" required /></td>
						 <td><input class="rad" type="radio" name=<?php echo "\"sound[$file]['like'][]\"";
							if ($defaultsOn == true) {
								echo "checked=\"checked\"";
							}
						  ?> value="3" required /></td>
						 <td><input class="rad" type="radio" name=<?php echo "\"sound[$file]['like'][]\""; ?> value="4" required  /></td>
						 <td><input class="rad" id="radGuiltyEnd" type="radio" name=<?php echo "\"sound[$file]['like'][]\""; ?> value="5" required  /></td>
						 <input type="hidden" name="filename" value <?php echo "$file"; ?> />
					</tr>					
					<tr><td><br /></td></tr>
				<?php
				}
				$_SESSION['controlCounter']++;
			}
			else
			{
				?>
				<tr>
					<td><?php echo $num ?></td>
					<td style="float:left"><audio controls id="a1" preload="none" src=<?php echo "$file"; ?>></audio></td>
					 <td style="white-space: nowrap;text-align:left">The pattern is similar?</td>
					 <td><input class="rad" id="radGuiltyStart" type="radio" name=<?php echo "\"sound[$file]['pattern'][]\""; ?> value="1" required /></td>
					 <td><input class="rad" type="radio" name=<?php echo "\"sound[$file]['pattern'][]\""; ?> value="2" required /></td>
					 <td><input class="rad" type="radio" name=<?php echo "\"sound[$file]['pattern'][]\"";
						if ($defaultsOn == true) {
							echo "checked=\"checked\"";
						}
					  ?> value="3" required /></td>
					 <td><input class="rad" type="radio" name=<?php echo "\"sound[$file]['pattern'][]\""; ?> value="4" required  /></td>
					 <td><input class="rad" id="radGuiltyEnd" type="radio" name=<?php echo "\"sound[$file]['pattern'][]\""; ?> value="5" required  /></td>
					 <input type="hidden" name="filename" value <?php echo "$file"; ?> />
				</tr>
				<tr>
					<td></td>
					<td></td>
					 <td style="text-align:left">The timbre is similar?</td>
					 <td><input class="rad" id="radGuiltyStart" type="radio" name=<?php echo "\"sound[$file]['timbre'][]\""; ?> value="1" required /></td>
					 <td><input class="rad" type="radio" name=<?php echo "\"sound[$file]['timbre'][]\""; ?> value="2" required /></td>
					 <td><input class="rad" type="radio" name=<?php echo "\"sound[$file]['timbre'][]\"";
						if ($defaultsOn == true) {
							echo "checked=\"checked\"";
						}
					  ?> value="3" required /></td>
					 <td><input class="rad" type="radio" name=<?php echo "\"sound[$file]['timbre'][]\""; ?> value="4" required  /></td>
					 <td><input class="rad" id="radGuiltyEnd" type="radio" name=<?php echo "\"sound[$file]['timbre'][]\""; ?> value="5" required  /></td>
					 <input type="hidden" name="filename" value <?php echo "$file"; ?> />
				</tr>
				<tr></tr>
				<tr></tr>			
				<tr>
					<td></td>
					<td></td>
					 <td style="text-align:left">Did you like the loop?</td>
					 <td><input class="rad" id="radGuiltyStart" type="radio" name=<?php echo "\"sound[$file]['like'][]\""; ?> value="1" required /></td>
					 <td><input class="rad" type="radio" name=<?php echo "\"sound[$file]['like'][]\""; ?> value="2" required /></td>
					 <td><input class="rad" type="radio" name=<?php echo "\"sound[$file]['like'][]\"";
						if ($defaultsOn == true) {
							echo "checked=\"checked\"";
						}
					  ?> value="3" required /></td>
					 <td><input class="rad" type="radio" name=<?php echo "\"sound[$file]['like'][]\""; ?> value="4" required  /></td>
					 <td><input class="rad" id="radGuiltyEnd" type="radio" name=<?php echo "\"sound[$file]['like'][]\""; ?> value="5" required  /></td>
					 <input type="hidden" name="filename" value <?php echo "$file"; ?> />
				</tr>
				<tr><td><br /></td></tr>
			<?php
			}
		}
		?>
		
		<input type="hidden" name="noOfSounds" value=<?php echo "\"$num\""; ?> />	
		<tr><td><br/></td></tr>        	
		<tr>
			<td>
				<input type="submit" value="Next">
			</td>

			<td colspan='6'>
				<?php
// 					echo "Sound: " . $_SESSION['fileCounter'] . '/' . $totalPatternFiles;
					echo "  Question: " . ($_SESSION['questionCounter'] + 1) . '/' . count($_SESSION['questions']);
				?>
			</td>
					
		</tr>
		
	</table>	
	</form>
	
		<br />
	
	<!-- Dirty hack for instructions for part 2 	 -->

	
</body>
</html>
<?php
}
?>