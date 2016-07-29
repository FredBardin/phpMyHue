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

header('Content-Type: text/html; charset=UTF-8'); // to correctly display translation

switch ($rt)
{
// Screens
	case "lights" :
		include 'include/lights.php';
		break;
	case "scenes" :
		include 'include/scenes.php';
		break;
	case "effects" :
		include 'include/effects.php';
		break;
	case "rules" :
		include 'include/rules.php';
		break;
	case "about" :
		include 'include/about.php';
		break;
// Actions
	case "runeffect" :
		$effect = $_REQUEST['effect'];
		@$debug = $_REQUEST['debug'];
		include 'include/hueeffect.php';
		$HueEffect->runEffect($effect);
		break;
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
	case "color" : // echo json with xy+bri from rgb
		include 'include/huecolor.php';
		$rgb = $_REQUEST['rgb'];
		echo RGBToXy($rgb);
		break;
	case "addcond" : // add a condition row
		include 'include/functions.php';
		$sensorid = $_REQUEST['sensorid'];
		$cond = $_REQUEST['cond'];
		getCondRow($sensorid, $cond, array(), true);
		break;
	case "addact" : // add an action row
		include 'include/functions.php';
		$act = $_REQUEST['act'];
		getActRow($act, array(), true);
		break;
}
?>
