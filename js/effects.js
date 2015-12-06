/*-------------------------
 Functions for effects tab
 F. Bardin 01/12/2015
 -------------------------*/
//-------------------------
// Function for effects tab
//-------------------------
function effectsTab(){
	scrollCurrentTab('#tabs');

	// Get selector for Div ID of current tab
	//var tabeffects = "#"+getCurrentTabsID('#tabs');
	//var tabdetail = "#"+getCurrentTabsID('#detail');

	// Trigger effect selection
	$('#tabs td span.ui-icon input').click(function(){
		$(this).change();
	});
	// Activate effect when a new selection occurs
	$('#tabs input[type=radio][name=efradio]').change(function(){
		$('#tabs span.ui-icon-arrow-1-e').each(function(){
			$(this).removeClass('ui-icon-arrow-1-e');
			$(this).addClass('ui-icon-radio-off');
			$(this).parent('td').parent('tr').removeClass('ui-state-focus');
		});
		var cbspan = $(this).parent('span');
		cbspan.parent('td').parent('tr').addClass('ui-state-focus');
		cbspan.removeClass('ui-icon-radio-off');
		cbspan.addClass('ui-icon-arrow-1-e');
		var effectid = $(this).attr('id');
		msg($(this).load('main.php?rt=runeffect&effect='+effectid));
	});

	// Load effects details
	$('#tabs td span.ui-icon-zoomin').click(function(){
		var effect = $(this).attr('effect');
		$("#"+getCurrentTabsID('#detail')).load('details.php?rt=effects&effect='+effect);
	});
} // effectsTab

//----------------------------------
// Function for effects details tab
//----------------------------------
function effectsDetail(effect){
	// Run effect in debug mode	
	$("#rundbg").click(function(){
		$("#runout").dialog("open");
	});
} // effectsDetail
