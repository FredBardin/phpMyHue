<?php
// Set the div details for sensor rules
// F. Bardin 2016/03/06
// ------------------------------------
// Anti-hack
if (! defined('ANTI_HACK')){exit;}

include 'include/functions.php';

@$sensorid=$_REQUEST['sensor'];
@$selruleid=$_REQUEST['rule'];

// Load sensors informations
$HueAPI->loadInfo("rules");

$maxelem = 4; // Max allowed conditions or actions

// Display selected sensor rules
$rulesnum=0;
$srules = array();
foreach ($HueAPI->info['rules'] as $ruleid => $rval)
{
	// Look for conditions on selected sensor
	if (preg_match("/\/sensors\/$sensorid/",$rval['conditions']['0']['address'])){
		$rulesnum++;
		$srules[$ruleid] = $rval;
	}
}
// If no rule : auto select CREATE option
if ($rulesnum == 0){$selruleid="0";}

echo "<INPUT TYPE=hidden ID=sensorid VALUE=$sensorid>";
echo "<TABLE><TR>";
echo "<TD>".$trs["Rule"];
echo "<TD><SELECT ID=srsel>\n";
echo "<OPTION VALUE=''>".$trs["Select"]."</OPTION>\n";
echo "<OPTION VALUE=0";
if ($selruleid == "0"){echo " SELECTED";}
echo ">".$trs["CREATE_NEW_RULE"]."</OPTION>\n";
foreach ($srules as $ruleid => $rval){
	echo "<OPTION VALUE=$ruleid";
	if ($selruleid == $ruleid){echo " SELECTED";}
	echo ">".$rval['name']."</OPTION>\n";
}
echo "</SELECT>\n";

//==> Ajouter bouton supprimer si selection d'une regle

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

	// Conditions
	echo "</TABLE>\n";
	echo "<FIELDSET CLASS=\"ui-widget ui-widget-content ui-corner-all\"><LEGEND>".$trs["Conditions"];
	echo "&nbsp;";
	echo "<BUTTON ID=cmvup title=\"".$trs["Move_up"]."\"></BUTTON>";
	echo "<BUTTON ID=cmvdn title=\"".$trs["Move_down"]."\"></BUTTON>";
	echo "<BUTTON ID=cdel title=\"".$trs["Delete"]."\"></BUTTON>";
	echo "<BUTTON ID=cadd title=\"".$trs["Add"]."\"></BUTTON>";
	echo "</LEGEND>\n";
	echo "<TABLE ID=condtable>";
	echo "<TR><TH> <TH>".$trs["Address"]."<TH>".$trs["Operator"]."<TH>".$trs["Value"]."\n";
	foreach ($srules[$selruleid]['conditions'] as $cond => $cval){getCondRow($sensorid, $cond, $cval);}
	echo "</TABLE>";
	echo "</FIELDSET>\n";

	// Actions
	echo "<FIELDSET CLASS=\"ui-widget ui-widget-content ui-corner-all\"><LEGEND>".$trs["Actions"];
	echo "&nbsp;";
	echo "<BUTTON ID=amvup title=\"".$trs["Move_up"]."\"></BUTTON>";
	echo "<BUTTON ID=amvdn title=\"".$trs["Move_down"]."\"></BUTTON>";
	echo "<BUTTON ID=adel title=\"".$trs["Delete"]."\"></BUTTON>";
	echo "<BUTTON ID=aadd title=\"".$trs["Add"]."\"></BUTTON>";
	echo "</LEGEND>\n";
	echo "<TABLE ID=acttable>";
	echo "<TR><TH> <TH>".$trs["Address"]."<TH>".$trs["Method"]."<TH>".$trs["Action"]."\n";
	foreach ($srules[$selruleid]['actions'] as $act => $aval){getActRow($act, $aval);}
	echo "</TABLE>";
	echo "<SPAN CLASS=error>".$trs["method_warning"]."</SPAN>";
	echo "</FIELDSET>\n";

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

	// Empty div for dialog
	echo "<DIV ID=deldialog></DIV>";
}
?>
<SCRIPT>
$("#srsel").selectmenu({width : 'auto'});
$("#srradio").buttonset({width : 'auto'});

// Buttons are disabled on init, they are enable on a row selection
$("#cmvup, #amvup").button({
	icons: {primary: "ui-icon-arrowthick-1-n"},
	text: false,
	disabled: true
});
$("#cmvdn, #amvdn").button({
	icons: {primary: "ui-icon-arrowthick-1-s"},
	text: false,
	disabled: true
});
$("#cdel, #adel").button({
	icons: {primary: "ui-icon-trash"},
	text: false,
	disabled: true
});
// 'Add' buttons
$("#cadd, #aadd").button({
	icons: {primary: "ui-icon-plusthick"},
	text: false
});
$("#rupd, #rdel").button();
sensorRulesDetail();
</SCRIPT>
