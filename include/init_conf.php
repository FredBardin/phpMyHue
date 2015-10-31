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

// Define 
define('INIT', true);

$conf_file = "config.php";
$template_file = "config.tpl.php";


ini_set('default_socket_timeout', 1);

// If config file does not exist : copy template
if (file_exists("include/$conf_file")){
	// Read config
	include "include/config.php";
} else {
	// Init parameters
	@$confstep = $_REQUEST['confstep'];
	@$bridgeip = $_REQUEST['bridgeip'];
	@$username = $_REQUEST['username'];
	$lang = "en";

	echo "<FORM METHOD=post>";

	// Config steps
	switch($confstep){
		case "1" : // Look for bridge
			echo "<H3>Looking for Hue brigde IP</H3>";
			getBridgeIP();
			echo "<INPUT TYPE=hidden NAME=\"confstep\" VALUE=\"2\">";
			echo "<INPUT TYPE=hidden NAME=\"bridgeip\" VALUE=\"$bridgeip\">";
			echo "<H2>Please press the bridge link button then click on continue within 30 seconds</H2>";
			break;

		case "2" : // Register application
			echo "<H3>Register application in bridge</H3>";
			getUserName();
			echo "<INPUT TYPE=hidden NAME=\"confstep\" VALUE=\"3\">";
			echo "<INPUT TYPE=hidden NAME=\"bridgeip\" VALUE=\"$bridgeip\">";
			echo "<INPUT TYPE=hidden NAME=\"username\" VALUE=\"$username\">";
			break;

		case "3" : // Record configuration
			echo "<H3>Record configuration</H3>";
			recordConf();
			break;

		default : // Init step
			echo "<H2>Configuration missing - Automatic setup begins</H2>";
			echo "<INPUT TYPE=hidden NAME=\"confstep\" VALUE=\"1\">";
	}
	echo "<INPUT TYPE=submit VALUE=\"Continue\">";
	echo "</FORM>";
	die();
}

//----------------------------------------
// Function to look for Hue bridge IP
//----------------------------------------
function getBridgeIP(){
	global $bridgeip; 

	// Get web server ip
	// It's assumed that bridge is on the same sub-network with subnet mask 255.255.255.0
	$ip = $_SERVER["SERVER_ADDR"];
	// Get subnet
	$subnet = preg_replace("/(.*)[.]([^.]*)/","$1",$ip);

	$request = "/api/config";
	$search_str = "Philips hue";
	$pattern = "/".$search_str."/";

	echo "Detection in progress on subnet $subnet<BR>";
	ob_flush();
	flush();

	$i=13; //FBA====> A REMETTRE A 0 APRES TESTS
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
		echo "<H4>Hue bridge found at $bridgeip</H4>";
	} else {
		die("<H2>Hue bridge not found - Configuration has to be set manually.</H2>");
	}

} // getBridgeIP

//----------------------------------------------
// Function to register app and get a username
//----------------------------------------------
function getUserName(){
	global $bridgeip,$username,$lang; 

	// Activate api
	include 'include/hueapi.php';

	// Register app
	$answer=json_decode($HueAPI->setInfo("",'{"devicetype":"phpMyHue#'.$bridgeip.'"}',"POST"),true);

	// Get result
	if (isset($answer[0]['error'])){
//FBA		die("<H2>ERROR : ".$answer[0]['error']['description']."</H2>");
$username="fredbardin";
		echo("<H2>ERROR : ".$answer[0]['error']['description']."</H2>");
	} else {
		$username = $answer[0]['success']['username'];
		echo "<H4>Application registered successfully.</H4>";
	}
} // getUserName

//----------------------------------------------
// Function to record configuration
//----------------------------------------------
function recordConf(){
	global $bridgeip,$username,$lang,$conf_file,$template_file;

	// Init config content
	$conf_array = array(
		"<?php",
		"if (! defined('ANTI_HACK')){exit;}",
		"/*****************",
		" * Configuration *",
		" *****************/",
		"\$bridgeip = \"$bridgeip\";",
		"\$username = \"$username\";",
		"\$lang = \"$lang\";",
		"?>"
	);
	$conf_count = count($conf_array);
	$conf_rec = "";
	$conf_html = "";
	for ($i = 0; $i < $conf_count; $i++){
		$conf_rec .= $conf_array[$i]."\n";
		$conf_html .= str_replace(" ","&nbsp;",htmlentities($conf_array[$i]))."<BR>\n";
	}

	if (! file_put_contents("include/$conf_file",$conf_rec)){
		echo "<BR><B>Fatal Error</B> : Automatic creation of 'include/$conf_file' failed.<BR>";
		echo "<U>Copy manually 'include/$template_file' to 'include/$conf_file' then fill this file with the following values</U> :<BR>";
	} else {
		echo "<B>Configuration file created successfully with the following values :</B><BR>";
	}
	// Echo to screen
	echo "<BR><DIV STYLE=\"margin:auto;width:250px;border:1px outset #000000;text-align:left;\"><CODE>".$conf_html."</CODE></DIV>";

} // recordConf

/*****************
 * Configuration *
 *****************/
/*
$bridgeip = "10.10.10.14"; // config+api+about ==> CONFIG
$username = "fredbardin"; // config+api ==> CONFIG

$appname = "hue#tardis"; // que config ==> INIT

$lang = "fr"; // config+api+about+index.php ==> CONFIG
*/

?>
