<?php
// Display and manage scenes
// F. Bardin 06/03/2015
//----------------------------
// Anti-hack
if (! defined('ANTI_HACK')){exit;}

// Load scenes and lights informations
$HueAPI->loadInfo("scenes");
$HueAPI->loadNameIndex("scenes");

?>
<SCRIPT language="javascript">
$('#detail').hide("slide");
</SCRIPT>
<?php

// Display scenes
echo "<TABLE>";
if (isset($HueAPI->info['scenesnames'])){
	foreach ($HueAPI->info['scenesnames'] as $sname => $sceneid){
		echo "\n<TR CLASS=radio>";
		echo "<TD><SPAN CLASS=\"ui-icon ui-icon-radio-off\"><INPUT TYPE=radio NAME=scradio ID=$sceneid></SPAN>";
		echo "<TD CLASS=sname><LABEL FOR=$sceneid>$sname</LABEL>";
		echo "<TD><LABEL FOR=$sceneid>&nbsp;".$trs["LightsNB"]." <SPAN>".count($HueAPI->info['scenes'][$sceneid]['lights'])."</SPAN></LABEL>";
	}
}
echo "</TABLE>";
?>
<SCRIPT TYPE="text/javascript" SRC="js/scenes.js"></SCRIPT>
<SCRIPT language="javascript">
scenesTab();
</SCRIPT>

