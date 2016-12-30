<?php
//===========================================
// Send cmd to hue api as web service
//
// Remark : there's a trick about action for lamps that aren't in a group,
// action is set to 'other' to select this virtual group of lamp
//
// Return in json format :
// for group 'other' only : content of pseudo group (only the lights part)
// for other command : request result
//
// F. Bardin 19/02/2015
//===========================================
// Anti-hack
define('ANTI_HACK', true);

include 'include/hueapi.php';

@$action=$_REQUEST['action']; // Action to send
@$cmdjs=$_REQUEST['cmdjs']; // Cmd to send in json format
@$method=$_REQUEST['method'];	// Method to use (default=PUT)

// If action
if (isset($action)){
	// Set default method if update command
	if (isset($cmdjs) && ! isset($method)){$method = "PUT";}
	
	if ($action == 'other'){ // Process lamps without group
		$HueAPI->loadInfo("groups");
		$HueAPI->loadInfo("lights");
		$HueAPI->assignLightsGroup();
		$i=0;
		foreach ($HueAPI->info['lights'] as $lnum => $lval){
			if (! isset($lval['grp'])){ // if no group
				// if command or delete : set, else load information
				if (isset($cmdjs) || $method == "DELETE"){
					$HueAPI->setInfo("lights/$lnum/state",$cmdjs,$method);
				}
				$HueAPI->info['groups']['other']['lights'][$i] = $lnum;
				$i++;
			}
		}
		echo json_encode($HueAPI->info['groups']['other']);
	} else { // Normal process
		// if command or delete : set, else load information
		if (isset($cmdjs) || $method == "DELETE"){
			echo $HueAPI->setInfo($action,$cmdjs,$method);
		} else {
			echo $HueAPI->loadInfo($action);
		}
	}
}

?>
