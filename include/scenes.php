<?php
// Display and manage scenes
// F. Bardin 06/03/2015
//----------------------------
// Anti-hack
if (! defined('ANTI_HACK')){exit;}

// Load scenes and lights informations
$HueAPI->loadInfo("scenes");

?>
<SCRIPT language="javascript">
$('#detail').hide("slide");
</SCRIPT>
<?php

// Create id->name array
$a_sname="";
foreach ($HueAPI->info['scenes'] as $sceneid => $sval){
	$a_sname[$sceneid] = $sval['name'];
}
asort($a_sname);

// Display scenes
echo "<TABLE>";
$oldname = "";
foreach ($a_sname as $sceneid => $sname){
	echo "\n<TR CLASS=radio>";
	echo "<TD><SPAN CLASS=\"ui-icon ui-icon-radio-off\"><INPUT TYPE=radio NAME=scradio ID=$sceneid></SPAN>";
	echo "<TD CLASS=sname><LABEL FOR=$sceneid>$sname</LABEL>";
	echo "<TD><LABEL FOR=$sceneid>&nbsp;".$trs["LightsNB"]." <SPAN>".count($HueAPI->info['scenes'][$sceneid]['lights'])."</SPAN></LABEL>";
}
echo "</TABLE>";
?>
<SCRIPT TYPE="text/javascript" SRC="js/scenes.js"></SCRIPT>
<SCRIPT language="javascript">
scenesTab();
</SCRIPT>

