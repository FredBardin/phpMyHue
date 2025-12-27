<?php
// Initialize configuration
//---------------------------------------------------
// If not exists (=first install) : create it
// If exists : load it and re-write it if requested
//---------------------------------------------------
// F. Bardin 06/09/2015
// 30/09/2017 : Add re-write config query management
// 22/12/2025 : add https management + multi-config support
//---------------------------------------------------
// Anti-hack
if (! defined('ANTI_HACK')){exit;}

include 'include/functions.php';

$conf_pref = "config";
$conf_ext = "php";
$template_file = "config.tpl.php";

ini_set('default_socket_timeout', 1);

// Select existing config files if not already known
@$conf_file = $_REQUEST['cfg'];
if (! isset($conf_file)){ # Look for config.php and config_*.php files
	$conf_files = glob("include/{$conf_pref}_*.$conf_ext");
	$conf_count = count($conf_files);
	if (file_exists("include/$conf_pref.$conf_ext")){
		array_unshift($conf_files, "include/$conf_pref.$conf_ext");
		$conf_count += 1;
	}
} else { // Use known config file
	$conf_count = 1;
}

// If several config file then choose one
if  ($conf_count > 1){
	echo "<H2>Multiple configuration files found - Choose one</H2>";
	echo "<FORM METHOD=post>";
	echo "<SELECT name=\"cfg\">\n";
	foreach ($conf_files as $val) { // Add files without extension
		$cf = preg_replace("/[.]$conf_ext$/","",basename($val));
		echo "<OPTION VALUE=\"$cf\"> $cf </OPTION>\n";
	}	
	echo "</SELECT><BR>";
	echo "<INPUT TYPE=hidden NAME=\"confstep\" VALUE=\"0\">";
	echo "<BR>";
	echo "<INPUT TYPE=submit VALUE=\"Continue\">";
	echo "</FORM>";
	die();
} elseif ($conf_count == 1 and ! isset($conf_file)) { // Init config file if only 1 not already known
	$conf_file = preg_replace("/[.]$conf_ext$/","",basename($conf_files[0]));
} elseif ($conf_count == 0) { // Init default config file name
	$conf_file = "$conf_pref";
}	

// Add extension to config file
$conf_file .= ".$conf_ext";

// Init config depending on number of existing config files
if (file_exists("include/$conf_file")){ // Config exists
	@$updconf = $_REQUEST['updconf'];

	include "include/$conf_file";

	// Load translations
	if ($lang == ""){$lang = "en";} // Default lang = en
	$trs = json_decode(implode(file('lang/text_'.$lang.'.json')),true);

	if (! isset($prot)) {
		$prot = 'http';
		echo "<H3>".$trs["Protocol"].' '.$trs["missing_in"]." 'include/$conf_file', ".$trs["initialised_to"]." : \$prot = \"http\"</H3>";
	}

	if (isset($updconf)){ // If config update requested
		// Backup config parameters sent
		@$prot_bck = $_REQUEST['prot'];
		@$bridgeip_bck = $_REQUEST['bridgeip'];
		@$username_bck = $_REQUEST['username'];
		@$lang_bck = $_REQUEST['lang'];

		// Update old config with new parameter(s)
		if (isset($prot_bck)){$prot = $prot_bck;}
		if (isset($bridgeip_bck)){$bridgeip = $bridgeip_bck;}
		if (isset($username_bck)){$username = $username_bck;}
		if (isset($lang_bck)){$lang = $lang_bck;}

		// Write new config
		if (! writeConf()){
			// If problem : reload old config and display error message
			include "include/$conf_file";
			$trs = json_decode(implode(file('lang/text_'.$lang.'.json')),true);
			echo "<H3>".$trs["Problem_for_updating_configuration_file"]."</H3>";
		}
	}

} else { // Initialize configuration
	define('INIT', true);

	@$confstep = $_REQUEST['confstep'];
	@$subnet = $_REQUEST['subnet'];

	// Load config parameters
	@$prot = $_REQUEST['prot'];
	@$bridgeip = $_REQUEST['bridgeip'];
	@$username = $_REQUEST['username'];
	@$lang = $_REQUEST['lang'];

//	if ($prot == "") $prot = "http";

	// reLoad translations
	if ($lang == ""){$lang = "en";} // Default lang = en
	$trs = json_decode(implode(file('lang/text_'.$lang.'.json')),true);

	echo "<FORM METHOD=post>";

	// Config steps
	switch($confstep){
		case "0" : // Confirm subnet
			echo "<H3>".$trs["Protocol"]." & ".$trs["Subnet_to_look_for_Hue_brigde"]."</H3>";
			getBridgeSubnet();
			echo "<SELECT NAME=\"prot\">\n";
			echo "<OPTION VALUE=\"https\" SELECTED>https</OPTION>\n";
			echo "<OPTION VALUE=\"http\">http</OPTION>\n";
			echo "</SELECT>\n";
			echo "<INPUT TYPE=text NAME=\"subnet\" VALUE=\"$subnet\" SIZE=\"10\">";
			echo "<INPUT TYPE=hidden NAME=\"confstep\" VALUE=\"1\">";
			echo "<INPUT TYPE=hidden NAME=\"lang\" VALUE=\"$lang\">";
			echo "<H4>".$trs["If_not_known_let_the_default_displayed_value"]."</H4>";
			break;

		case "1" : // Look for bridge
			echo "<H3>".$trs["Looking_for_Hue_brigde_IP"]."</H3>";
			getBridgeIP();
			echo "<INPUT TYPE=hidden NAME=\"confstep\" VALUE=\"2\">";
			echo "<INPUT TYPE=hidden NAME=\"prot\" VALUE=\"$prot\">";
			echo "<INPUT TYPE=hidden NAME=\"lang\" VALUE=\"$lang\">";
			echo "<INPUT TYPE=hidden NAME=\"bridgeip\" VALUE=\"$bridgeip\">";
			echo "<H2>".$trs["Please_press_the_bridge_link_button_then_click_on_continue_within_30_seconds"]."</H2>";
			break;

		case "2" : // Register application
			echo "<H3>".$trs["Register_application_in_bridge"]."</H3>";
			getUserName();
			echo "<INPUT TYPE=hidden NAME=\"confstep\" VALUE=\"3\">";
			echo "<INPUT TYPE=hidden NAME=\"prot\" VALUE=\"$prot\">";
			echo "<INPUT TYPE=hidden NAME=\"lang\" VALUE=\"$lang\">";
			echo "<INPUT TYPE=hidden NAME=\"bridgeip\" VALUE=\"$bridgeip\">";
			echo "<INPUT TYPE=hidden NAME=\"username\" VALUE=\"$username\">";
			break;

		case "3" : // Record configuration
			echo "<H3>".$trs["Record_configuration"]."</H3>";
			recordConf();
			break;

		default : // Init step
			echo "<H2>Configuration missing - Automatic setup begins</H2>";
			echo "<INPUT TYPE=hidden NAME=\"confstep\" VALUE=\"0\">";
			echo "<H3>Choose a language ";
			choose_lang();
			echo "</H3>";
}
	echo "<INPUT TYPE=submit VALUE=\"".$trs["Continue"]."\">";
	echo "</FORM>";
	die();
}

//----------------------------------------------------------------
// Function to initialize the subnet where to look for Hue bridge
//----------------------------------------------------------------
function getBridgeSubnet(){
	global $subnet; 
	// Init default subnet from web server ip
	$ip = $_SERVER["SERVER_ADDR"];
	$subnet = preg_replace("/(.*)[.]([^.]*)/","$1",$ip);

} // getBridgeSubnet

//----------------------------------------
// Function to look for Hue bridge IP
//----------------------------------------
function getBridgeIP(){
	global $trs,$prot,$bridgeip,$subnet; 

	$request = "/api/config";
	$search_str = "Philips hue";
	$search_str_pro = "Hue Bridge Pro";
	$pattern = "/(".$search_str."|".$search_str_pro.")/";

	echo $trs["Detection_in_progress_on_subnet"]." $subnet ($prot)<BR>";
	ob_flush();
	flush();

	if ($prot=="https"){
		$context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
			]
		]);
	} else {
		$context = null;
	}

	$i=0;
	$name = "";
	$found=false;
	// It's assumed that bridge is on the same sub-network with subnet mask 255.255.255.0
	while (! $found and $i < 254){ // Scan subnet with ip range from 1 to 254
		$i++;
		$bridgeip = $subnet.".".$i;
		$url="$prot://".$bridgeip.$request;
		echo "... $bridgeip ";
		ob_flush();
		flush();

		$result = @file($url, 0, $context);
		if (is_array($result)){
			if (count(preg_grep($pattern,$result)) > 0){
				$found = true;
				$name = json_decode($result[0])->name;
			} 
		} 
	}
	if ($found){
		echo "<H4>".$trs["Hue_bridge_found_at"]." $bridgeip ($name)</H4>";
	} else {
		die("<H2>".$trs["Hue_bridge_not_found_-_Configuration_has_to_be_set_manually"]."</H2>");
	}

} // getBridgeIP

//----------------------------------------------
// Function to register app and get a username
//----------------------------------------------
function getUserName(){
	global $trs,$username,$lang,$conf_file; 

	// Activate api
	include 'include/hueapi.php';

	// Register app
	$answer=json_decode($HueAPI->setInfo("",'{"devicetype":"phpMyHue#'.$_SERVER["SERVER_NAME"].'"}',"POST"),true);

	// Get result
	if (isset($answer[0]['error'])){
		die("<H2>".$trs["ERROR"]." : ".$answer[0]['error']['description']."</H2>");
	} else {
		$username = $answer[0]['success']['username'];
		echo "<H4>".$trs["Application_registered_successfully"]."</H4>";
	}
} // getUserName

//----------------------------------------------
// Function to record configuration
//----------------------------------------------
function recordConf(){
	global $trs,$conf_file,$template_file;

	$conf_html="Y"; 
	if (! writeConf($conf_html)){// Warning, conf_html is used as reference in the called function. Don't use litteral value.
		echo "<BR><B>".$trs["Fatal_Error"]."</B> : ".$trs["Automatic_creation_of"]." 'include/$conf_file' ".$trs["failed"].".<BR>";
		echo "<U>".$trs["Copy_manually"]." 'include/$template_file' ".$trs["to"]." 'include/$conf_file' ".$trs["then_fill_this_file_with_the_following_values"]."</U> :<BR>";
	} else {
		echo "<B>".$trs["Configuration_file_created_successfully_with_the_following_values"]." :</B><BR>";
	}
	// Echo to screen
	echo "<BR><DIV STYLE=\"margin:auto;width:500px;border:1px outset #000000;text-align:left;\"><CODE>".$conf_html."</CODE></DIV>";

} // recordConf

//-----------------------------------------------------------------------------------------------
// Function to (re)write completly config.php file
// Parameter : conf_html (optional). If set get in return the config file content in html format
// Return : true/false (true=writing ok, false=error)
// All config parameters must be already set as global
//-----------------------------------------------------------------------------------------------
function writeConf(&$conf_html=""){
	global $trs,$prot,$bridgeip,$username,$lang,$conf_file;

	// Init config content
	$conf_array = array(
		"<?php",
		"if (! defined('ANTI_HACK')){exit;}",
		"/*****************",
		" * Configuration *",
		" *****************/",
		"\$prot = \"$prot\";",
		"\$bridgeip = \"$bridgeip\";",
		"\$username = \"$username\";",
		"\$lang = \"$lang\";",
		"?>"
	);

	// Format array content to be usable
	$conf_count = count($conf_array);
	$conf_rec = "";
	$conf_html = "";
	for ($i = 0; $i < $conf_count; $i++){
		$conf_rec .= $conf_array[$i]."\n";
		$conf_html .= str_replace(" ","&nbsp;",htmlentities($conf_array[$i]))."<BR>\n";
	}

	// Write conf file
	if (file_put_contents("include/$conf_file",$conf_rec)){return true;}
	else {return false;}
} // writeConf

?>
