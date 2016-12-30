<?php
// =====================================================
// Rules details for advanced management (=complete)
// Included from rules_details.php
// Include js functions getConditionsJson and getActionsJson which are used during an update.
// These functions are specific to each rules display (simple or advanced)
// -----------------------------------------------------
// F. Bardin 2016/08/30
// =====================================================
// Anti-hack
if (! defined('ANTI_HACK')){exit;}

// Conditions
echo "<FIELDSET CLASS=\"ui-widget-content ui-corner-all\"><LEGEND>".$trs["Conditions"];
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
echo "<FIELDSET CLASS=\"ui-widget-content ui-corner-all\"><LEGEND>".$trs["Actions"];
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

?>
<SCRIPT>
// Buttons up/down/del are disabled on init, they are enable on a row selection
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

//-----------------------------------------------------
// Get conditions json from displayed screen
// (Specific to each display advanced or simplified)
// Parameter : sensorid (required)
// Return : json string for rule conditions
//-----------------------------------------------------
function getConditionsJson(sensorid){
	var tdnum;
	var cond,address,operator,value;
	cond = "";
	$("#condtable tr").each(function(){
		address = "";
		tdnum = 0;
		$(this).find("td").each(function(){
			tdnum++;
			switch(tdnum){
				case 1 : // Check box = ignored
					break;
				case 2 : // Sensor address
					address = $(this).find("input").val();
					break;
				case 3 : // Operator
					operator = $(this).find("select").val();
					break;
				case 4 : // Value
					value = $(this).find("input").val();
					break;
			}
		});
		if (address != "" && operator != ""){
			if (cond != ""){cond += ",";}
			cond += '{"address":"/sensors/'+sensorid+'/'+address+'","operator":"'+operator+'"';
			if (operator != "dx"){
				cond += ',"value":"'+value+'"';
			}
			cond += '}';
		}
	});

	return cond;
} // getConditionsJson

//-----------------------------------------------------
// Get actions json from displayed screen
// (Specific to each display advanced or simplified)
// Return : json string for rule actions
//-----------------------------------------------------
function getActionsJson(){
	var act,method,body;
	act = "";
	$("#acttable tr").each(function(){
		address = "";
		tdnum = 0;
		$(this).find("td").each(function(){
			tdnum++;
			switch(tdnum){
				case 1 : // Check box = ignored
					break;
				case 2 : // Action address
					address = $(this).find("input").val();
					break;
				case 3 : // Method
					method = $(this).find("select").val();
					break;
				case 4 : // Json body send as action
					body = $(this).find("input").val();
					break;
			}
		});
		if (address != "" && method != ""){
			if (act != ""){act += ",";}
			act += '{"address":"'+address+'","method":"'+method+'"';
			act += ',"body":{'+body+'}';
			act += '}';
		}
	});

	return act;
} // getActionsJson
</SCRIPT>
