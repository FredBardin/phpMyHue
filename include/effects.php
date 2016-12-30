<?php
// Manage Effects
// F. Bardin 26/11/2015
//-----------------------------------
// Anti-hack
if (! defined('ANTI_HACK')){exit;}

include 'include/hueeffect.php';

// Read effect directory
$effects = array();
$dir=opendir('effects');
while ($file = readdir($dir)){
	if (! preg_match("/^\./",$file) && preg_match('/\.xml/',$file)){
		$effects[] = preg_replace('/\.xml/','',$file);
	}
}
closedir($dir);

// Display available effects
sort($effects);
$descri = array();
echo "<TABLE>";
for ($i=0; @$effects[$i]; $i++){
	$descri[$i] = $HueEffect->getDescription($effects[$i]);
	if (! isset($descri[$i]['name'])){$descri[$i]['name'] = $effects[$i];}
	echo "\n<TR CLASS=radio>";
	echo "<TD><SPAN CLASS=\"ui-icon ui-icon-radio-off\"><INPUT TYPE=radio NAME=efradio ID=".$effects[$i]."></SPAN>";
	echo "<TD CLASS=sname><LABEL FOR=".$effects[$i].">".$descri[$i]['name']."</LABEL>";
	echo "<TD><LABEL FOR=".$effects[$i].">";
	if (isset($descri[$i]['comment'])){echo $descri[$i]['comment'];}
	echo "</LABEL>";
	echo "<TD>&nbsp";
	echo "<TD><SPAN CLASS=\"ui-icon ui-icon-zoomin\" effect=".$effects[$i]." TITLE=\"".$trs["See_details"]."\"></SPAN>";
}
?>
<SCRIPT TYPE="text/javascript" SRC="js/effects.js"></SCRIPT>
<SCRIPT language="javascript">
$('#detail').hide("slide");
effectsTab();
</SCRIPT>

