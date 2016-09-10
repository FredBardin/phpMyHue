<?php
// =====================================================
// Rules details for simplified ZGPSwitch management
// Included from rules_details.php
// Include js functions getConditionsJson and getActionsJson which are used during an update.
// These functions are specific to each rules display (simple or advanced)
// -----------------------------------------------------
// F. Bardin 2016/08/26
// =====================================================
// Anti-hack
if (! defined('ANTI_HACK')){exit;}

// Get rule conditions
// For Hue Tap : 
// - 1 condition to detect last pressed button (operator 'eq')
// - 1 condition to detect that previous conditon has changed (operator 'dx')
// Look for value of pressed button
$butval = "";
foreach ($srules[$selruleid]['conditions'] as $cond => $cval){
	if ($cval['operator'] == "eq"){
		$butval = $cval['value'];
		break;
	}
}

// Get rule actions
// For Hue Tap : managed action is scene display or switch off lights (only 1 action allowed to stay simple, several actions wouldn't be very logical)
$selsceneid="";
$selgroupid="";
$htmode = "sc";
foreach ($srules[$selruleid]['actions'] as $act => $aval){
	if (isset($aval['body']['scene'])){
		$htmode = "sc";
		$selsceneid = $aval['body']['scene'];
	} elseif (isset($aval['body']['on'])){
		$selgroupid = preg_replace("/\/groups\/(.*)\/action/","$1", $aval['address']);
		if ($aval['body']['on']==""){$htmode = "off";}
		else						{$htmode = "on";}
	}
}

echo "<FIELDSET CLASS=\"ui-widget-content ui-corner-all\">";
echo "<TABLE><TR>";
echo "<TD>";

// Hue Tap button selection : Event value to button value is 34=1, 16=2, 17=3, 18=4
echo "<SELECT ID=selbutton>";
echo "<OPTION>".$trs["Select"]." ".$trs["Button"]."</OPTION>";
echo "<OPTION VALUE=34";
if ($butval == "34"){echo " SELECTED";}
echo ">".$trs["Button"]." 1</OPTION>";
echo "<OPTION VALUE=16";
if ($butval == "16"){echo " SELECTED";}
echo ">".$trs["Button"]." 2</OPTION>";
echo "<OPTION VALUE=17";
if ($butval == "17"){echo " SELECTED";}
echo ">".$trs["Button"]." 3</OPTION>";
echo "<OPTION VALUE=18";
if ($butval == "18"){echo " SELECTED";}
echo ">".$trs["Button"]." 4</OPTION>";
echo "</SELECT>";

// Action : Scene or lights group on/off
echo "<TD>";
echo "<DIV ID=htradio>";
echo "<INPUT TYPE=radio NAME=htradio ID=scenemode VALUE=sc";
if ($htmode == "sc"){echo " CHECKED=checked";}
echo "><LABEL FOR=scenemode>".$trs["Scenes"]."</LABEL>";
echo "<INPUT TYPE=radio NAME=htradio ID=onmode VALUE=on";
if ($htmode == "on"){echo " CHECKED=checked";}
echo "><LABEL FOR=onmode>".$trs["Lights"]." ".$trs["On"]."</LABEL>";
echo "<INPUT TYPE=radio NAME=htradio ID=offmode VALUE=off";
if ($htmode == "off"){echo " CHECKED=checked";}
echo "><LABEL FOR=offmode>".$trs["Lights"]." ".$trs["Off"]."</LABEL>";
echo "</DIV>"; // htradio

echo "<TR>";
echo "<TD>";
echo "<TD>";
// Scenes list
$HueAPI->loadInfo("scenes");
// Create id->name array
$a_sname="";
foreach ($HueAPI->info['scenes'] as $sceneid => $sval){
	$a_sname[$sceneid] = $sval['name'];
}
asort($a_sname);
// Display scenes list
echo "<SELECT ID=selhtsc>";
echo "<OPTION>".$trs["Select"]." ".$trs["Scene"]."</OPTION>";
foreach ($a_sname as $sceneid => $sname){
	echo "<OPTION VALUE=$sceneid";
	if ($sceneid == $selsceneid){echo " SELECTED";}
	echo ">$sname (".count($HueAPI->info['scenes'][$sceneid]['lights'])." ".$trs["Lights"].")";
	echo "</OPTION>";
}
echo "</SELECT>";

//  On/Off list (all+groups)
$HueAPI->loadInfo("groups");
createGroupList($selgroupid, "selhton", " ".$trs["On"]);
createGroupList($selgroupid, "selhtoff", " ".$trs["Off"]);

echo "</TABLE>";
echo "</FIELDSET>";

//------------------------------------------------------
// Create a group list for a defined action
//------------------------------------------------------
function createGroupList($gid, $selid, $label_suffix){
	global $trs,$HueAPI;

	echo "<SELECT ID=$selid>";
	echo "<OPTION VALUE=0>".$trs["All"]."$label_suffix</OPTION>";
	foreach ($HueAPI->info['groups'] as $gnum => $gval){ 
		echo "<OPTION VALUE=$gnum";
		if ($gid == $gnum){echo " SELECTED";}
		echo ">".$gval['name']."$label_suffix</OPTION>";
	}
	echo "</SELECT>";
} // createGroupList

?>
<SCRIPT>
$("#selbutton").selectmenu({width : 'auto'});
$("#htradio").buttonset({width : 'auto'});
$("#selhtsc").selectmenu({width : 'auto'}).selectmenu("menuWidget").addClass("overflow");
$("#selhton").selectmenu({width : 'auto'});
$("#selhtoff").selectmenu({width : 'auto'});

// Hide scene or on/off selectmenu at init
switch ($("#htradio [name=htradio]:checked").val()){
	case "sc" :
		$("#selhton").selectmenu("widget").hide();
		$("#selhtoff").selectmenu("widget").hide();
		break;
	case "on" :
		$("#selhtsc").selectmenu("widget").hide();
		$("#selhtoff").selectmenu("widget").hide();
		break;
	case "off" :
		$("#selhtsc").selectmenu("widget").hide();
		$("#selhton").selectmenu("widget").hide();
		break;
}

// Trigger change for action and hide/show selectmenu
$("#htradio [name=htradio]").change(function(){
	switch ($("#htradio [name=htradio]:checked").val()){
		case "sc" :
			$("#selhton").selectmenu("widget").hide();
			$("#selhtoff").selectmenu("widget").hide();
			$("#selhtsc").selectmenu("widget").show();
			break;
		case "on" :
			$("#selhtsc").selectmenu("widget").hide();
			$("#selhtoff").selectmenu("widget").hide();
			$("#selhton").selectmenu("widget").show();
			break;
		case "off" :
			$("#selhtsc").selectmenu("widget").hide();
			$("#selhton").selectmenu("widget").hide();
			$("#selhtoff").selectmenu("widget").show();
			break;
	}
});

//-----------------------------------------------------
// Get conditions json from displayed screen
// (Specific to each display advanced or simplified)
// Parameter : sensorid (required)
// Return : json string for rule conditions
//-----------------------------------------------------
function getConditionsJson(sensorid){
	var cond,value;

	value = $("#selbutton").val();
	cond = '{"address":"/sensors/'+sensorid+'/state/buttonevent","operator":"eq","value":"'+value+'"},';
	cond += '{"address":"/sensors/'+sensorid+'/state/lastupdated","operator":"dx"}';

	return cond;
} // getConditionsJson

//-----------------------------------------------------
// Get actions json from displayed screen
// (Specific to each display advanced or simplified)
// Return : json string for rule actions
//-----------------------------------------------------
function getActionsJson(){
	var act,body,gid;

	switch ($("#htradio [name=htradio]:checked").val()){
		case "sc" :
			gid = 0;
			var sceneid = $("#selhtsc").val();
			body = '"scene":"'+sceneid+'"';
			break;
		case "on" :
			gid = $("#selhton").val();
			body = '"on":true';
			break;
		case "off" :
			gid = $("#selhtoff").val();
			body = '"on":false';
			break;
	}

	act = '{"address":"/groups/'+gid+'/action","method":"PUT","body":{'+body+'}}';
	return act;
} // getActionsJson
</SCRIPT>
