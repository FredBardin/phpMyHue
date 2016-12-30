/*-------------------------
 Functions for rules tab
 F. Bardin 05/03/2016
 -------------------------*/

//---------------------------------------
// Globals for rules details tab
//---------------------------------------
var nbcond, nbact; 			// number of rows for condition or action
var nextcondid, nextactid; 	// next id for condition or action
var selcond, selact; 		// selected row for condition or action
var tabdetail = "#"+getCurrentTabsID("#detail");
var rowmaxelem = 4;	// Max number of elements in a row

//=======================================
// Function for rules tab
//=======================================
function rulesTab(){
	scrollCurrentTab("#tabs");

	// Get selector for Div ID of current tab
	var tabrules = "#"+getCurrentTabsID("#tabs");

	// Trigger sensor selection
	$("#tabs td span.ui-icon input").click(function(){
		$(this).change();
	});
	// Select sensor rules when a new selection occurs
	$("#tabs input[type=radio][name=seradio]").change(function(){
		$("#tabs span.ui-icon-arrow-1-e").each(function(){
			$(this).removeClass("ui-icon-arrow-1-e");
			$(this).addClass("ui-icon-radio-off");
			$(this).parent("td").parent("tr").removeClass("ui-state-focus");
		});
		var cbspan = $(this).parent("span");
		cbspan.parent("td").parent("tr").addClass("ui-state-focus");
		cbspan.removeClass("ui-icon-radio-off");
		cbspan.addClass("ui-icon-arrow-1-e");
		// Load detail tab with sensor rules
		var sensorid = $(this).attr("id");
		$(tabdetail).load("details.php?rt=rules&sensor="+sensorid, function(){
			$("#detail").show("slide");
			scrollCurrentTab("#detail");
		});
	});
} // rulesTab

//=======================================
// Functions for rules details tab
//=======================================
//---------------------------------------
// Trigger row selection (advanced mode)
// Select a row for conditions or actions acts as a radio button
// Row selection impacts button enable/disable
//---------------------------------------
function catchRowSelection(objectid){
	$(objectid).change(function() {
		var id=$(this).attr("id");
		var selClass=$(this).attr("class");
		var pref=selClass.substr(0,1); // get 'c' or 'a'
		var buttonList="#"+pref+"del";
		if (! $("#"+id).parent("span").hasClass("cbchecked")){ // if not checked, remove existing checked
			$(tabdetail+" tbody span.cbchecked input."+selClass).prop("checked",false);
			$(tabdetail+" tbody span.cbchecked input."+selClass).parent("span").removeClass("cbchecked");
			// The current row is checked --> Enable buttons
			$(buttonList).button("option", "disabled", false);

			// Stored clicked row and disable up/down button if needed
			var selrow = $(this).parent("span").parent("td").parent("tr").index();
			var tableid;
			if (pref == "c"){
				selcond = selrow;
				tableid = "condtable";
			} else {
				selact  = selrow;
				tableid = "acttable";
			}
			updateUpDownState(tableid);

		} else { // The current row is unchecked --> disable buttons
			buttonList="#"+pref+"mvup, #"+pref+"mvdn, "+buttonList;
			$(buttonList).button("option", "disabled", true);
			if (pref == "c"){selcond = "";}
			else			{selact  = "";}
		}
		$("#"+id).parent("span").toggleClass("cbchecked");
	});
} // catchRowSelection

//---------------------------------------
// Trigger operator change (advanced mode)
//---------------------------------------
function catchSelopeChange(objectid){
	$(objectid).on("selectmenuchange", function (event,val) {
		var condnum = $(this).attr("id").substr(7); // id = "sbcond_"+cond
		var valope = $(this).val();
		var valid = "cval"+condnum;
		if (valope == "dx"){$("#"+valid).hide();}
		else               {$("#"+valid).show();}
	});
} // catchSelopeChange

//--------------------------------------------------
// Set the state (enable/disable) of the tables buttons (advanced mode)
// These buttons are : up, down, delete, add
//--------------------------------------------------
function setButtonsState(){
	// Table conditions buttons
	if (nbcond == 1){
		$(tabdetail+" tbody input.csel").hide(0);
		$("#cmvup, #cmvdn, #cdel").button("option", "disabled", true);
	} else {
		$(tabdetail+" tbody input.csel").filter(":first").show(0);
	}
	if (nbcond == rowmaxelem)	{$("#cadd").button("option", "disabled", true);}
	else						{$("#cadd").button("option", "disabled", false);}

	// Table actions buttons
	if (nbact == 1){
		$(tabdetail+" tbody input.asel").hide(0);
		$("#amvup, #amvdn, #adel").button("option", "disabled", true);
	} else {
		$(tabdetail+" tbody input.asel").filter(":first").show(0);
	}
	if (nbact == rowmaxelem){$("#aadd").button("option", "disabled", true);}
	else					{$("#aadd").button("option", "disabled", false);}
} // setButtonsState

//----------------------------------------------------------
// Update up and down button depending on the selected row (advanced mode)
// parameter : tableid = id of table buttons
//----------------------------------------------------------
function updateUpDownState(tableid){
	var selrow, nbrow, pref;
	// Init values
	if (tableid == "condtable"){
		selrow = selcond;
		nbrow = nbcond;
		pref = "c";
	} else {
		selrow = selact;
		nbrow = nbact;
		pref = "a";
	}
	if (selrow == 1){				// if 1st row : disable up button
		$("#"+pref+"mvup").button("option","disabled", true);
		if (nbrow > 1){
			$("#"+pref+"mvdn").button("option","disabled", false);
		}
	} else {
		$("#"+pref+"mvup").button("option","disabled", false);
		if (selrow == nbrow){	// if last row : disable down button
			$("#"+pref+"mvdn").button("option","disabled", true);
		} else {
			$("#"+pref+"mvdn").button("option","disabled", false);
		}
	}
} // updateUpDownState

//---------------------------------------
// Initialize the rows count with displayed rule (advanced mode)
//---------------------------------------
function InitRowsCount(){
	nbcond = $(tabdetail+" tbody input.csel").length;
	nbact = $(tabdetail+" tbody input.asel").length;
	nextcondid = nbcond;
	nextactid = nbact;
	setButtonsState();
} // InitRowsCount

//---------------------------------------
// Function for 'sensor' rules detail tab (advanced mode included)
//---------------------------------------
function sensorRulesDetail(){
	InitRowsCount();	

	// Trigger rule display on selection
	$("#srsel").on("selectmenuchange", function(){
		var sensorid = $("#sensorid").val();
		var ruleid = $(this).val();
		$(tabdetail).load("details.php?rt=rules&nh=&sensor="+sensorid+"&rule="+ruleid, function(){
			scrollCurrentTab("#detail");
		});
		// re-Initialize rows counters and buttons
		InitRowsCount();	
	});

	// Trigger row selection and operator change
	$(tabdetail+" tbody input.csel,"+tabdetail+" tbody input.asel").each(function(){
		catchRowSelection(this);
	});
	$(tabdetail+" .selope").each(function(){
		catchSelopeChange(this);
	});

	// Manage buttons trigger for tables
	$("#cmvup").click(function(){mvUpSelectedDetail("condtable");});
	$("#cmvdn").click(function(){mvDownSelectedDetail("condtable");});
	$("#cdel").click(function(){delSelectedDetail("condtable");});
	$("#cadd").click(function(){addCond();});
	$("#amvup").click(function(){mvUpSelectedDetail("acttable");});
	$("#amvdn").click(function(){mvDownSelectedDetail("acttable");});
	$("#adel").click(function(){delSelectedDetail("acttable");});
	$("#aadd").click(function(){addAct();});

	// Manage update of rule
	$("#rupd").click(function(){updateRule();});
	$("#rdel").click(function(){deleteRule();});

	// Manage switch between simple and advanced mode if existing
	$("#simplemod").click(function(){switchToAdvMode(false);});
	$("#advmod").click(function(){switchToAdvMode(true)});;
} // sensorRulesDetail

//-----------------------------------------------------
// Move up a table row (advanced mode)
// parameter :
// - tableid = id the table where the row is to move up
//-----------------------------------------------------
function mvUpSelectedDetail(tableid){
	var rowcontent = $("#"+tableid+" tbody span.cbchecked").parent("TD").parent("TR").detach();
	var rowindex;
	if (tableid == "condtable")	{
		rowindex = selcond - 1;
		selcond--;
	} else {
		rowindex = selact  - 1;
		selact--;
	}
	$("#"+tableid+" tr").eq(rowindex).before(rowcontent);
	updateUpDownState(tableid);
} // mvUpSelectedDetail

//-----------------------------------------------------
// Move down a table row (advanced mode)
// parameter :
// - tableid = id the table where the row is to move down
//-----------------------------------------------------
function mvDownSelectedDetail(tableid){
	var rowcontent = $("#"+tableid+" tbody span.cbchecked").parent("TD").parent("TR").detach();
	var rowindex;
	if (tableid == "condtable")	{
		rowindex = selcond;
		selcond++;
	} else {
		rowindex = selact;
		selact++;
	}
	$("#"+tableid+" tr").eq(rowindex).after(rowcontent);
	updateUpDownState(tableid);
} // mvDownSelectedDetail

//-----------------------------------------------------
// Delete selected row (advanced mode)
// parameters :
// - tableid = id the table where the row is to delete
//-----------------------------------------------------
function delSelectedDetail(tableid){
	$("#"+tableid+" tbody span.cbchecked").parent("TD").parent("TR").each(function(){
		$(this).remove();
		var pref;
		if (tableid == "condtable"){
			nbcond--;
			pref = "c";
		} else {
			nbact--;
			pref = "a";
		}
		// Row deleted = no selection --> disable buttions
		var buttonList="#"+pref+"mvup, #"+pref+"mvdn, #"+pref+"del";
		$(buttonList).button("option", "disabled", true);
		setButtonsState();
	});	
} // delSelectedDetail

//-----------------------------------------------------
// Add new condition row (advanced mode)
//-----------------------------------------------------
function addCond(){
	var sensorid = $("#sensorid").val();
	$("#condtable").append("<TR></TR>");
	$("#condtable tr:last").load('main.php?rt=addcond&sensorid='+sensorid+'&cond='+nextcondid, function(){
		$("#condcb_"+nextcondid).each(function(){catchRowSelection(this);});
		$("#sbcond_"+nextcondid).each(function(){catchSelopeChange(this);});
		nbcond++;
		nextcondid++;
		setButtonsState();
	});
} // addCond

//-----------------------------------------------------
// Add new action row (advanced mode)
//-----------------------------------------------------
function addAct(){
	$("#acttable").append('<TR></TR>');
	$("#acttable tr:last").load('main.php?rt=addact&act='+nextactid, function(){
		$("#actcb_"+nextactid).each(function(){catchRowSelection(this);});
		nbact++;
		nextactid++;
		setButtonsState();
	});
} // addAct

//-----------------------------------------------------
// Update rule with current display
// Remark : call getConditionsJson and getActionJson from each specific display
// The specific display are in lib and named rd_<display>.php
//-----------------------------------------------------
function updateRule(){
	// Get values
	var sensorid = $("#sensorid").val();
	var ruleid = $("#srsel").val();
	var rule_name = $("#rulename").val();
	var rule_status = $("#srradio [name=srradio]:checked").val();

	// Get conditions
	var cond = getConditionsJson(sensorid);
//msg("cond="+cond);
	
	// Get actions
	var act = getActionsJson();
//msg("act="+act);

	// Create json string only if condition and action exist
	if (cond != "" && act != ""){
		var cmdjs = "&cmdjs={";
		cmdjs += '"name":"'+rule_name+'","status":"'+rule_status+'",';
		cmdjs += '"conditions":['+cond+'],';
		cmdjs += '"actions":['+act+']';
		cmdjs += "}";

		// Send request
		var action = "rules";
		var method = "&method="; // Create by default
		if (ruleid == "0"){ // Create
			method += "POST";
		} else { 			// Update
			action += "/"+ruleid;
			method += "PUT";
		}

		$.getJSON('hueapi_cmd.php?action='+action+cmdjs+method, (function(jsmsg){
			if (processReturnMsg(jsmsg)){
				var successMsg;
				if (ruleid == "0"){
					ruleid = jsmsg[0].success.id;
					successMsg = trs.Created;
				} else {
					successMsg = trs.Updated;
				}
				msg(trs.Rule+' '+ruleid+' "'+rule_name+'" '+successMsg);

				// re-display tab
				$(tabdetail).load("details.php?rt=rules&nh=&sensor="+sensorid+"&rule="+ruleid, function(){
					scrollCurrentTab("#detail");
				});
				// re-Initialize rows counters and buttons
				InitRowsCount();	
			} 
		}));
	}
} // updateRule

//-----------------------------------------------------
// Update rule with current display
//-----------------------------------------------------
function deleteRule(){
	var ruleid = $("#srsel").val();
	var action = "rules/"+ruleid;
	var method = "&method=DELETE";

	// Confirmation required before delete
    $("#deldialog").dialog({
	  title : trs.Delete_the_rule,
      resizable: true,
      modal: true,
	  open: function(event, ui){ // Change close button title
			$('[aria-describedby="deldialog"] .ui-dialog-titlebar-close').attr('title', trs.Close)
	  },
      buttons: [
		{	// Delete button
	    	text:trs.Delete,
			click: function() {
		  		$.getJSON('hueapi_cmd.php?action='+action+method, (function(jsmsg){
					if (processReturnMsg(jsmsg,trs.Rule+' '+trs.Deleted)){
						var sensorid = $("#sensorid").val();
						$(tabdetail).load("details.php?rt=rules&nh=&sensor="+sensorid, function(){
							scrollCurrentTab("#detail");
						});
					}
		  		}));
          		$(this).dialog("close");
       		}
		},
		{	// Cancel button
			text: trs.Cancel,
        	click: function() {
          		$(this).dialog("close");
        	}
		}
		]
	});
} // deleteRule

//-----------------------------------------------------
// Switch to simple or advanced mode
//-----------------------------------------------------
function switchToAdvMode(advmode){
	var sensorid = $("#sensorid").val();
	var ruleid = $("#srsel").val();

	var advstr= "";
	if (advmode){advstr = "&advmode="+advmode;}

	$(tabdetail).load("details.php?rt=rules&nh=&sensor="+sensorid+"&rule="+ruleid+advstr, function(){
		scrollCurrentTab("#detail");
	});
	// re-Initialize rows counters and buttons
	InitRowsCount();	
} // switchToAdvMode
