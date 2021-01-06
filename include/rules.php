<?php
// Manage Rules
// All rules are for a sensor, 1st screen is the sensors list.
// F. Bardin 05/03/2016
//----------------------------
// Anti-hack
if (! defined('ANTI_HACK')){exit;}

// Load sensors informations
$HueAPI->loadInfo("sensors");
$HueAPI->loadNameIndex("sensors");

?>
<SCRIPT language="javascript">
$('#detail').hide("slide");
</SCRIPT>
<?php

// Display sensors
echo "<TABLE>";
echo "<TR><TD><TH>".$trs["Sensor"]."<TH>".$trs["Type"];
if (isset($HueAPI->info['sensorsnames'])){
	foreach ($HueAPI->info['sensorsnames'] as $sname => $sensorid){
		echo "\n<TR CLASS=radio>";
		echo "<TD><SPAN CLASS=\"ui-icon ui-icon-radio-off\"><INPUT TYPE=radio NAME=seradio ID=$sensorid></SPAN>";
		echo "<TD CLASS=sname><LABEL FOR=$sensorid>$sname</LABEL>";
		echo "<TD><LABEL FOR=$sensorid>&nbsp;".$HueAPI->info['sensors'][$sensorid]['type']."</LABEL>";
	}
}
echo "</TABLE>";
?>
<SCRIPT TYPE="text/javascript" SRC="js/rules.js"></SCRIPT>
<SCRIPT language="javascript">
rulesTab();
</SCRIPT>

