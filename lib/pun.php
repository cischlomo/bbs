<?php

function format_time($params, $smarty) //$timestamp, $date_only = false)
{
	global $pun_config, $lang_common, $user;
	$timestamp=$params["time"];
	if ($timestamp == '')
		return 'Never';

	$diff = ($user['timezone'] - $pun_config['o_server_timezone']) * 3600;
	$timestamp += $diff;
	$now = time();

	$date = date($pun_config['o_date_format'], $timestamp);
	$today = date($pun_config['o_date_format'], $now+$diff);
	$yesterday = date($pun_config['o_date_format'], $now+$diff-86400);

	if ($date == $today)
		$date = 'Today';
	else if ($date == $yesterday)
		$date = 'Yesterday';

	//return $pun_config['o_time_format'];
	if (!$date_only)
		return $date.' '.date($pun_config['o_time_format'], $timestamp);
	else
		return $date;
}