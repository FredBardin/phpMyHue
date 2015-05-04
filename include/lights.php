<?php
// Display and manage lights
// F. Bardin 07/02/2015
//-----------------------------------
// Anti-hack
if (! defined('ANTI_HACK')){exit;}

include 'include/functions.php';

// Load groups and lights informations
$HueAPI->loadInfo("groups");
$HueAPI->loadInfo("lights");
$HueAPI->assignLightsGroup();

?>
<SCRIPT language="javascript">
$('#detail').hide("slide");
</SCRIPT>
<?php
display_lights_groups();
?>
<SCRIPT TYPE="text/javascript" SRC="js/lights.js"></SCRIPT>
<SCRIPT language="javascript">
$('#allon, #alloff, #tabs button.gron, #tabs button.groff, #otheron, #otheroff').button();
lightsTab(); // keep in last position after object initialization
</SCRIPT>

