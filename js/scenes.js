/*-------------------------
 Functions for scenes tab
 F. Bardin 21/03/2015
 -------------------------*/
//-------------------------
// Function for scenes tab
//-------------------------
function scenesTab(){
	scrollCurrentTab('#tabs');

	// Get selector for Div ID of current tab
	var tabscenes = "#"+getCurrentTabsID('#tabs');
	var tabdetail = "#"+getCurrentTabsID('#detail');

	// Trigger scene selection
	$('#tabs td span.ui-icon input').click(function(){
		$(this).change();
	});
	// Activate scene when a new selection occurs
	$('#tabs input[type=radio][name=scradio]').change(function(){
		$('#tabs span.ui-icon-arrow-1-e').each(function(){
			$(this).removeClass('ui-icon-arrow-1-e');
			$(this).addClass('ui-icon-radio-off');
			$(this).parent('td').parent('tr').removeClass('ui-state-focus');
		});
		var cbspan = $(this).parent('span');
		cbspan.parent('td').parent('tr').addClass('ui-state-focus');
		cbspan.removeClass('ui-icon-radio-off');
		cbspan.addClass('ui-icon-arrow-1-e');
		var sceneid = $(this).attr('id');
		$('#detail').attr('sceneid',sceneid);
		$.getJSON('hueapi_cmd.php?action=groups/0/action&cmdjs={"scene":"'+sceneid+'"}', function(jsmsg){
			if (processReturnMsg(jsmsg)){
				loadScenesDetail(sceneid);
			}
		});

	});

	// Load detail tab with scene details
	$(tabdetail).load('details.php?rt=scenes', function(){
		lightsList(tabdetail,"S_");
		initBriSliders();
		$('#detail').show("slide");
		scrollCurrentTab('#detail');

		// if a scene was previously selected : re-select it
		if ($('#detail').is('[sceneid]')){
			var sceneid = $('#detail').attr('sceneid');
			var cbspan = $('#'+sceneid).parent('span');
			cbspan.parent('td').parent('tr').addClass('ui-state-focus');
			cbspan.removeClass('ui-icon-radio-off');
			cbspan.addClass('ui-icon-arrow-1-e');
			loadScenesDetail(sceneid);
		} else { // If no scene selected : disable update
			$('#updscene').prop('disabled',true).addClass('ui-state-disabled');
		}
	});
} // scenesTab

// -----------------------------
// Initialize brillance sliders
// -----------------------------
function initBriSliders(){
	var tabdetail = "#"+getCurrentTabsID('#detail');

	$(tabdetail+' .brislider:not([lnum])').val(0);
	$.getJSON('hueapi_cmd.php?action=lights', function(info){
		$(tabdetail+' tbody .brislider[lnum]').each(function(){
			lnum = $(this).attr('lnum');
			$(this).val(info[lnum].state.bri);
		});
	});
} // initBriSliders

//------------------------
// Load Scenes detail tab
//------------------------
// Remark : re-display could be buggy, because bridge take a time to update lights values when a scene is called
// 3 displays are done after 1s, 6s, 11s and 21s (which is generally enough).
//------------------------
function loadScenesDetail(sceneid){
	$.getJSON('hueapi_cmd.php?action=scenes', (function(info){
		var timeout = 5000;

		// Enable name and update
		$('#updscene').removeAttr('disabled').removeClass('ui-state-disabled');

		// Update tab with selected scene
		$('#scname').val(info[sceneid].name);

		var tabdetail = "#"+getCurrentTabsID('#detail');

		$(tabdetail+' table input[type=checkbox]').prop('checked',false);
		$(tabdetail+' table input[type=checkbox]').parent('span').removeClass('cbchecked');
		$(tabdetail+' td.label').removeClass('ui-state-focus');

		jQuery.each(info[sceneid].lights, function(num, lnum){
			$(tabdetail+' tbody tr.light[lnum='+lnum+'] input.light').prop('checked',true);
			$(tabdetail+' tbody tr.light[lnum='+lnum+'] input.light').parent('span').addClass('cbchecked');
			$(tabdetail+' tbody tr.light[lnum='+lnum+'] td.label').addClass('ui-state-focus');

			// Re-display colors
			setTimeout(function(){$(tabdetail+' a.switch[lnum='+lnum+']').load('main.php?rt=display&lnum='+lnum);
				setTimeout(function(){$(tabdetail+' a.switch[lnum='+lnum+']').load('main.php?rt=display&lnum='+lnum);
					setTimeout(function(){$(tabdetail+' a.switch[lnum='+lnum+']').load('main.php?rt=display&lnum='+lnum);
						setTimeout(function(){$(tabdetail+' a.switch[lnum='+lnum+']').load('main.php?rt=display&lnum='+lnum);}, timeout*2);
					}, timeout);
				}, timeout);
			}, 1000);
		});
		// Re-display brillance slider
		setTimeout(function(){initBriSliders(); // 1s
			setTimeout(function(){initBriSliders(); // 6s
				setTimeout(function(){initBriSliders(); // 11s
					setTimeout(function(){initBriSliders();}, timeout*2); // 21s
				}, timeout);
			}, timeout);
		}, 1000);
	}));
} // loadScenesDetail

//----------------------------------
// Function for scenes detail tab
//----------------------------------
// remark : lights list function are managed in function.js
//----------------------------------
function scenesDetail(){
	// Update selected scene
	$('#updscene').click(function(){saveScene($('#detail').attr('sceneid'));});

	// Create new scene
	$('#newscene').click(function(){saveScene();});
} // scenesDetail

//--------------------------------------
// Save a scene
//--------------------------------------
// If sceneid is supplied update the scene.
// If sceneid is not supplied, create a new scene with a new id.
//--------------------------------------
function saveScene(sceneid){
	var scname = $('#scname').val();
	if (scname != ""){ // if name not empty
		var tabdetail = "#"+getCurrentTabsID('#detail');
		var tabscene = "#"+getCurrentTabsID('#tabs');

		var newscene = false;
		if (! sceneid){ // Generate a new id
			newscene = true;
			sceneid = ""; // to avoid string value 'undefined' if something goes wrong
			sceneid = uniqid('pmh-').substr(0,14);
			$('#detail').attr('sceneid',sceneid);
		}

		// Search for selected lights
		var lights_enum = "";
		var nblights = 0;
		$(tabdetail+' tbody input.light').each(function(){
			if ($(this).prop('checked')){
				if (nblights > 0){lights_enum += ",";}
				lights_enum += '"'+$(this).attr('lnum')+'"';
				nblights++;
			}
		})

		if (nblights > 0){ // if at least 1 light is selected
			var cmdjs = '{"name":"'+encodeURIComponent(scname)+'","lights":['+lights_enum+']}';

			// Send update
			$.getJSON('hueapi_cmd.php?action=scenes/'+sceneid+'&cmdjs='+cmdjs, function(jsmsg){
				var msg = "Scene "+sceneid+" with name '"+scname+"' ";
				if (newscene){msg += "Created.";}
				else         {msg += "Updated.";}
				if (processReturnMsg(jsmsg,msg)){
					if (newscene){ // reload whole scene tab
						$("#tabs").tabs('load',1);
					} else { // Update scene list
						$(tabscene+' table .sname label[for='+sceneid+']').text(scname);
						$(tabscene+' table label[for='+sceneid+'] span').text(nblights);
					}
				}
			});
		} else {
			msg(trs.No_light_selected_for_scene,true);
		}
	} else {
		msg(trs.Scene_name_empty,true);
	}
} // saveScene
