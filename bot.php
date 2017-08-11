<?php
//Set this to the token/bot ID of your bot, ex 1252346142346243 or whatever your code is
$token = "";
//Set this to your Weather Underground API token, ex: 42362362362 or whatever your code is
$wunderground = "";
//Wunderground location string in format "STATEABBREVIATION/CITY_NAME"
$location = "";
//Set this to 1 to make a chat log under specified dir
$log = 1;
//Name for log file
$logfile = "log";
//Directory for logs, add slash at beginning for absolute path, there must be a slash at the end
$logdir = "logs/";
//chmod settings for dir
$logdirchmod = 0777;
//The following lines get the message info from groupme's post to this url and put them into variables
$data = json_decode(file_get_contents('php://input'));
$name = $data->name;
$text = $data->text;
$type = $data->sender_type;
$id = $data->sender_id;
//Responses in the format of "array('phrase to look for', "response phrase"),
$responses = array(
	array('test', "It works!"),
	array('abc', "123")
);
//Checks to see if the type is a user and only handles messages from users to prevent infinite loops
if ($type == "user" ) {
	//Basic response function
	$element = 0;
	//Goes through responses array
	foreach ($responses as $catch) {
		//Finds if a phrase you are looking for is in the text to a message
		if (stripos($text, $catch[0]) !== FALSE) {
			//If a phrase you have set is found, this sets the message to be the appropriate response
			//Makes the appropriate post data in json format for groupme
			$postdata = escapeshellarg(json_encode(array(
				'bot_id' => $token,
				'text' => $responses[$element][1]
			)));
			`curl -s -X POST -d $postdata -H 'Content-Type: application/json' https://api.groupme.com/v3/bots/post`;
		}
		//Increments element
		$element++;
	}
	//Weather function
	if (stripos($text, "weather") !== FALSE) {
        	//Gets the conditions for your area
		$rawweather = json_decode(`curl -s http://api.wunderground.com/api/$wunderground/conditions/q/$location.json`);
		//makes variables for condition information
		$temp = $rawweather->current_observation->feelslike_string;
		$weather = $rawweather->current_observation->weather;
		$icon = $rawweather->current_observation->icon_url;
		//Compiles this information into a post for the forecast
		$forecast = "The weather is $weather with a temperature of $temp";
		//Encodes the forecast into json and adds the token and the weather icon
		$postdata = escapeshellarg(json_encode(array(
			'bot_id' => $token,
			'text' => $forecast,
			'attachments' => [ array(
				'type' => 'image',
				'url' => $icon
			)]
		)));
		`curl -s -X POST -d $postdata -H 'Content-Type: application/json' https://api.groupme.com/v3/bots/post`;
	}
}
//Logging function
if ($log == 1) {
	if (is_dir($logdir)) {
		file_put_contents($logdir.$logfile, "$name : $text \n", FILE_APPEND | LOCK_EX);
	} else {
		mkdir($logdir, $logdirchmod);
	}
}
