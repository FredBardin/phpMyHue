<?php
//============================================================================
// Router code for main tab
//----------------------------------------------------------------------------
// Parameter : routage target
//----------------------------------------------------------------------------
// F. Bardin 07/02/2015
//============================================================================
// Anti-hack
define('ANTI_HACK', true);

include 'include/hueapi.php';

@$rt=$_REQUEST['rt'];

switch ($rt)
{
	case "lights" :
		include 'include/lights.php';
		break;
	case "scenes" :
		include 'include/scenes.php';
		break;
	case "about" :
		include 'include/about.php';
		break;
// action
	case "switch" :
	case "display" :
		include 'include/huecolor.php';
		$lnum = $_REQUEST['lnum'];

		$js=$HueAPI->loadInfo("lights/$lnum");

		if ($rt == "switch"){
			$action = "lights/$lnum/state";
			$state = &$HueAPI->info['lights'][$lnum]['state'];
			if ($state['on'] == ""){
				$state['on'] = true;
				$cmd_array = array('on'=>true);
			} else {
				$state['on'] = "";
				$cmd_array = array('on'=>false);
			}
			$HueAPI->setInfo($action,$cmd_array);
		}

		display_light($lnum);
		break;
	case "color" :
		include 'include/huecolor.php';
		$rgb = $_REQUEST['rgb'];
		@$type = $_REQUEST['type'];
		echo RGBToXy($rgb,$type);
		break;
}
?>
