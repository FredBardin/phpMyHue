<?php
//============================================================================
// Router code for content of tab details
//----------------------------------------------------------------------------
// Parameter : routage target
//----------------------------------------------------------------------------
// F. Bardin 15/02/2015
// 22/12/2025 : add multi-config support
//============================================================================
// Anti-hack
define('ANTI_HACK', true);

@$rt=$_REQUEST['rt'];
@$nohide=$_REQUEST['nh']; // if set : doesn't hide details tab
@$conf_file=$_REQUEST['cf'];

include 'include/hueapi.php';

switch ($rt)
{
	case "lights" :
	case "scenes" :
		// Load groups and lights informations
		$HueAPI->loadInfo("groups");
		$HueAPI->loadInfo("lights");
		$HueAPI->assignLightsGroup();
		$HueAPI->loadNameIndex("groups");
		$HueAPI->loadNameIndex("lights");

	case "effects" :
	case "rules" :
		include 'include/'.$rt.'_details.php';
		break;
}
if (! isset($nohide)){
?>
<SCRIPT>
$("#detail").hide();
</SCRIPT>
<?php
}
?>


