<?php
//============================================================================
// Router code for content of tab details
//----------------------------------------------------------------------------
// Parameter : routage target
//----------------------------------------------------------------------------
// F. Bardin 15/02/2015
//============================================================================
// Anti-hack
define('ANTI_HACK', true);

include 'include/hueapi.php';

@$rt=$_REQUEST['rt'];
@$nohide=$_REQUEST['nh']; // if set : doesn't hide details tab

switch ($rt)
{
	case "lights" :
	case "scenes" :
		// Load groups and lights informations
		$HueAPI->loadInfo("groups");
		$HueAPI->loadInfo("lights");
		$HueAPI->assignLightsGroup();
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


