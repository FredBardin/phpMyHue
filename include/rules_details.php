<?php
// Set the div details for sensor rules
// F. Bardin 2016/03/06
// ------------------------------------
// Anti-hack
if (! defined('ANTI_HACK')){exit;}

include 'include/functions.php';

@$sensorid=$_REQUEST['sensor'];
@$selruleid=$_REQUEST['rule'];
@$advmode=$_REQUEST['advmode']; // Advanced mode (true if supply)

// Load sensors informations
$HueAPI->loadInfo("sensors/$sensorid");
$HueAPI->loadInfo("rules");

$maxelem = 4; // Max allowed conditions or actions

// Display selected sensor rules
$rulesnum=0;
$srules = array();
$a_srules = array();
foreach ($HueAPI->info['rules'] as $ruleid => $rval)
{
	// Look for conditions on selected sensor
	if (preg_match("/\/sensors\/$sensorid/",$rval['conditions']['0']['address'])){
		$rulesnum++;
		$srules[$ruleid] = $rval;
		$a_srules[$ruleid] = $rval['name'];
	}
}
// If no rule : auto select CREATE option, else sort by name
if ($rulesnum == 0){$selruleid="0";}
else {asort($a_srules);}

echo "<INPUT TYPE=hidden ID=sensorid VALUE=$sensorid>";
echo "<TABLE><TR>";
echo "<TD>".$trs["Rule"];
echo "<TD><SELECT ID=srsel>\n";
echo "<OPTION VALUE=''>".$trs["Select"]."</OPTION>\n";
echo "<OPTION VALUE=0";
if ($selruleid == "0"){echo " SELECTED";}
echo ">".$trs["CREATE_NEW_RULE"]."</OPTION>\n";
foreach ($a_srules as $ruleid => $rname){
	echo "<OPTION VALUE=$ruleid";
	if ($selruleid == $ruleid){echo " SELECTED";}
	echo ">$rname</OPTION>\n";
}
echo "</SELECT>\n";

// If rule selected
if(! isset($selruleid) or $selruleid == ""){
	echo "</TABLE>";
} else {
	// Init displayed fields if creation
	if ($selruleid == 0){
		$srules[$selruleid]['name'] = "";
		$srules[$selruleid]['status'] = "enabled";
		$srules[$selruleid]['conditions']['0']['address'] = "";
		$srules[$selruleid]['conditions']['0']['operator'] = "";
		$srules[$selruleid]['conditions']['0']['value'] = "";
		$srules[$selruleid]['actions']['0']['address'] = "";
		$srules[$selruleid]['actions']['0']['method'] = "PUT";
		$srules[$selruleid]['actions']['0']['body'] = "";
	}
	echo "<TR>";
	echo "<TD>".$trs['Name'];
	echo "<TD><INPUT TYPE=text SIZE=30 ID=rulename VALUE=\"".$srules[$selruleid]['name']."\" CLASS=\"ui-corner-all\">";

	echo "<TD>&nbsp;<DIV ID=srradio>";
	echo "<INPUT TYPE=radio NAME=srradio ID=rulon VALUE=enabled";
	if ($srules[$selruleid]['status'] == "enabled"){echo " CHECKED=checked";}
	echo "><LABEL FOR=rulon>".$trs["enabled"]."</LABEL>";
	echo "<INPUT TYPE=radio NAME=srradio ID=ruloff VALUE=disabled";
	if ($srules[$selruleid]['status'] == "disabled"){echo " CHECKED=checked";}
	echo "><LABEL FOR=ruloff>".$trs["disabled"]."</LABEL>";
	echo "</DIV>"; // srradio

	echo "</TABLE>\n";

	// Look for simple settings (depending on sensor type)
	$sensor_found=false;
	unset($simplemode);
	if (! isset($advmode)){ // If advanced mode not asked
		foreach (glob("include/rd_*.php") as $rd_file){
			$rd_sensor = preg_replace("/include\/rd_(.*).php/","$1",$rd_file);
			if ($rd_sensor == $HueAPI->info['sensors'][$sensorid]['type']){
				$simplemode=true;
				include $rd_file;
				break;
			}
		}
	}

	// Advanced settings if no simple settings used
	if (! isset($simplemode)){include 'include/rd_advanced.php';}

	// Management buttons (Add/Update+Delete)
	echo "&nbsp;";
	echo "<BUTTON ID=rupd>";
	if ($selruleid == 0){// Create
		echo $trs["Add"];
	} else {			 // Update and Delete
		echo $trs["Update"];
		echo "</BUTTON>";

		echo "&nbsp;";
		echo "<BUTTON ID=rdel>".$trs["Delete"];
	}
	echo "</BUTTON>";

	// Manage simple/advanced button if needed
	echo "&nbsp;";
	if (isset($advmode)){echo "<BUTTON ID=simplemod>".$trs["Simple_mode"]."</BUTTON>";}
	if (isset($simplemode)){echo "<BUTTON ID=advmod>".$trs["Advanced_mode"]."</BUTTON>";}

	// Empty div for dialog
	echo "<DIV ID=deldialog></DIV>";
}
?>
<SCRIPT>
$("#srsel").selectmenu({width : 'auto'});
$("#srradio").buttonset({width : 'auto'});

$("#rupd, #rdel").button();

// simple/advanced button
$("#simplemod, #advmod").button();

sensorRulesDetail();
</SCRIPT>
