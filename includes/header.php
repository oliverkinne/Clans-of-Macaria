<?php
$server = $_SERVER['SERVER_NAME'];
$pageURL = $_SERVER['REQUEST_URI'];
$page = preg_replace('/^(index|default|home|welcome)\.(php|html|htm|asp|aspx)$/i', '', preg_replace('/^.*\//', '', $pageURL));
$path = preg_replace('/\/[^\/]*$/', '/', $pageURL);
$mainPath = preg_replace('/^(\/[^\/]*\/).*$/', '$1', $path);

if ($server != 'direct.oliverkinne.com' && $server != 'www.oliverkinne.com') {
	header('Location: http://www.oliverkinne.com'.$path.$page, true, 301);
	die();
}
?><!DOCTYPE HTML>
<html xmlns:fb="https://www.facebook.com/2008/fbml" xmlns:addthis="http://www.addthis.com/help/api-spec" xmlns:og="http://ogp.me/ns#">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

	<meta http-equiv="X-UA-Compatible" content="IE=Edge" />

	<meta name="HandheldFriendly" content="True">
	<meta name="MobileOptimized" content="320">
	<meta name="viewport" content="initial-scale=1.0">

	<link rel="apple-touch-icon-precomposed" href="/clans-of-macaria/apple-touch-icon.png" />
	<link rel="shortcut icon" href="/clans-of-macaria/favicon.ico" />

	<meta property="og:site_name" content="Clans of Macaria" />
	<meta property="og:url" content="http://www.oliverkine.com<?php echo($path.$page)?>" />

	<meta name="keywords" content="board,game,turn based,players,rounds,settle,clan,team" />
