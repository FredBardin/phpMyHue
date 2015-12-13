/*-------------------------
 Functions for effects tab
 F. Bardin 01/12/2015
 -------------------------*/
//-------------------------
// Function for effects tab
//-------------------------
function effectsTab(){
	scrollCurrentTab('#tabs');

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
		$(this).load('main.php?rt=runeffect&effect='+effectid);
	});

	// Manage effects details
	$('#tabs td span.ui-icon[effect]').click(function(){
		$(this).toggleClass('ui-icon-circle-zoomin');
		$(this).toggleClass('ui-icon-circle-zoomout');
		// If details to display
		if($(this).hasClass('ui-icon-circle-zoomout')){
			var effect = $(this).attr('effect');

			// Toggle other existing zoomout icon
			$('#tabs td span.ui-icon-circle-zoomout').each(function(){
				if ($(this).attr('effect') != effect){
					$(this).toggleClass('ui-icon-circle-zoomin');
					$(this).toggleClass('ui-icon-circle-zoomout');
				}
			});

			// Show and load tab
			$("#detail").show("slide");
			$("#"+getCurrentTabsID('#detail')).load('details.php?rt=effects&effect='+effect);

		} else {
			$("#detail").hide("slide");
		}
	});
} // effectsTab

//----------------------------------
// Function for effects details tab
//----------------------------------
function effectsDetail(effect){
	// Run effect in debug mode	
	$("#rundbg").click(function(){
		$("#runout").text(trs.Running_please_wait);
		$("#runout").dialog("open");
		$("#runout").load('main.php?rt=runeffect&effect='+effect+'&debug=1');
	});
} // effectsDetail
