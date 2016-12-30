// Javascript Functions for phpMyHue
// F. Bardin 2015/02/10
//------------------------------------------------------------------------
// Functions list :
// isMobile 			returns true/false if navigator is used from a mobile device
// uniqid 				generates a pseudo unique id of 13 char (+ prefix if supplied)
// $.scrollbarWidth 	returns the scroll bar width of the navigator in pixels
// scrollCurrentTab 	enables vertical scroll of a tab id if needed
// msg 					displays a normal message or an error then fades it out
// processReturnMsg 	processes return message send by the bridge and display it with msg()
// getCurrentTabsID 	returns the current tab id of a given tab object
// lightList 			manage list list event (for light tab or scene detail tab)
// switchGroup 			switch on or off a group of lamps
//------------------------------------------------------------------------
// 2016/12/28 : Correction on lights de-select if present in several groups 
//------------------------------------------------------------------------
//----------------------------------------
// Create fonctions for mobiles detection
//----------------------------------------
var isMobile = {
    Android: 	function(){return /Android/i.test(navigator.userAgent);},
    BlackBerry: function(){return /BlackBerry/i.test(navigator.userAgent);},
    iOS: 		function(){return /iPhone|iPad|iPod/i.test(navigator.userAgent);},
    Windows: 	function(){return /IEMobile/i.test(navigator.userAgent);},
    any: function(){return (isMobile.Android()||isMobile.BlackBerry()||isMobile.iOS()||isMobile.Windows());}
};

//----------------------------------------------
// Generate a unique id as php 'uniqid' function
// --> generate a 13 char string (+prefix if supplied)
//----------------------------------------------
function uniqid(prefix, more_entropy) {
  //  discuss at: http://phpjs.org/functions/uniqid/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //  revised by: Kankrelune (http://www.webfaktory.info/)
  if (typeof prefix === 'undefined') {prefix = '';}

  var retId;
  var formatSeed = function (seed, reqWidth) {
    seed = parseInt(seed, 10).toString(16); // to hex str
    if (reqWidth < seed.length) { // so long we split
      return seed.slice(seed.length - reqWidth);
    }
    if (reqWidth > seed.length) { // so short we pad
      return Array(1 + (reqWidth - seed.length)).join('0') + seed;
    }
    return seed;
  };

  if (!this.php_js) {this.php_js = {};}
  if (!this.php_js.uniqidSeed) { // init seed with big random int
    this.php_js.uniqidSeed = Math.floor(Math.random() * 0x75bcd15);
  }
  this.php_js.uniqidSeed++;

  // start with prefix, add current milliseconds hex string
  retId = prefix;
  retId += formatSeed(parseInt(new Date().getTime() / 1000, 10), 8);
  // add seed hex string
  retId += formatSeed(this.php_js.uniqidSeed, 5);
  if (more_entropy) { // for more entropy we add a float lower to 10
    retId += (Math.random() * 10).toFixed(8).toString();
  }

  return retId;
} // uniqid

//----------------------------------------
// Calculate scrollbar width
//----------------------------------------
(function($){$.scrollbarWidth=function(){
	if(!$._scrollbarWidth){
    	var parent = $('<div style="width:50px;height:50px;overflow:auto"><div/></div>').appendTo('body');
    	var child=parent.children();
    	$._scrollbarWidth=child.innerWidth()-child.height(99).innerWidth();
    	parent.remove();
	}
	return $._scrollbarWidth;
};})(jQuery);
$.scrollbarWidth();

//----------------------------------------
// Set vertical scroll bar on current tab if needed
// If scroll id exists : scroll on this component
// The scroll id container can't have padding, margin or border, except padding top
//----------------------------------------
function scrollCurrentTab(tabsID){
	// Initialize parameters
	var curtabid = getCurrentTabsID(tabsID);
	var innerdivid = 'inner-'+curtabid;
	var scrolldivid = 'scroll-'+curtabid;

	var divtab=$(tabsID);
	var	curtab=$('#'+curtabid);
	var scrolltab = curtab;
	if ($('#'+curtabid+' #scroll').length){scrolltab = $('#'+curtabid+' #scroll');}

	var bar_width=0;
	var win_height=$(window).height();
	var curtab_pos=scrolltab.offset();
	var curtab_padding_top=Math.floor(scrolltab.css("padding-top").replace("px", ""));
	var curtab_padding_bottom=Math.floor(curtab.css("padding-bottom").replace("px", ""));
	var curtab_border_bottom=Math.floor(curtab.css("border-bottom-width").replace("px", ""));
	var curtab_margin_bottom=Math.floor(curtab.css("margin-bottom").replace("px", ""));
	var curtab_outer_bottom_tot=curtab_padding_bottom+curtab_border_bottom+curtab_margin_bottom;

	var divtab_padding_bottom=Math.floor(divtab.css("padding-bottom").replace("px", ""));
	var divtab_border_bottom=Math.floor(divtab.css("border-bottom-width").replace("px", ""));
	var divtab_margin_bottom=Math.floor(divtab.css("margin-bottom").replace("px", ""));
	var divtab_outer_bottom_tot=divtab_padding_bottom+divtab_border_bottom+divtab_margin_bottom;

	var scroll_height=Math.floor(win_height-curtab_pos.top);
	scroll_height -=curtab_padding_top+curtab_outer_bottom_tot+divtab_outer_bottom_tot;

	// Create inner div if not exists to wrap content
	if (! $('#'+innerdivid).length){scrolltab.wrapInner('<DIV ID='+innerdivid+'>');}
	var innerdiv = $('#'+innerdivid);

	var inner_height=Math.ceil(innerdiv.height());

	// Manage scroll bar display
	if (inner_height>=scroll_height){ // If scroll bar is needed
		var scrolldiv = "";

		if (! $('#'+scrolldivid).length){ // Create scroll div if not exist
			innerdiv.wrap('<DIV ID='+scrolldivid+'>');
			scrolldiv = $('#'+scrolldivid);

			// Set width to include scrollbar width
			var win_width=$(window).width();
			var scrolldiv_width=scrolldiv.width();
			if(scrolldiv_width!=win_width){bar_width=$.scrollbarWidth();}
			var scroll_width=(scrolldiv_width+bar_width);
			scrolldiv.css('width',scroll_width);
		} else {
			scrolldiv = $('#'+scrolldivid);
		}

		// Set scroll height + vertical scrollbar
		var	scroll_pos=scrolldiv.scrollTop();
		scroll_height-=scroll_pos;
		scrolldiv.scrollTop(scroll_pos);
		scrolldiv.css("height",scroll_height);
		scrolldiv.css("overflow-y","auto");
	} else { // remove scrolldiv if exists
		if ($('#'+scrolldivid).length){innerdiv.unwrap();}
	}
} // scrollCurrentTab

// Intercept browser resize
$(window).resize(function(){
	scrollCurrentTab('#tabs');
	if ($('#detail').is(':visible')){scrollCurrentTab('#detail');}
});

//----------------------------------------
// Display message
// parameter err = true if error message
//----------------------------------------
function msg(msg,err){
	err = err || false;
	var msgdiv = '#msg';

	if (err){$(msgdiv).addClass('error');}
	else    {$(msgdiv).removeClass('error');}
	$(msgdiv).text(msg);
	$(msgdiv).fadeIn('fast');
	$(msgdiv).fadeOut(5000, function(){
		$(msgdiv).text('');
		$(msgdiv).fadeIn('fast');
	});
} // msg

//----------------------------------------
// Process Return message send by bridge
// Paremeters : 
// - retmsg = json message returned by the bridge
// - successmsg = if supplied, message to display for sucessful command
// Return : true/false if succes/error
// display the result in msg box
//----------------------------------------
function processReturnMsg(retmsg,successmsg){
	sucessmsg = successmsg || '';
	var result = true;

	if (retmsg[0].error){
		result = false;
		msg(trs.ERROR+' : '+retmsg[0].error.description, true);
	} else {
		msg(successmsg);
	}
	return result;
} // processReturnMsg

//----------------------------------------
// Get ID of a current jquery-ui tabs content
//----------------------------------------
function getCurrentTabsID(tabSelector){
	var activeTabIdx = $(tabSelector).tabs('option','active');

	return $(tabSelector+' > ul > li').eq(activeTabIdx).attr('aria-controls');
} // getCurrentTabsID

//----------------------------------------
// Function to manage lights list events
// listtab = id of list tab
// prefid = prefix applied to id if detail tab
//----------------------------------------
function lightsList(listtab,prefid){
	prefid = prefid || "";

	// Switch a light
	$(listtab+' a.switch').click(function(onoff) {
		onoff.preventDefault();
		// switch and reload light
		var lnum = $(this).attr('lnum');
		$(listtab+' a.switch[lnum='+lnum+']').load('main.php?rt=switch&lnum='+lnum);
	});

	// Switch all
	$('#'+prefid+'allon').click(function(){
		switchGroup(listtab,'0','on');
	});
	$('#'+prefid+'alloff').click(function(){
		switchGroup(listtab,'0','off');
	});

	// Switch a group
	$(listtab+' button.gron').click(function(){
		var gnum = $(this).attr('gnum');
		switchGroup(listtab,gnum,'on');
	});
	$(listtab+' button.groff').click(function(){
		var gnum = $(this).attr('gnum');
		switchGroup(listtab,gnum,'off');
	});

	// Switch lamps without group
	$('#'+prefid+'otheron').click(function(){
		switchGroup(listtab,'other','on');
	});
	$('#'+prefid+'otheroff').click(function(){
		// reload current tab
		switchGroup(listtab,'other','off');
	});

	// Select all
	$('#'+prefid+'cb_all').change(function() {
		$('#'+prefid+'s_all').toggleClass('cbchecked');
		if ($(this).prop('checked')){
			$(listtab+' tbody input[type="checkbox"]').prop('checked',true);
			$(listtab+' tbody input[type="checkbox"]').parent('span').addClass('cbchecked');
			$(listtab+' td.label').addClass('ui-state-focus');
		} else {
			$(listtab+' tbody input[type="checkbox"]').prop('checked',false);
			$(listtab+' tbody input[type="checkbox"]').parent('span').removeClass('cbchecked');
			$(listtab+' td.label').removeClass('ui-state-focus');
		}
		if (prefid == ""){loadSelectedLightsDetail(listtab);}
	});

	// Select group
	$(listtab+' tbody input.grp').change(function() {
		id=$(this).attr('id');
		var gnum = $(this).attr('gnum');
		$('#'+prefid+'s_'+gnum).toggleClass('cbchecked');

		if ($(this).prop('checked')){
			$(listtab+' tbody tr.grp[gnum='+gnum+'] td.label').addClass('ui-state-focus');
			$(listtab+' tbody tr.grp'+gnum+' input.light').prop('checked',true);
			$(listtab+' tbody tr.grp'+gnum+' input.light').parent('span').addClass('cbchecked');
			$(listtab+' tbody tr.grp'+gnum+' td.label').addClass('ui-state-focus');
		} else { // unckecked lamp + all
			$(listtab+' tbody tr.grp[gnum='+gnum+'] td.label').removeClass('ui-state-focus');
			$(listtab+' tbody tr.grp'+gnum+' input.light').prop('checked',false);
			$(listtab+' tbody tr.grp'+gnum+' input.light').parent('span').removeClass('cbchecked');
			$(listtab+' tbody tr.grp'+gnum+' td.label').removeClass('ui-state-focus');
			$('#'+prefid+'cb_all').prop('checked',false);
			$('#'+prefid+'s_all').removeClass('cbchecked');
			$(listtab+' thead td.label').removeClass('ui-state-focus');
		}
		if (prefid == ""){loadSelectedLightsDetail(listtab)};
	});

	// Uncheck all and group if a lamp is unchecked
	// + check/uncheck all lines for the same lamp (if it belongs to several groups)
	$(listtab+' tbody input.light').change(function() {
		id=$(this).attr('id');
		var lnum = $(this).attr('lnum');
		var gnum = $(this).attr('gnum');
		if ($(this).prop('checked')){
			$(listtab+' tbody tr.light[lnum='+lnum+'] td.label').addClass('ui-state-focus');
			$(listtab+' tbody tr.light[lnum='+lnum+'] input.light').prop('checked',true);
			$(listtab+' tbody tr.light[lnum='+lnum+'] input.light').parent('span').addClass('cbchecked');
		} else {
			$(listtab+' tbody tr.light[lnum='+lnum+'] td.label').removeClass('ui-state-focus');
			$(listtab+' tbody tr.light[lnum='+lnum+'] input.light').prop('checked',false);
			$(listtab+' tbody tr.light[lnum='+lnum+'] input.light').parent('span').removeClass('cbchecked');
			$(listtab+' tbody tr.light[lnum='+lnum+']').each(function(){
				gnum = $(this).attr('gnum');
				$(listtab+' tbody tr.grp[gnum='+gnum+'] td.label').removeClass('ui-state-focus');
				$(listtab+' tbody tr.grp[gnum='+gnum+'] input.grp').prop('checked',false);
				$(listtab+' tbody tr.grp[gnum='+gnum+'] input.grp').parent('span').removeClass('cbchecked');
			});
			$(listtab+' thead td.label').removeClass('ui-state-focus');
			$('#'+prefid+'cb_all').prop('checked',false);
			$('#'+prefid+'s_all').removeClass('cbchecked');
		}
		if (prefid == ""){loadSelectedLightsDetail(listtab);}
	});
	
	// Collapse/Extend groups
	$(listtab+' span.grp').click(function(){
		var gnum = $(this).attr('gnum');
		var open = $(this).attr('open');

		collapseGroup(listtab,prefid,this,open);

		if (gnum == 0){ // if all, manage all groups
			$(listtab+' tbody span.grp').each(function(){
				collapseGroup(listtab,prefid,this,open);
			});
		}
	});

	// Intialize brightness slider if existing
	$(listtab+' .brislider').each(function(){
		var bsid = $(this).attr('id');
		$(this).noUiSlider({
			start: 0,
			step: 1,
			connect: 'upper',
			range: {
					'min': 0,
					'max': 254
			},
			format: wNumb({decimals: 0})
		});
		$(this).Link('lower').to($('#'+bsid+'_val'));
	});
	// Change light brightness
	$(listtab+' .brislider').change(function(){
		var val = $(this).val();
		var gnum = $(this).attr('gnum');
		var lnum = "";
		if ($(this).is('[lnum]')){lnum = $(this).attr('lnum');}
		if (lnum == ""){
			var bschild = listtab+' tbody .brislider' 
			if (gnum != 'all'){bschild = bschild + '[gnum="'+gnum+'"]';}
			$(bschild).val(val);
			$(bschild+'[lnum]').change();
		} else {
			var action = 'lights/'+lnum+'/state';
			var cmdjs = '&cmdjs={"bri":'+val+'}'; 
			var curlnum = (lnum);
			$.getJSON('hueapi_cmd.php?action='+action+cmdjs, (function(jsmsg){
				if (processReturnMsg(jsmsg)){
					$(listtab+' a.switch[lnum='+curlnum+']').load('main.php?rt=display&lnum='+curlnum);
				}
			}));
		}
	});	
} // lightsList

// ---------------------------------------------------
// Manage the collapse capabilities on a group list
// Parameters :
// listtab = id of list tab
// prefid = prefix applied to id if detail tab
// groupobj = pointer to the group row to manage
// open = current status of groupobj
// ---------------------------------------------------
function collapseGroup(listtab,prefid,groupobj,open){
	var gnum = $(groupobj).attr('gnum');

	if (open){
		$(listtab+' tbody tr.grp'+gnum).hide(300);
		$(groupobj).switchClass('ui-icon-circle-minus','ui-icon-circle-plus',0);
		$(groupobj).removeAttr('open');
		// Uncheck lights if group not checked
		if (! $(listtab+' tbody tr.grp[gnum='+gnum+'] input.grp').prop('checked')){
			$(listtab+' tbody tr.grp'+gnum+' input.light').prop('checked',false);
			$(listtab+' tbody tr.grp'+gnum+' td.label').removeClass('ui-state-focus');
			if (prefid == ""){loadSelectedLightsDetail(listtab);}
		}
	} else {
		$(listtab+' tbody tr.grp'+gnum).show(300);
		$(groupobj).switchClass('ui-icon-circle-plus','ui-icon-circle-minus',0);
		$(groupobj).attr('open','');
	}
} // collapseGroup

// -----------------------------
// Switch a lights group on/off
// listtab = tab id with lights list
// if gnum=other : lamp without group
// onoff = on or off
// -----------------------------
function switchGroup(listtab,gnum,onoff){
	var action = "groups/"+gnum+"/action";
	var cmdjs = '&cmdjs={"on":'+(onoff == 'on' ? true : false)+'}';

	if (gnum == 'other'){action = gnum;}

	$.getJSON('hueapi_cmd.php?action='+action+cmdjs, function(){
		// re-display all lights in the group
		var lnum = "";
		var gsearch = "";
		if (gnum != '0'){gsearch = ' tr.grp'+gnum;}
		$(listtab+gsearch+' a.switch').each(function(){
			lnum = $(this).attr('lnum');
			$(this).load('main.php?rt=display&lnum='+lnum);
		});
	});
} // switchGroup
