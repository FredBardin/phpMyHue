<?php
// Set the div details for scenes
// F. Bardin 2015/03/21
// ------------------------------------
// Anti-hack
if (! defined('ANTI_HACK')){exit;}

include 'include/functions.php';

echo "\n<FIELDSET CLASS=\"ui-widget ui-widget-content ui-corner-all\"><LEGEND>".$trs["Save_scene_with_selected_lights"]."</LEGEND>";
echo "\n<DIV ID=dispscname>".$trs["Name"]." <INPUT TYPE=text ID=scname CLASS=ui-corner-all> ";
echo "<BUTTON ID=updscene>".$trs["Update_scene"]."</BUTTON>&nbsp;";
echo "<BUTTON ID=newscene>".$trs["New_scene"]."</BUTTON>";
echo "</DIV>"; // dispscname
echo "</FIELDSET>";
echo "<BR>";

echo "\n<DIV ID=scroll>";
display_lights_groups("S_","B",true);
echo "\n<DIV>"; // scroll

?>
<SCRIPT language="javascript">
$('#S_allon, #S_alloff, #detail button.gron, #detail button.groff, #S_otheron, #S_otheroff').button();
$('#updscene, #newscene').button();
scenesDetail();
</SCRIPT>

