<?php
function logging($userid, $name, $text) {
	include 'config.php';
	if ($log) {
		if (!is_dir($logdir)) {
			mkdir($logdir, $logdirchmod);
		}
		file_put_contents($logdir . '/' . $logfile, "$userid($name): $text\n", FILE_APPEND);
	}
}

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

function weather_response($text) {
	include 'config.php';
	if (stripos($text, 'weather') !== FALSE) {
		if (isset($wutoken) && isset($wuloc)) {
			$rawweather = json_decode(file_get_contents("https://api.wunderground.com/api/$wutoken/conditions/q/$wuloc.json"));
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

function btc_response($text) {
	if (stripos($text, 'bitcoin') !== FALSE) {
		$pricedata = json_decode(file_get_contents("https://min-api.cryptocompare.com/data/price?fsym=BTC&tsyms=USD"));
		$usdprice = $pricedata->USD;
		$message = "Bitcoin is worth \$$usdprice";
		$btclogo = 'https://files.coinmarketcap.com/static/img/coins/32x32/bitcoin.png';
		send_img($message, $btclogo);
	}
}

function eth_response($text) {
	if (stripos($text, 'ethereum') !== FALSE) {
		$pricedata = json_decode(file_get_contents("https://min-api.cryptocompare.com/data/price?fsym=ETH&tsyms=BTC,USD"));
		$usdprice = $pricedata->USD;
		$btcprice = $pricedata->BTC;
		$message = "Ethereum is worth \$$usdprice and $btcprice Bitcoin";
		$ethlogo = 'https://files.coinmarketcap.com/static/img/coins/32x32/ethereum.png';
		send_img($message, $ethlogo);
	}
}

function ltc_response($text) {
	if (stripos($text, 'litecoin') !== FALSE) {
		$pricedata = json_decode(file_get_contents("https://min-api.cryptocompare.com/data/price?fsym=LTC&tsyms=BTC,USD"));
		$usdprice = $pricedata->USD;
		$btcprice = $pricedata->BTC;
		$message = "Litecoin is worth \$$usdprice and $btcprice Bitcoin";
		$ltclogo = 'https://files.coinmarketcap.com/static/img/coins/32x32/litecoin.png';
		send_img($message, $ltclogo);
	}
}

function curl_post($postfields) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://api.groupme.com/v3/bots/post');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
	curl_exec($ch);
	curl_close($ch);
}

function send($message) {
	include 'config.php';
	$postdata = [
		'bot_id' => $bottoken,
		'text' => $message
	];
	curl_post(http_build_query($postdata));
}

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

function store_array($array, $file) {
	$array = json_encode($array);
	file_put_contents($file, "<?php\n" . $array);
}

function read_array($file) {
	$array = file_get_contents($file);
	$array = str_replace('<?php', null, $array);
	$array = json_decode($array, true);
	return $array;
}

function get_bot_group() {
	include 'config.php';
	$bots = json_decode(file_get_contents("https://api.groupme.com/v3/bots?token=$apitoken"));
	foreach($bots->response as $element) {
		if ($element->bot_id == $bottoken) {
			return $element->group_id;
		}
	}
}

function get_user_id($name) {
	include 'config.php';
	$user_id = 'No member with that name found';
	$groupid = get_bot_group();
	$groups = json_decode(file_get_contents("https://api.groupme.com/v3/groups?token=$apitoken"));
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

function get_name($userid) {
	include 'config.php';
	$name = 'Invalid userid';
	$groupid = get_bot_group();
	$groups = json_decode(file_get_contents("https://api.groupme.com/v3/groups?token=$apitoken"));
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

function parse_cmd($command) {
	$command = explode(' -"', $command);
	array_splice($command, 0, 1);
	foreach($command as &$element) {
		$element = substr($element, 0, strlen($element) -1);
	}
	return $command;
}

function disp_help() {
	$help = <<<'EOHELP'
		'/help' displays this message
		'/ignorelist' lists all users who are being ignored
		'/ignore -"userid"' ignores all messages from specified user
		'/unignore -"userid"' removed ignore on specified user
		'/responses' displays all current responses
		'/addresponse -"find" -"respond"' adds a response to the "find" phrase %n = name, %u = userid
		'/delresponse -"find"' deletes a response for phrase "find"
		'/admins' displays all current admins
		'/getuserid -"name"' displays user id of a member of the group
		'/addadmin -"userid" adds the specified user ID to the admin list
		'/deladmin -"userid" adds the specified user ID to the admin list
		'/enable -"(weather|btc|eth)"' enables a custom response
		'/disable -"(weather|btc|eth)"' disables a custom response
		'/status' lists all settings and their current status
EOHELP;
	send($help);
}


function list_ignored() {
	$message = null;
	$ignored = read_array('ignore.php');
	foreach($ignored as $element) {
		$name = get_name($element);
		$message .= "$element($name)\n";
	}
	send($message);
}

function add_ignore($userid) {
	$ignored = read_array('ignore.php');
	$message = "Something bad happened :(";
	$name = get_name($userid);
	if (!in_array($userid, $ignored)) {
		if ($name !== 'Invalid userid') {
			$ignored[count($ignored)] = $userid;
			store_array($ignored, 'ignore.php');
			$message = "$userid($name) has been added to the ignore list";
		} else {
			$message = "No member associated with User ID \"$userid\" is in the group";
		}
	} else {
		$message = "$userid($name) is already being ignored";
	}
	return $message;
}

function del_ignore($userid) {
	$ignored = read_array('ignore.php');
	$message = "Something bad happened :(";
	$name = get_name($userid);
	if (in_array($userid, $ignored)) {
		array_splice($ignored, array_search($userid, $ignored), 1);
		$message = "$userid($name) was removed from the ignore list";
		store_array($ignored, 'ignore.php');
	} else {
		$message = "$userid($name) is not being ignored";
	}
	return $message;
}

function list_responses() {
	$message = null;
	$responses = read_array('responses.php');
	foreach($responses as $element) {
		$message .= "$element[0] -> $element[1]\n";
	}
	send($message);
}

function search_responses($needle) {
	$responses = read_array('responses.php');
	$counter = 0;
	$position = false;
	foreach($responses as $element) {
		if (stripos($element[0], $needle) !== FALSE || stripos($needle, $element[0]) !== FALSE) {
			$position = $counter;
		}
	$counter++;
	}
	return $position;
}

function add_response($find, $response) {
	$responses = read_array('responses.php');
	$message = "Something bad happened :(";
	if (search_responses($find) !== FALSE) {
		$message = "There is already a similar response for $find";
	} else {
		$responses[count($responses)] = [$find, $response];
		store_array($responses, 'responses.php');
		$message = "Added response $find -> $response";
	}
	return $message;
}

function del_response($find) {
	$responses = read_array('responses.php');
	$message = "Something bad happened :(";
	if (search_responses($find) !== FALSE) {
		array_splice($responses, search_responses($find), 1);
		store_array($responses, 'responses.php');
		$message = "Deleted response for $find";
	} else {
		$message = "There is not a response for $find, nothing to delete";
	}
	return $message;
}

function list_admins() {
	$message = null;
	$admins = read_array('admins.php');
	foreach($admins as $element) {
		$name = get_name($element);
		$message .= "$element($name)\n";
	}
	send($message);
}

function add_admin($userid) {
	$admins = read_array('admins.php');
	$message = "Something bad happened :(";
	$name = get_name($userid);
	if (!in_array($userid, $admins)) {
		if ($name !== 'Invalid userid') {
			$admins[count($admins)] = $userid;
			store_array($admins, 'admins.php');
			$message = "$userid($name) has been added to the admin list";
		} else {
			$message = "No member associated with User ID \"$userid\" is in the group";
		}
	} else {
		$message = "$userid($name) is already an admin";
	}
	return $message;
}

function del_admin($userid) {
	$admins = read_array('admins.php');
	$message = "Something bad happened :(";
	$name = get_name($userid);
	if (in_array($userid, $admins)) {
		array_splice($admins, array_search($userid, $admins), 1);
		$message = "$userid($name) was removed from the admin list";
		store_array($admins, 'admins.php');
	} else {
		$message = "$userid($name) is not an admin";
	}
	return $message;
}

function enable_custom($setting) {
	$settings = read_array('settings.php');
	$message = "Something bad happened :(";
	if ($settings[$setting] == 1) {
		$message = "Already enabled, no changes made";
	} else {
		$settings[$setting] = 1;
		$message = "Response enabled";
		store_array($settings, 'settings.php');
	}
	return $message;
}

function disable_custom($setting) {
	$settings = read_array('settings.php');
	$message = "Something bad happened :(";
	if ($settings[$setting] == 0) {
		$message = "Already disabled, no changes made";
	} else {
		$settings[$setting] = 0;
		$message = "Response disabled";
		store_array($settings, 'settings.php');
	}
	return $message;
}

function list_status() {
	$message = null;
	$settings = read_array('settings.php');
	foreach($settings as $setting => $state) {
		$message .= "$setting -> $state\n";
	}
	send($message);
}
