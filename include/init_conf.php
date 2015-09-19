<?php
// Initialize configuration
//------------------------------------------------
// If not exists (=first install) : create it
// If exists : load it
//------------------------------------------------
// F. Bardin 06/09/2015
//------------------------------------------------
// Anti-hack
if (! defined('ANTI_HACK')){exit;}

$conf_file = "config.php";
$template_file = "config.tpl.php";
$bridgeip = "";
$username = "";
$lang = "";

ini_set('default_socket_timeout', 1);

// If config file does not exist : copy template
if (file_exists("include/$conf_file")){
	// Read config
	include "include/config.php";
} else {
	echo "<H2>Configuration missing - Initialization</H2>";
	if (! copy("include/$template_file","include/$conf_file"))
	{
		echo "<B>Fatal Error</B> : Copy template file 'include/$template_file' to 'include/$conf_file' failed.<BR>";
		echo "<U>Try to copy 'include/$template_file' to 'include/$conf_file' manually.</U><BR>";
	}
	else {echo "Automatic configuration in progress<BR>";}
	ob_flush();
	flush();
}

// If config not complete : initialize parameters
if ($bridgeip == ""){ // Detect hue bridge
	// It's assumed that bridge is on the same sub-network with subnet mask 255.255.255.0

	// Get web server ip
	$ip = $_SERVER["SERVER_ADDR"];
	// Get subnet
	$subnet = preg_replace("/(.*)[.]([^.]*)/","$1",$ip);

	$request = "/api/config";
	$search_str = "Philips hue";
	$pattern = "/".$search_str."/";

	echo "<BR>Hue brigde IP not known.<BR>";
	echo "Detection in progress on subnet $subnet<BR>";
	ob_flush();
	flush();

	$i=0;
	$found=false;
	while (! $found and $i < 254){ // Scan subnet with ip range from 1 to 254
		$i++;
		$bridgeip = $subnet.".".$i;
		$url="http://".$bridgeip.$request;
		echo "... $bridgeip ";
		ob_flush();
		flush();

		$result1 = @file($url);
		if (is_array($result1)){
			$result = preg_grep($pattern,$result1);
			if (count($result) > 0){$found = true;} 
		} 
	}
	if ($found){
		echo "<BR>Hue bridge found at $bridgeip";
	} else {
		die("<H3>Hue bridge not found - Configuration has to be set manually.</H3>");
	}
}
// Username


//die; // stop pour test --> a enlever
/*
if (! defined('ANTI_HACK')){exit;}
*/
/*****************
 * Configuration *
 *****************/
/*
$bridgeip = "10.10.10.14";
$username = "fredbardin";

$appname = "hue#tardis";
$apiurl = "http://$bridgeip/api/$username";

$lang = "fr";
*/
?>
