// Javascript Functions for lights tab in phpMyHue
// F. Bardin 2015/02/10
// ----------------------------------------------------------------------
// 2016/12/28 : Correct group description when not a lightgroup
// 2017/05/28 : Some corrections in particular configuration
// 2017/12/30 : Immediate display update for color and brightness when a change occurs
// 2017/01/02 : Optimize immediate display and correct problems on some brightness changes
// ----------------------------------------------------------------------

/*====================================
  Tab lights functions
=====================================*/
//-------------------------
// Function for lights tab
//-------------------------
function lightsTab(){
	scrollCurrentTab('#tabs');

	// Get selector for Div ID of current tab
	var tablights = "#"+getCurrentTabsID('#tabs');

	// Manage lights list events
	lightsList(tablights)

	// Load detail tab with lights details
	$("#"+getCurrentTabsID('#detail')).load('details.php?rt=lights');
} // lightsTab

/*====================================
  Lights tab detail functions
=====================================*/
//-----------------------------------------------------------------------
// Load selected element into detail tab
// if detail exists show tab, else hide
// if all or a group is selected, only load this element not its content
//-----------------------------------------------------------------------
function loadSelectedLightsDetail(tablights){
	var tabdetail = "#"+getCurrentTabsID('#detail');
	var selcount = 0;	// Element count
	var lasttype = "";	// Last element type
	var lastnum = "";	// Last element numid
	var lastname = "";	// Last element name
	var selstring = "";	// Displayed selection string
	var lamponly = true;// True/false if only lamp selection
	var grponly = true;	// True/false if only group selection
	var recordlight = true; // True if checked lamp have to be recorded in selection (false when their group is selected)
	var a_lnum = new Array(); // Array of selected lamp numid to avoid multiple selection of same lamp if it exists in several groups


	// Select all checkboxes
	$(tablights+' table input[type=checkbox]').each(function(){
		if ($(this).prop('checked')){
			var elemid = $(this).attr('id');		
			var num = "";
			var name = "";

			// Identify the row type and construct the selection string
			if (elemid == 'cb_all'){ // ALL
				selstring = ', <SPAN type=all>'+trs.All_lamps+'</SPAN>';
				selcount++;
				lamponly = false;
				grponly = false;
				lasttype = 'all';
				return false;

			} else {

				if ($(this).attr('class') == 'grp'){ // Group
					num = $(this).attr('gnum');
					name = $(tablights+' tbody tr.grp[gnum='+num+'] td.label').text();
					lamponly = false;
					recordlight = false; // unallow light recording until the next group is reached
					selstring += ', <SPAN type=group num='+num+'>'+trs.group+' "'+name+'"</SPAN>';
					selcount++;
					lasttype = 'group';
					lastnum = num;
					lastname = name;
					if (num == 'other'){grponly = false;} // other is a pseudo-group not a real one

				} else { // Light
					if (recordlight){ // if current group not selected : record light
						num = $(this).attr('lnum'); 
						name = $(tablights+' tbody tr.grp'+$(this).attr('gnum')+'[lnum='+num+'] td.label').text();

						// Check if lamp numid not already selected
						var notsel = true;
						var i;
						for (i=0; i < a_lnum.length; i++){
							if (a_lnum[i] == num){
								notsel = false;
								break;
							}
						}

						// if lamp not already selected
						if (notsel){
							grponly = false;
							a_lnum[a_lnum.length] = num;
							selstring += ', <SPAN type=light num='+num+'>"'+name+'"</SPAN>';
							selcount++;
					    	lasttype = 'light';
							lastnum = num;
							lastname = name;
						}
					}
				}
			}
		} else { // if group, authorize lights recording
			if ($(this).attr('class') == 'grp'){recordlight = true;} 
		}
	});

	// Update detail tab display
	if (selcount > 0){
		// Initialize to defaults
		$('#detail').show("slide"); 		// Show tab
		$('#selname').hide(); 				// Hide name selection
		$('#transset').hide(); 				// Hide color settings transfert
		$('#descri').accordion('option','active',false); // Close description
		$('#descri').hide(); 				// Hide description
		$('#brislider').val(0); 			// Reset brightness
		updateColorPicker("",0,'#ffffff'); // Set displayed color to white

		// Show group management only if all elements are of the same type
		if (lamponly != grponly){
			$('#grpmgmt').show();

			if (lamponly){ // Show lamp options
				$('#grplegend').text(trs.Fill_a_group_with_selected_lamps);
				$('#grplightopt').show();
				$('#grpopt').hide();
			}
			else { // Show group option
				$('#grplegend').text(trs.Delete_selected_groups);
				$('#grplightopt').hide();
				$('#grpopt').show();
			}
		}
	   	else {$('#grpmgmt').hide();}

		// If only 1 element and not all lamps : name can be changed if not 'other' group
		if (selcount == 1 && lasttype != 'all'){
			if (lastnum != 'other'){ // if not group other : display change name
				var action = "";
				selstring = '<SPAN type='+lasttype+' num='+lastnum+'>';
				if (lasttype == 'group'){
					selstring += trs.Group;
					action = 'groups/'+lastnum;
				} else {
					selstring += trs.Light;
					action = 'lights/'+lastnum;
				}
				selstring += ' : </SPAN>';
			
				// Display change name controls
				$('#elemname').val(lastname);
				$('#selname').show();

				// Display transfert color settings if light
				if (lasttype == 'light'){
					$('#transset').show();
				}

				// Display informations
				$.getJSON('hueapi_cmd.php?action='+action, function(info){
					var descri = lasttype.charAt(0).toUpperCase()+lasttype.slice(1)+' id: '+lastnum;
					descri += '<BR>'+trs.Type+': '+info.type;
					if (lasttype == 'light' || info.type == 'Luminaire')
					{
						descri += '<BR>'+trs.Model_id+': '+info.modelid;
					}
				 	if (lasttype == 'light'){
						descri += '&nbsp;&nbsp;&nbsp;'+trs.Unique_id+': '+info.uniqueid;
						descri += '<BR>'+trs.Software_version+': '+info.swversion;
						if (info.state.effect != 'none'){
							descri += '<BR>'+trs.Effect+': '+info.state.effect;
						}
						// Set brightness
						$('#brislider').val(info.state.bri);

						// Set color
						updateColorPicker(tablights,lastnum);

					} else {
						if (info.action.effect != 'none'){
							descri += '<BR>'+trs.Effect+': '+info.action.effect;
						}
					}
					$('#detdescri').html(descri);
				 });
				 $('#descri').show();

			} else { // Normal display
				selstring = selstring.substr(2);
				selstring = trs.Element+' : '+selstring;
			}
		} else { // if several elements : normal display
			selstring = selstring.substr(2);
			selstring = trs.Elements+' : '+selstring;
		}
	} else {
		$('#detail').hide("slide");
		$('#descri').accordion('option','active',false);
	}

	$(tabdetail+' #sellist').html(selstring);
} // loadSelectedLightsDetail

//---------------------------------------
// Execute action from lights detail tab
// Parameters : action [,xy color value]
//
// xy if only use for 'color' action
//---------------------------------------
function lightsDetailAction(tabaction,xy){
	var type = "";
	var num = "";
	var action = "";
	var actionsup = "";
	var cmdjs = "";
	var method = "";
	var successmsg = "";
	var tablights = "#"+getCurrentTabsID('#tabs');

	// Read each selected element to process action
	$('#sellist span').each(function(){
		type = $(this).attr('type');
		num = $(this).attr('num');
		action = '';
		actionsup = '';

		// Set action to execute depending on element type
		switch(type){
			case 'all' :
				action='groups/0/action';
				break;

			case 'group' :
				if (num != 'other'){
					action='groups/'+num;
					actionsup='/action';
				}
				else {action=num;} // Trick for hueapi_cmd.php and pseudo group 'other'
				break;

			case 'light' :
				action='lights/'+num;
				actionsup='/state';
				break;
		}

		// Set bridge content to update depending on tab detail action
		switch(tabaction){
			case 'updname' :
				var name = $('#elemname').val();
				cmdjs = '"name":"'+encodeURIComponent(name)+'"';

				// Update name in tab lights (!! update occurs regardless of possible error)
				if (type == 'light'){
					$(tablights+' table label[lnum='+num+']').text(name);
				} else {
					$(tablights+' table label[gnum='+num+']').text(name);
				}
				successmsg = trs.Name_updated;
				break;

			case 'bri' :
				action += actionsup;
				cmdjs = '"bri":'+$('#brislider').val();
				break;

			case 'blink1' :
				action += actionsup;
				cmdjs = '"alert":"select"';
				break;
			case 'blink30s' :
				action += actionsup;
				cmdjs = '"alert":"lselect"';
				break;
			case 'blinkoff' :
				action += actionsup;
				cmdjs = '"alert":"none"';
				successmsg = trs.Blink_stopped;
				break;
			case 'colorloop' :
				action += actionsup;
				cmdjs = '"effect":"colorloop"';
				successmsg = trs.Color_Loop_started;
				break;
			case 'colorloopoff' :
				action += actionsup;
				cmdjs = '"effect":"none"';
				successmsg = trs.Color_Loop_stopped;
				break;

			case 'color' :
				action += actionsup;
				cmdjs = '&cmdjs='+JSON.stringify(xy);
				break;

			case 'grpassign' : // only execute after end of loop
				cmdjs += ',"'+num+'"';
				action = "";
				break;

			case 'delgrp' :
				method = '&method=DELETE';
				successmsg = trs.Group+" "+$('#elemname').val()+" "+trs.Deleted;
				break;

			default : // do nothing
				action = "";
				break;
		}
		if (action != ""){ // Send action and return result into msg box (!! no error management --> delegated to bridge)
			if (cmdjs != "" && tabaction != 'color'){cmdjs = '&cmdjs={'+cmdjs+'}';}

			// Store current value to use in load callback below (else only get the last one)
			var curtype = (type);
			var curnum = (num);

			$.getJSON('hueapi_cmd.php?action='+action+cmdjs+method, (function(jsmsg){
				if (processReturnMsg(jsmsg,successmsg)){
					switch(tabaction){
						case 'delgrp' :	// reload lights tabs
							$("#tabs").tabs('load',0);
							break;

						case 'bri' : // reload updated lamps
						case 'color' :
							if (curtype == 'light'){ // only 1 light
								$(tablights+' table a.switch[lnum='+curnum+']').load('main.php?rt=display&lnum='+curnum, function(data){
									updateColorPicker(tablights, curnum);
								});
							} else { // update group
								var searchgroup = '';
								var lnum = 0;
								if (curtype != 'all'){searchgroup = ' tr.grp'+curnum;}
								$(tablights+' table'+searchgroup+' a.switch').each(function(){
									lnum = $(this).attr('lnum');
									$(this).load('main.php?rt=display&lnum='+lnum, function(data){
										updateColorPicker(tablights, lnum);
									});
								});
							}
							break;
					}
				} else {
					// Reset color and brightness if error
					switch(tabaction){
						case 'bri' :
						case 'color' :
							$('#brislider').val(0);
							updateColorPicker("",0,'#ffffff');
					}
				}
			}));
		}
	});

	//== Group assign/create executed after reading all lamp
	if (tabaction == 'grpassign'){
		var valsel = $('#assigngrp').val();
		var newgrp = $('#newgrp').val();
		var successmsg = trs.Group+" ";

		action = 'groups';
		cmdjs = '"lights":['+cmdjs.substr(1)+']';

		if (newgrp != ""){ // Create new group with selection
			method = '&method=POST';
			cmdjs = '&cmdjs={"name":"'+newgrp+'",'+cmdjs+'}';
			successmsg += newgrp+" "+trs.Created;
		} else {          // Update lamp of selected group
			if (valsel != 'other'){
				action += '/'+valsel;
				cmdjs = '&cmdjs={'+cmdjs+'}';
				successmsg += $('#assigngrp option[value='+valsel+']').text()+" "+trs.Updated;
			} else { // no action if no selection
				action = '';
			}
		}
		if (action != ''){
			$.getJSON('hueapi_cmd.php?action='+action+cmdjs+method, function(jsmsg){
				if (processReturnMsg(jsmsg,successmsg)){
					$("#tabs").tabs('load',0);
				}
			});
		}
	}
	
	//== Copy actions
	if (tabaction == 'tsexec'){
		var valtarget = $('#tssell').val();
		var actionselected = 'lights/'+num;
		var actiontargeted = 'lights/'+valtarget;
		actionsup='/state';

		if (valtarget != 'none' && valtarget != num){ // if selection not equal current light
			var typaction = $('#tsradio [name=tsradio]:checked').val();
			var lselected, ltargeted;
					
			function cleanstate(stateobj){ // Clean state properties : keep copyable only
				stateobj.on = true;	// Light must be on to accept color change
				delete stateobj.alert;
				delete stateobj.reachable;
				delete stateobj.effect;
				delete stateobj.colormode;
				// Always use xy to switch color because it is also the used parameters to display color.
				// When a color update occurs with a mode, the bridge computes values for the other modes but it takes some times.
				// During the calculation time, values for other mode are not accurates, only the updated values are correct.
				delete stateobj.hue;
				delete stateobj.sat;
				delete stateobj.ct;

				return stateobj;
			} // cleanstate

			$.getJSON('hueapi_cmd.php?action='+actionselected, function(infosel){// Get selected
				lselected = cleanstate(infosel.state);

				$.getJSON('hueapi_cmd.php?action='+actiontargeted, function(infotgt){// Get targeted
					ltargeted = cleanstate(infotgt.state);

					switch (typaction){ // Execture action
						case 'cpto' : // Copy selected to targeted
							$.getJSON('hueapi_cmd.php?action='+actiontargeted+actionsup+'&cmdjs='+JSON.stringify(lselected),
								function(jsmsg){
									if (processReturnMsg(jsmsg)){
										$(tablights+' a.switch[lnum='+valtarget+']').load('main.php?rt=display&lnum='+valtarget);
									}
							});
							break;
						case 'cpfrom' : // Copy targeted to selected
							$.getJSON('hueapi_cmd.php?action='+actionselected+actionsup+'&cmdjs='+JSON.stringify(ltargeted),
								function(jsmsg){
									if (processReturnMsg(jsmsg)){
										$(tablights+' a.switch[lnum='+num+']').load('main.php?rt=display&lnum='+num, function(data){
											updateColorPicker(tablights, num, "", true);
										});
									}
							});
							break;
						case 'swwith' : // Switch selected with targeted = copy to + copy from
							$.getJSON('hueapi_cmd.php?action='+actiontargeted+actionsup+'&cmdjs='+JSON.stringify(lselected),
								function(ret1){
									if (processReturnMsg(ret1)){
										$.getJSON('hueapi_cmd.php?action='+actionselected+actionsup+'&cmdjs='+JSON.stringify(ltargeted),
											function(ret2){
												$(tablights+' a.switch[lnum='+valtarget+']').load('main.php?rt=display&lnum='+valtarget);
												if (processReturnMsg(ret2)){
													$(tablights+' a.switch[lnum='+num+']').load('main.php?rt=display&lnum='+num, function(data){
														updateColorPicker(tablights, num, "", true);
													});
												}
										});
									}
							});
							break
					}
				});
			});
		}
	}

} // lightsDetailAction

//---------------------------------
// Functions for lights detail tab
//---------------------------------
function lightsDetail(){
	// Change name (appears only if 1 element)
    $('#updname').click(function(){lightsDetailAction('updname');});

	// Share color setting
    $('#tsexec').click(function(){lightsDetailAction('tsexec');});

	// Set brightness
	$('#brislider').change(function(){lightsDetailAction('bri');});

	// Alert
	$('#blink1').click(function(){lightsDetailAction('blink1');});
	$('#blink30s').click(function(){lightsDetailAction('blink30s');});
	$('#blinkoff').click(function(){lightsDetailAction('blinkoff');});

	// Colorloop
	$('#colorloop').click(function(){lightsDetailAction('colorloop');});
	$('#colorloopoff').click(function(){lightsDetailAction('colorloopoff');});

	// Assign group to selection (appears if selection contains only lamps)
	$('#grpassign').click(function(){lightsDetailAction('grpassign');});
	$('#delgrp').click(function(){lightsDetailAction('delgrp');});

} // lightsDetail

//------------------------------------------------------------------------
// Update color picker value
// Parameters : tablights, lnumref[, hexrgb[, updateBri]]
//
// If hexrgb given : tablights could be empty ("") because not used
// If hexrgb not given : tablights have to be filled
// updateBri (true/false=default) : update bri display too 
//------------------------------------------------------------------------
function updateColorPicker(tablights, lnumref, hexrgb, updateBri=false){
	if (! hexrgb || hexrgb == ""){
		hexrgb = $(tablights+' table a.switch[lnum='+lnumref+'] div').attr('rgb');
	}
	if (hexrgb){
		$('#colorpicker').data('minicolors-initialized',false);
		$('#colorpicker').minicolors('value',hexrgb);
		$('#colorpicker').data('minicolors-initialized',true);
		if (updateBri){ // Update brightness is requested too
			$.getJSON('main.php?rt=color&rgb='+encodeURIComponent(hexrgb), function(xy){
				$('#brislider').val(xy.bri);
			});
		}
	}
} // updateColorPicker

//----------------------------------------
// Function triggered by minicolors picker
//----------------------------------------
function changeColorPicker(rgb){
		// Get x, y, bri correspondance to RGB
		$.getJSON('main.php?rt=color&rgb='+encodeURIComponent(rgb), function(xy){
			$('#brislider').val(xy.bri); // Update brightness
			lightsDetailAction('color',xy);
		});
} // changeColorPicker
