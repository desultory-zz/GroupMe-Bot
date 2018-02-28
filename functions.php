<?php
//Writes the contents of a variable to a text file for debugging purposes
function debugvar($variable) {
	file_put_contents('debug.txt', print_r($variable, true));
}
//Initialize the database
function initdb() {
	$db = new PDO('sqlite:db.sqlite');
	$dbcmds = ['CREATE TABLE IF NOT EXISTS config(
		name TEXT NOT NULL,
		value TEXT NOT NULL
		)',
	'CREATE TABLE IF NOT EXISTS settings(
		name TEXT NOT NULL,
		value INTEGER NOT NULL
		)',
	'CREATE TABLE IF NOT EXISTS responses(
		find TEXT NOT NULL,
		respond TEXT NOT NULL
		)',
	'CREATE TABLE IF NOT EXISTS users(
		name TEXT NOT NULL,
		userid TEXT NOT NULL,
		admin INTEGER,
		ignored INTEGER
		)',
	'CREATE TABLE IF NOT EXISTS log(
		entry TEXT NOT NULL,
		timestamp INTEGER NOT NULL
		)',
	];
	foreach ($dbcmds as $cmd) {
		$db->exec($cmd);
	}
	$clean = 1;
	foreach ($db->errorInfo() as $error) {
		if ($error != 0) {
			$clean = $error;
		}
	}
	return $clean;
}
//Gets the specified config variable value from the database
function get_config_var($parameter) {
	$db = new PDO('sqlite:db.sqlite');
	$query = $db->prepare('SELECT value FROM config WHERE name=:name');
	$query->bindValue(':name', $parameter, PDO::PARAM_STR);
	$query->execute();
	$result = $query->fetch(PDO::FETCH_ASSOC);
	return $result['value'];
}
//Returns the responses as an array
function get_responses() {
	$db = new PDO('sqlite:db.sqlite');
	$query = $db->prepare('SELECT find,respond FROM responses');
	$query->execute();
	return $query->fetchAll();
}
//Returns the chat log
function get_log() {
	$db = new PDO('sqlite:db.sqlite');
	$query = $db->prepare('SELECT entry,timestamp FROM log');
	$query->execute();
	return $query->fetchAll();
}
//Returns the admins as an array
function get_admins() {
	$db = new PDO('sqlite:db.sqlite');
	$query = $db->prepare('SELECT userid FROM users WHERE admin=1');
	$query->execute();
	return $query->fetchAll(PDO::FETCH_COLUMN, 0);
}
//Returns the ignored users as an array
function get_ignored() {
	$db = new PDO('sqlite:db.sqlite');
	$query = $db->prepare('SELECT userid FROM users WHERE ignored=1');
	$query->execute();
	return $query->fetchAll(PDO::FETCH_COLUMN, 0);
}
//Returns the settings as an array
function get_settings() {
	$db = new PDO('sqlite:db.sqlite');
	$query = $db->prepare('SELECT name,value FROM settings');
	$query->execute();
	$result = $query->fetchAll();
	foreach ($result as $setting) {
		$settings[$setting[0]] = $setting[1];
	}
	return $settings;
}
//Logs all chat to the database
function logging($userid, $name, $text) {
	$db = new PDO('sqlite:db.sqlite');
	if (get_config_var('log')) {
		$entry = "$name($userid): $text";
		$statement = $db->prepare('INSERT INTO log (entry, timestamp) VALUES (:entry, :timestamp)');
		$statement->bindValue(':entry', $entry, PDO::PARAM_STR);
		$statement->bindValue(':timestamp', time(), PDO::PARAM_STR);
		$statement->execute();
	}
}

//Basic response (no images)
function basic_response($text, $name, $userid) {
	$responses = get_responses();
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
	$wutoken = get_config_var('wutoken');
	$wuloc = get_config_var('wuloc');
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
	$bottoken = get_config_var('bottoken');
	$postdata = [
		'bot_id' => $bottoken,
		'text' => $message
	];
	curl_post(http_build_query($postdata));
}
//Send image function, takes message and img url as inputs and posts to GroupMe
function send_img($message, $image) {
	$bottoken = get_config_var('bottoken');
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
	$bottoken = get_config_var('bottoken');
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
//Get bot group function, returns the group id of the bot
function get_bot_group() {
	$apitoken = get_config_var('apitoken');
	$bottoken = get_config_var('bottoken');
	$bots = json_decode(curl_get("https://api.groupme.com/v3/bots?token=$apitoken"));
	foreach($bots->response as $element) {
		if ($element->bot_id == $bottoken) {
			return $element->group_id;
		}
	}
}
//Get user id function, takes a name as input and returns the user id
function get_user_id($name) {
	$apitoken = get_config_var('apitoken');
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
	$apitoken = get_config_var('apitoken');
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
//Get users function, gets user information from the groupme api, adds it to the database, and returns it as an array
function get_users() {
	$apitoken = get_config_var('apitoken');
	$groupid = get_bot_group();
	$groups = json_decode(curl_get("https://api.groupme.com/v3/groups?token=$apitoken"));
	$index = 0;
	$db = new PDO('sqlite:db.sqlite');
	foreach($groups->response as $element) {
		if ($element->id == $groupid) {
			foreach($element->members as $member) {
				$userid = $member->user_id;
				$name = $member->nickname;
				$avatar = $member->image_url;
				$query = $db->prepare('SELECT userid FROM users WHERE userid=:userid');
				$query->bindValue('userid', $userid, PDO::PARAM_STR);
				$query->execute();
				$result = $query->fetch(PDO::FETCH_ASSOC);
				if (isset($result['userid'])) {
					$query = $db->prepare('UPDATE users SET name=:name WHERE userid=:userid');
					$query->bindValue(':name', $name, PDO::PARAM_STR);
					$query->bindValue(':userid', $userid, PDO::PARAM_STR);
					$query->execute();
				} else {
					$query = $db->prepare('INSERT INTO users (name, userid) VALUES (:name, :userid)');
					$query->bindValue(':name', $name, PDO::PARAM_STR);
					$query->bindValue(':userid', $userid, PDO::PARAM_STR);
					$query->execute();
				}
				$members[$index] = [
					"userid" => $userid,
					"name" => $name,
					"avatar" => $avatar
				];
			$index++;
			}
		}
	}
	return $members;
}
//Adds a response to the database, uses input find and respond where find is the text that is searched for and respond is the text that is retrned
function add_response($find, $respond) {
	$responses = get_responses();
	$exists = 0;
	foreach ($responses as $element) {
		if (stripos($element[0], $find) !== FALSE || stripos($find, $element[0]) !== FALSE) {
			$exists = 1;
		}
	}
	if (!$exists) {
		$db = new PDO('sqlite:db.sqlite');
		$query = $db->prepare('INSERT INTO responses (find, respond) VALUES (:find, :respond)');
		$query->bindValue(':find', $find, PDO::PARAM_STR);
		$query->bindValue(':respond', $respond, PDO::PARAM_STR);
		$query->execute();
	} else {
		echo "Similar find already exists<br>";
	}

}
//Deletes responses from the database, takes the "find" string as input
function del_responses($delete) {
	$db = new PDO('sqlite:db.sqlite');
	foreach ($delete as $find) {
		$query = $db->prepare('DELETE FROM responses WHERE find=:find');
		$query->bindValue(':find', $find, PDO::PARAM_STR);
		$query->execute();
	}
}
//Deletes all admins from the database
function delete_admins() {
	$db = new PDO('sqlite:db.sqlite');
	$query = $db->prepare('UPDATE users SET admin = 0');
	$query->execute();
}
//Updates the admins by deleting all of them and then adding the specified userids
function update_admins($admins) {
	delete_admins();
	$db = new PDO('sqlite:db.sqlite');
	foreach ($admins as $element) {
		$query = $db->prepare('UPDATE users SET admin=:admin WHERE userid=:userid');
		$query->bindValue(':userid', $element, PDO::PARAM_STR);
		$query->bindValue(':admin', '1', PDO::PARAM_STR);
		$query->execute();
	}
}
//Deletes all ignored users from the database
function delete_ignored() {
	$db = new PDO('sqlite:db.sqlite');
	$query = $db->prepare('UPDATE users SET ignored = 0');
	$query->execute();
}
//Updates the users by deleting all of them and then adding the specified userids
function update_ignored($ignored) {
	delete_ignored();
	$db = new PDO('sqlite:db.sqlite');
	foreach ($ignored as $element) {
		$query = $db->prepare('UPDATE users SET ignored=:ignored WHERE userid=:userid');
		$query->bindValue(':userid', $element, PDO::PARAM_STR);
		$query->bindValue(':ignored', '1', PDO::PARAM_STR);
		$query->execute();
	}
}
//Resets all settings in the database
function reset_settings() {
	$db = new PDO('sqlite:db.sqlite');
	$query = $db->prepare('UPDATE settings SET value = 0');
	$query->execute();
}
//Updates the settings by restting all of the settings and then enabling the specified ones
function update_settings($settings) {
	reset_settings();
	$db = new PDO('sqlite:db.sqlite');
	foreach ($settings as $element) {
		$query = $db->prepare('UPDATE settings SET value=:value WHERE name=:name');
		$query->bindValue(':name', $element, PDO::PARAM_STR);
		$query->bindValue(':value', '1', PDO::PARAM_STR);
		$query->execute();
	}
}
//Adds the specified setting to the array if it doesn't already exist
function add_setting($setting) {
	$settings = get_settings();
	$exists = 0;
	foreach ($settings as $element=>$key) {
		if ($setting == $element) {
			$exists = 1;
		}
	}
	if (!$exists) {
		$db = new PDO('sqlite:db.sqlite');
		$query = $db->prepare('INSERT INTO settings (name, value) VALUES (:name, :value)');
		$query->bindValue(':name', $setting, PDO::PARAM_STR);
		$query->bindValue(':value', '1', PDO::PARAM_STR);
		$query->execute();
	} else {
		echo "Setting already exists<br>";
	}

}
//Deletes responses from the database, takes the "find" string as input
function del_settings($delete) {
	$db = new PDO('sqlite:db.sqlite');
	foreach ($delete as $setting) {
		$query = $db->prepare('DELETE FROM settings WHERE name=:setting');
		$query->bindValue(':setting', $setting, PDO::PARAM_STR);
		$query->execute();
	}
}
//Display the setup form
function disp_setup() {
	$setup = <<<'EOSETUP'
<form name="setup" method="post" action="">
<table align="center" style="width: 50%;">
	<tr>
		<td>Panel Username:</td>
		<td><input type="text" style="width: 100%;" name="user" placeholder="Panel username" required></td>
	</tr>
	<tr>
		<td>Panel Password:</td>
		<td><input type="password" style="width: 100%;" name="pass" placeholder="Panel password" required></td>
	</tr>
	<tr>
		<td>GroupMe API token</td>
		<td><input type="text" style="width: 100%;" name="apitoken" placeholder="Your GroupMe API token" required></td>
	</tr>
	<tr>
		<td>GroupMe Bot Token</td>
		<td><input type="text" style="width: 100%;" name="bottoken" placeholder="Your GroupMe bot token" required></td>
	</tr>
	<tr>
		<td>WeatherUnderground API token</td>
		<td><input type="text" style="width: 100%;" name="wutoken" placeholder="Your WeatherUnderground API token" value="null" required></td>
	</tr>
	<tr>
		<td>WeatherUnderground Location Code</td>
		<td><input type="text" style="width: 100%;" name="wuloc" placeholder="Your WeatherUnderground Location Code" value="null" required></td>
	</tr>
	<tr>
		<td>Logging, check to enable</td>
		<td><input type="checkbox" style="width: 100%;" name="log" value="1" checked required></td>
	</tr>
	<tr>
		<td colspan="3"><input type="submit" value="Initialize"></td>
	</tr>
</table>
</form>
EOSETUP;
	echo $setup;
}

