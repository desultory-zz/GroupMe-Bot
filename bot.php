<?php
//Includes
include 'config.php';
include 'admins.php';
include 'responses.php';
//The following lines get the message info from groupme's post to this url and put them into variables
$data = json_decode(file_get_contents('php://input'));
$name = $data->name;
$text = $data->text;
$sender_type = $data->sender_type;
$sender_id = $data->sender_id;
//Logging
if ($log == 1) {
	//Checks to make sure logging dir exists
        if (!is_dir($logdir)) {
		//Makes the logging dir if it doesn't exist and sets it to the specified chmod
                mkdir($logdir, $logdirchmod);
        }
	//Puts the message in log file in format "Sender ID(Name) : Message"
	file_put_contents($logdir.$logfile, "$sender_id($name) : $text \n", FILE_APPEND | LOCK_EX);
}
//Checks to see if the type is a user and only handles messages from users to prevent infinite loops, also doesn't respond to commands
if ($sender_type == "user" && $text[0] !== "/") {
	basic_response($text, $responses);
	//Checks to see if text contains weather and WU variables are set before calling the weather reaponse fucntion
	if (stripos($text, "weather") !== FALSE) {
		if (isset($wutoken) && isset($wulocation)) {
			weather_response($wutoken, $wulocation);
		}
	}
}
//commands function, first checks to see if a message contains a / at the beginning
if ($text[0] == "/" && in_array($sender_id,$admins)) {
	//Initialezes message as null
	$message = null;
	//Splits the input contained in quotes into elements of array "commands"
	preg_match_all('`"([^"]*)"`', escapeshellcmd($text), $command);
	if ($text == "/help") {
		//Help message
		$message = '
		/responses lists current respones
		/addresponse makes a new response in the format "/addresponse "phrase" "response""
		/delresponse deletes a reaponse in the format "/delresponse "phrase""
		/editresponse edits a response in the format "/editresponse "phrase" "response""
		/admins lists current admins by user id
		/addadmin adds an admin in the format "/addadmin "userid""
		/deladmin delets an admin in the format "/deladmin "userid""
		';
	} else if ($text == "/responses") {
		//Goes through the array of responses and puts each set on one line
		foreach($responses as $val) {
			$message .= "$val[0] -> $val[1]\n";
		}
	} else if (strpos($text, '/addresponse') !== FALSE) {
		//Checks to see if the arguments are set correctly before continuing
		if (isset($command[1][0]) && $command[1][0] !== "" && isset($command[1][1]) && $command[1][1] !== "" && !isset($command[1][2])) {
			//Checks to see if that response is already in the responses file first
			if (search_array($command[1][0], $responses)) {
				$message = 'There is already a response for that phrase or a response that contains that phrase';
			} else {
				//Creates and formats the new response
				$newresponse = '['.$command[0][0].', '.$command[0][1].'],';
				//Adds the new response to the responses file
				file_put_contents('responses.php',str_replace('$responses = [', "\$responses = [\n\t$newresponse", file_get_contents('responses.php')));
				$message = 'Added response '.$command[0][0].' -> '.$command[0][1];
			}
		} else {
			$message = 'Invalid input';
		}
	} else if (strpos($text, '/delresponse') !== FALSE) {
		//Checks to see if the arguements are set correctly
		if (isset($command[1][0]) && $command[1][0] !== "" && !isset($command[1][1])) {
			//Checks to see if that response is already in the responses file first
			if (search_array($command[1][0], $responses)) {
				//loads current responses by reading the responses.php file
				$currentresponses = file('responses.php');
				//Reads each line of the responses file (now in array currentresponses)
				foreach ($currentresponses as $linenumber => $line) {
					//Checks to see if the line contains the response that is being deleted
					if (strpos($line, $command[0][0]) !== FALSE) {
						//Once that response is found, loads the contents of the responses file into new variable newresponses
						$newresponses = file_get_contents('responses.php');
						//Deletes appropriate line
						$newresponses = str_replace($line, '', $newresponses);
						//Writes the modified version back to the responses.php file
						file_put_contents('responses.php', $newresponses);
						$message = 'Deleted response for '.$command[0][0];
					}
				}
			} else {
				$message = 'There is no response for the phrase '.$command[0][0].', nothing to delete';
			}
		} else {
			$message = 'Invalid input';
		}
	} else if (strpos($text, '/editresponse') !== FALSE) {
		//Checks to see if the arguments are set correctly
		if (isset($command[1][0]) && $command[1][0] !== "" && isset($command[1][1]) && $command[1][1] !== "" && !isset($command[1][2])) {
			//Checks to see if that response is already in the responses file first
			if (search_array($command[1][0], $responses)) {
				//If the response is found in the responses file, reads it into array currentresponses
				$currentresponses = file('responses.php');
				//Reads responses.php file line by line
				foreach ($currentresponses as $linenumber => $line) {
					//Finds the line containing the response to be modified
					if (strpos($line, $command[0][0]) !== FALSE) {
						//Once the response is found, loads the contents of the responses file into new variable newresponses
						$newresponses = file_get_contents('responses.php');
						//Replaces the line with a new line containing the appropriate response information
						$newresponses = str_replace($line, "\t[".$command[0][0].', '.$command[0][1]."],\n", $newresponses);
						//Writes the modified version back to the responses.php file
						file_put_contents('responses.php', $newresponses);
						$message = 'Edited response '.$command[0][0].' -> '.$command[0][1];
					}
				}
			} else {
				$message = 'That phrase does not have a response to edit';
			}
		} else {
			$message = 'Invalid input';
		}
	} else if ($text == "/admins") {
		//Goes through the array of admins and puts each user id on one line
		foreach($admins as $val) {
			$message .= "$val\n";
		}
	} else if (strpos($text, '/addadmin') !== FALSE) {
		//Checks to see if the arguments are set correctly before continuing
		if (isset($command[1][0]) && $command[1][0] !== "" && !isset($command[1][1])) {
			//Checks to see if that user is already in the admins file first
			if (in_array($command[1][0], $admins)) {
				$message = 'That user is already an admin';
			} else {
				//Adds the new user id to admin file
				file_put_contents('admins.php',str_replace('$admins = [', "\$admins = [\n\t".$command[0][0].', ', file_get_contents('admins.php')));
				$message = 'Added admin with user id '.$command[1][0].' to the admins file';
			}
		} else {
			$message = 'Invalid input';
		}
	} else if (strpos($text, '/deladmin') !== FALSE) {
		//Checks to see if the arguements are set correctly
		if (isset($command[1][0]) && $command[1][0] !== "" && !isset($command[1][1])) {
			//Checks to see if that user id is an admin
			if (in_array($command[1][0], $admins)) {
				//loads current admins by reading the admins.php file
				$currentadmins = file('admins.php');
				//Reads each line of the admins file (now in array currentadmins)
				foreach ($currentadmins as $linenumber => $line) {
					//Checks to see if the line contains the user id that is being deleted
					if (strpos($line, $command[0][0]) !== FALSE) {
						//Once that user id is found, loads the contents of the admins file into new variable newadmins
						$newadmins = file_get_contents('admins.php');
						//Deletes appropriate line
						$newadmins = str_replace($line, '', $newadmins);
						//Writes the modified version back to the admin.php file
						file_put_contents('admins.php', $newadmins);
						$message = 'Removed admin with user id '.$command[0][0];
					}
				}
			} else {
				$message = 'User '.$command[0][0].' is not an admin, nothing to delete';
			}
		} else {
			$message = 'Invalid input';
		}
	} else {
		$message = 'Command not found';
	}
	send($message, null, null);
}



//Send function, token should be the groupme bot token
//Message is the message you want to send
//attachments should be the attachments you want to send, this should be a string if it's a url and an array where the 0 element is the user id and the 1 element is the location of the username in format [start,end]
//attachmenttype should be 'image' or 'mentions
function send($message, $attachments, $attachmenttype) {
	include 'config.php';
	//Sets the basic postdata arguments
	$postdata = [
		'bot_id' => $token,
		'text' => $message,
	];
	//This is only run if there are attachments
	if (isset($attachments)) {
		//Sets the attachment type to image and sets the url to the $attachments argument
		if ($attachmenttype == 'image') {
			$attachments = [
				'type' => 'image',
				'url' => $attachments
			];
		} else if ($attachmenttype == 'mentions') {
			//Sets the attachments type to mentions and adds the user id's and location
			$attachments = [
				'type' => 'mentions',
				'user_ids' => $attachments[0],
				'loci' => attachments[1]
			];
		}
		//Adds the attachments element to the postdata and then adds the attachments to that element
		$postdata['attachments'] = [$attachments];
	}
	//Encodes the postdata in json format then adds single quotes around it
	$postdata = escapeshellarg(json_encode($postdata));
	`curl -s -X POST -d $postdata -H 'Content-Type: application/json' https://api.groupme.com/v3/bots/post`;
}
//This function is used for the commands ability, it is designed to simply read the first element of each element in an array and check to see if that is equal to the supplied needle
function search_array($needle, $haystack) {
	$exists = 0;
	foreach ($haystack as $element) {
		if (stripos($needle, $element[0]) !== FALSE) {
			$exists = 1;
		}
	}
	return $exists;
}
//Basic response function
function basic_response($text, $responses) {
	$element = 0;
	//Goes through responses array
	foreach ($responses as $catch) {
		//Finds if a phrase you are looking for is in the text to a message
		if (stripos($text, $catch[0]) !== FALSE) {
			//If a phrase you have set is found, this sets the message to be the appropriate response
			$message = $responses[$element][1];
			send($message, null, null);
		}
		$element++;
	}
}
//Weather function
function weather_response($token, $location) {
	//Gets the conditions for your area
	$rawweather = json_decode(`curl -s http://api.wunderground.com/api/$token/conditions/q/$location.json`);
	//makes variables for condition information
	$temperature = $rawweather->current_observation->feelslike_string;
	$weather = $rawweather->current_observation->weather;
	$icon = $rawweather->current_observation->icon_url;
	//Compiles this information into a post for the forecast
	$forecast = "The weather is $weather with a temperature of $temperature";
	//Encodes the forecast into json and adds the token and the weather icon
	send($forecast, $icon, 'image');
}
