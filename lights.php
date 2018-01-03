<?php
//set this to the pins you have you relays on
$pins = [
	0,
	1,
	3,
	4
];
//set this to the ip where you have the gpio.php utility (https://github.com/desultory/PiScripts/blob/master/gpio.php)
$ip = '';

function perform_curl($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT_MS, 200);
	return(trim(curl_exec($ch)));
	curl_close($ch);
}

function multicurl($ips) {
	$mh = curl_multi_init();
	foreach ($ips as $element=>$ip) {
		$ch[$element] = curl_init();
		curl_setopt($ch[$element], CURLOPT_URL, $ip);
		curl_setopt($ch[$element], CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch[$element], CURLOPT_HEADER, 0);
		curl_setopt($ch[$element], CURLOPT_RETURNTRANSFER, 1);
		curl_multi_add_handle($mh, $ch[$element]);
	}
	$active = null;
	do {
		$mrc = curl_multi_exec($mh, $active);
	} while ($mrc == CURLM_CALL_MULTI_PERFORM);
	while ($active && $mrc == CURLM_OK) {
		if (curl_multi_select($mh) != -1) {
			do {
				$mrc = curl_multi_exec($mh, $active);
			} while ($mrc == CURLM_CALL_MULTI_PERFORM);
		}
	}
	foreach ($ips as $element=>$ip) {
		curl_multi_remove_handle($mh, $ch[$element]);
	}
	curl_multi_close($mh);
}
function get_gpio_state($ip, $pin) {
	$request = "http://" . "$ip" . "/gpio.php?" . "p=" . "$pin" . "&r=1";
	return perform_curl($request);
}

function blink($ip, $pins, $delay, $count) {
	usleep($delay);
	foreach($pins as $element=>$pin) {
		$pinstate[$element] = get_gpio_state($ip, $pin);
	}
	for ($i = 0; $i < $count; $i++) {
		foreach($pins as $element=>$pin) {
			if ($pinstate[$element]) {
				$requests[$element] = "http://" . "$ip" . "/gpio.php?" . "p=". "$pin" . "&w=1" . "&s=0";
				$pinstate[$element] = 0;
			} else {
				$requests[$element] = "http://" . "$ip" . "/gpio.php?" . "p=". "$pin" . "&w=1" . "&s=1";
				$pinstate[$element] = 1;
			}
		}
		multicurl($requests);
	}
	usleep($delay);
}

function lights_on($ip, $pins) {
	foreach($pins as $element=>$pin) {
		$requests[$element] = "http://" . "$ip" . "/gpio.php?" . "p=". "$pin" . "&w=1" . "&s=1";
	}
	multicurl($requests);
}

function lights_off($ip, $pins) {
	foreach($pins as $element=>$pin) {
		$requests[$element] = "http://" . "$ip" . "/gpio.php?" . "p=". "$pin" . "&w=1" . "&s=0";
	}
	multicurl($requests);
}
