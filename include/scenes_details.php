<?php
// Set the div details for scenes
// F. Bardin 2015/03/21
// ------------------------------------
// Anti-hack
if (! defined('ANTI_HACK')){exit;}

include 'include/functions.php';

echo "\n<FIELDSET CLASS=\"ui-widget ui-widget-content ui-corner-all\"><LEGEND>Save scene with selected lights</LEGEND>";
echo "\n<DIV ID=dispscname>Name <INPUT TYPE=text ID=scname CLASS=ui-corner-all> ";
echo "<BUTTON ID=updscene>Update scene</BUTTON>&nbsp;";
echo "<BUTTON ID=newscene>New scene</BUTTON>";
echo "</DIV>"; // dispscname
echo "</FIELDSET>";
echo "<BR>";

echo "\n<DIV ID=scroll>";
display_lights_groups("S_","B",true);
echo "\n<DIV>"; // scroll

?>
<SCRIPT language="javascript">
$('#detail button.allon, #detail button.alloff, #detail button.gron, #detail button.groff, #detail button.otheron, #detail button.otheroff').button();
$('#updscene, #newscene').button();
scenesDetail();
</SCRIPT>

