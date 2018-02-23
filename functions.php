<?php
//Writes the contents of a variable to a text file for debugging purposes
function debugvar($variable) {
	file_put_contents('debug.txt', print_r($variable, true));
}
//logs all chat to specified log file
function logging($userid, $name, $text) {
	include 'config.php';
	if ($log) {
		if (!is_dir($logdir)) {
			mkdir($logdir, $logdirchmod);
		}
		file_put_contents($logdir . '/' . $logfile, "$userid($name): $text\n", FILE_APPEND);
	}
}
//Basic response (no images)
function basic_response($text, $name, $userid) {
	$responses = read_array('responses.php');
	foreach ($responses as $element) {
		if (stripos($text, $element[0]) !== FALSE) {
			$message = $element[1];
			$message = str_replace('%u', $userid, $message);
			if (stripos($message, '%n') !== FALSE) {
				$message = str_replace('%n', $name, $message);
				mention($message, $name);
			} else {
				send($message);
			}
		}
	}
}
//WUnderground response
function weather_response($text) {
	include 'config.php';
	if (stripos($text, 'weather') !== FALSE) {
		if (isset($wutoken) && isset($wuloc)) {
			$rawweather = json_decode(curl_get("https://api.wunderground.com/api/$wutoken/conditions/q/$wuloc.json"));
			$temperature = $rawweather->current_observation->feelslike_string;
			$weather = $rawweather->current_observation->weather;
			$icon = $rawweather->current_observation->icon_url;
			$forecast = "The weather is $weather with a temperature of $temperature";
			send_img($forecast, $icon);
		} else {
			send('WUnderground token and location are not set');
		}
	}
}
//Bitcoin value response
function btc_response($text) {
	if (stripos($text, 'bitcoin') !== FALSE) {
		$pricedata = json_decode(curl_get("https://min-api.cryptocompare.com/data/price?fsym=BTC&tsyms=USD"));
		$usdprice = $pricedata->USD;
		$message = "Bitcoin is worth \$$usdprice";
		$btclogo = 'https://files.coinmarketcap.com/static/img/coins/32x32/bitcoin.png';
		send_img($message, $btclogo);
	}
}
//Ethereum value response
function eth_response($text) {
	if (stripos($text, 'ethereum') !== FALSE) {
		$pricedata = json_decode(curl_get("https://min-api.cryptocompare.com/data/price?fsym=ETH&tsyms=BTC,USD"));
		$usdprice = $pricedata->USD;
		$btcprice = $pricedata->BTC;
		$message = "Ethereum is worth \$$usdprice and $btcprice Bitcoin";
		$ethlogo = 'https://files.coinmarketcap.com/static/img/coins/32x32/ethereum.png';
		send_img($message, $ethlogo);
	}
}
//Litecoin value response
function ltc_response($text) {
	if (stripos($text, 'litecoin') !== FALSE) {
		$pricedata = json_decode(curl_get("https://min-api.cryptocompare.com/data/price?fsym=LTC&tsyms=BTC,USD"));
		$usdprice = $pricedata->USD;
		$btcprice = $pricedata->BTC;
		$message = "Litecoin is worth \$$usdprice and $btcprice Bitcoin";
		$ltclogo = 'https://files.coinmarketcap.com/static/img/coins/32x32/litecoin.png';
		send_img($message, $ltclogo);
	}
}
//Curl get function, takes url and returns the get response
function curl_get($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "$url");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$get = curl_exec($ch);
	curl_close($ch);
	return $get;
}
//Curl post to groupme, takes the postfields and posts to the groupme bot api
function curl_post($postfields) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://api.groupme.com/v3/bots/post');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
	curl_exec($ch);
	curl_close($ch);
}
//Send message function, takes a message as input and posts to GroupMe
function send($message) {
	include 'config.php';
	$postdata = [
		'bot_id' => $bottoken,
		'text' => $message
	];
	curl_post(http_build_query($postdata));
}
//Send message function, takes a message as input and posts to GroupMe
function send_configurl($userid) {
	include 'config.php';
	$dm = [
		'source_guid' => uniqid(),
		'recipient_id' => $userid,
		'text' => "asdf"
	];
	$postdata = [
		'direct_message' => $dm
	];
	$postfields = json_encode($postdata);
	send($postfields);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://api.groupme.com/v3/direct_messages?token=$apitoken");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	curl_close($ch);
}
//Send image function, takes message and img url as inputs and posts to GroupMe
function send_img($message, $image) {
	include 'config.php';
	$attachments = [
		'type' => 'image',
		'url' => $image
	];
	$postdata = [
		'bot_id' => $bottoken,
		'text' => $message,
		'attachments' => [$attachments]
	];
	curl_post(json_encode($postdata));
}
//Mention function, takes a message and name as inputs and posts to GroupMe
function mention($message, $name) {
	include 'config.php';
	$loci = [
		stripos($message, $name),
		strlen($name)
	];
	$attachments = [
		'loci' => [$loci],
		'type' => 'mentions',
		'user_ids' => [get_user_id($name)]
	];
	$postdata = [
		'bot_id' => $bottoken,
		'text' => $message,
		'attachments' => [$attachments]
	];
	curl_post(json_encode($postdata));
}
//Store array function, takes an array and file as input and stores in a format that can later be loaded using read_array
function store_array($array, $file) {
	$array = json_encode($array);
	file_put_contents($file, "<?php\n" . $array);
}
//Read array function, takes a file saved with store_array as input and can be assigned to a variable
function read_array($file) {
	$array = file_get_contents($file);
	$array = str_replace('<?php', null, $array);
	$array = json_decode($array, true);
	return $array;
}
//Get bot group function, returns the group id of the bot
function get_bot_group() {
	include 'config.php';
	$bots = json_decode(curl_get("https://api.groupme.com/v3/bots?token=$apitoken"));
	foreach($bots->response as $element) {
		if ($element->bot_id == $bottoken) {
			return $element->group_id;
		}
	}
}
//Get user id function, takes a name as input and returns the user id
function get_user_id($name) {
	include 'config.php';
	$user_id = 'No member with that name found';
	$groupid = get_bot_group();
	$groups = json_decode(curl_get("https://api.groupme.com/v3/groups?token=$apitoken"));
	foreach($groups->response as $element) {
		if ($element->id == $groupid) {
			foreach($element->members as $member) {
				if (stripos($member->nickname, $name) !== FALSE) {
					$user_id = $member->user_id;
				}
			}
		}
	}
	return $user_id;
}
//Get name function, takes a user id as input and returns the name associated with that user id
function get_name($userid) {
	include 'config.php';
	$name = 'Invalid userid';
	$groupid = get_bot_group();
	$groups = json_decode(curl_get("https://api.groupme.com/v3/groups?token=$apitoken"));
	foreach($groups->response as $element) {
		if ($element->id == $groupid) {
			foreach($element->members as $member) {
				if ($userid == $member->user_id) {
					$name = $member->nickname;
				}
			}
		}
	}
	return $name;
}
//Add response fucntion, adds a response from the find and response arguments passed
function add_response($find, $response) {
	$responses = read_array('responses.php');
	$counter = 0;
	$position = false;
	foreach($responses as $element) {
		if (stripos($element[0], $find) !== FALSE || stripos($find, $element[0]) !== FALSE) {
			$position = $counter;
		}
	$counter++;
	}
	if ($position) {
		echo "<h2>Response already exists</h2><br><br>";
	} else {
		$responses[count($responses)] = [$find, $response];
		store_array($responses, 'responses.php');
	}
}
//Delete response by array number function, deletes responses by array index specified
function del_responses($delete) {
	$responses = read_array('responses.php');
	foreach ($delete as $element) {
		$responses[$element] = null;
	}
	$responses = array_values(array_filter($responses));
	store_array($responses, 'responses.php');
	echo "<b>Responses updated</b><br>";
}
//Update setting function, takes an array of setting values as an input and sets all settings appropriately
function update_settings($update) {
	$settings = read_array('settings.php');
	foreach ($settings as $key=>$value) {
		if (isset($update[$key])) {
			$settings[$key] = 1;
		} else {
			$settings[$key] = 0;
		}
	}
	store_array($settings, 'settings.php');
}
//Deletes asetting by number function, delete settings specified by an array of indexes
function del_setting_bynum($delete) {
	$settings = read_array('settings.php');
	foreach ($settings as $key=>$value) {
		if (isset($delete[$key])) {
			unset($settings[$key]);
		}
	}
	store_array($settings, 'settings.php');
}
//Adds a setting by name and enables it by default
function add_setting($setting) {
	$settings = read_array('settings.php');
	if (! isset($settings[$setting])) {
		$settings[$setting] = 1;
		store_array($settings, 'settings.php');
	} else {
		echo "<b>Setting already exists</b><br>";
	}
}
//Get users function, returns an array containing all users and their user ids
function get_users() {
	include 'config.php';
	$groupid = get_bot_group();
	$groups = json_decode(curl_get("https://api.groupme.com/v3/groups?token=$apitoken"));
	$index = 0;
	foreach($groups->response as $element) {
		if ($element->id == $groupid) {
			foreach($element->members as $member) {
				$members[$index] = [
					"userid" => $member->user_id,
					"name" => $member->nickname,
					"avatar" => $member->image_url
				];
			$index++;
			}
		}
	}
	return $members;
}
//Update admins function, takes an array of admin ids as an input and rewrites the admins to only include them
function update_admins($admins) {
	foreach ($admins as $element=>$key) {
		if (isset($adminsnew)) {
			$adminsnew[sizeof($adminsnew)] = $element;
		} else {
			$adminsnew[0] = $element;
		}
	}
	store_array($adminsnew, 'admins.php');
}
//Update ignore function, takes an array of ignored user ids as an input and rewrites the ignores to only include them
function update_ignore($ignore) {
	foreach ($ignore as $element=>$key) {
		if (isset($ignorenew)) {
			$ignorenew[sizeof($ignorenew)] = $element;
		} else {
			$ignorenew[0] = $element;
		}
	}
	store_array($ignorenew, 'ignore.php');
}
