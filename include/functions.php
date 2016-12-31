<?php
/*==================================================
 Functions library for phpMyHue
 ---------------------------------------------------
 These functions are used by several screens or need an external call by ajax
 ---------------------------------------------------
 huecolor.php : includes functions to manage colors
 choose_lang : allow to choose a lang amon exiting translation files
 display_bri_slider : display a slider to manage brightness
 display_td_checkbox : display selection checkbox on a row
 display_light_row : display a light row
 display_lights_groups : display all groups and lights
 getCondRow : display a condition row for a rule
 getActRow : display an action row for a rule
 selOperator : display the operation select box for a row condition for a rule
 selMethod : display the method select box for a row action for a rule
====================================================*/
// Anti-hack
if (! defined('ANTI_HACK')){exit;}

include "huecolor.php"; // Function to process color from hue

// -------------------------------------------------------------
// Display a select list to choose interface language
// -------------------------------------------------------------
function choose_lang(){
	global $lang;

	// Get lang file array
	$lang_ar = glob('lang/text_??.json');

	// Display lang list
	echo "<SELECT ID=c_lang NAME=\"lang\">\n";
	$lang_count = count($lang_ar);
	for ($i=0; $i < $lang_count; $i++){
		$lang_val = preg_replace("/^.*text_(..)\.json$/","$1",$lang_ar[$i]);
		echo "<OPTION VALUE=$lang_val";
		if ($lang_val == $lang){echo " SELECTED";}
		echo ">$lang_val</OPTION>";
	}
	echo "</SELECT>\n";
} // choose_lang

// -------------------------------------------------------------
// Display brightness slider
// -------------------------------------------------------------
// use 2 <TD> : one for the slide, one to display the value
// -------------------------------------------------------------
// Parameters : prefix id, id, gnum[, lnum]
// -------------------------------------------------------------
function display_bri_slider($prefid,$id,$gnum,$lnum=""){
	echo "<TD CLASS=bslider>";
	echo "<DIV ID=".$prefid."bs_$id CLASS=brislider gnum=$gnum";
	if ($lnum != ""){echo " lnum=$lnum";}
	echo "></DIV>";
	echo "<TD><DIV ID=".$prefid."bs_".$id."_val CLASS=bsval></DIV>";
} // display_bri_slider

// -------------------------------------------------------------
// Display checkbox for selecting a row from a table
// -------------------------------------------------------------
// parameters : prefix id, checkbox ID, checkbox class, light id, grp id
// -------------------------------------------------------------
function display_td_checkbox($prefid, $id, $class="", $gnum="", $lnum=""){
	echo "<TD> ";
	echo "<SPAN ID=".$prefid."s_$id>";
	echo "<INPUT TYPE=checkbox ID=".$prefid."cb_$id";
	if ($class != ""){
		echo " CLASS=\"$class\"";
		if ($gnum != ""){
			echo " gnum=$gnum";
			if ($lnum != ""){echo " lnum=$lnum";}
		}
	}
	echo ">";
	echo "</SPAN>";
} // display_td_checkbox

// -------------------------------------------------------------
// Display a light row
// -------------------------------------------------------------
// Parameters = prefix id, light number, group number, check box position, brislider
// prefix id = prefix to apply to tag id
// check box position : B/E, Begin/End of light row
// brislider : true/false
// -------------------------------------------------------------
function display_light_row($prefid,$lnum,$gnum,$cbpos="E",$brislider=false){
	global $HueAPI;

	$lid = $gnum."_$lnum"; // add group to light num to have unique id
	$unreachable = false;
	if ($HueAPI->info['lights'][$lnum]['state']['reachable'] == ""){$unreachable = true;}

	// Display icon+name
	echo "<TR CLASS=\"light grp$gnum\" lnum=$lnum gnum=$gnum>";
	echo "<TD>";
	if ($cbpos == "B"){display_td_checkbox($prefid, $lid, "light", $gnum, $lnum);}
	echo "<TD>";
	if (! $unreachable){echo "<A HREF=lights CLASS=switch lnum=$lnum>";}
	display_light($lnum);
	if (! $unreachable){echo "</A>";}
	echo "<TD CLASS=\"label light\"><LABEL FOR=".$prefid."cb_$lid lnum=$lnum>".$HueAPI->info['lights'][$lnum]['name']."</LABEL>";
	if ($cbpos == "E"){display_td_checkbox($prefid, $lid, "light", $gnum, $lnum);}
	if ($brislider){display_bri_slider($prefid,$lid,$gnum,$lnum);}
} // display_light_row

// -------------------------------------------------------------
// Display groups and lights
// -------------------------------------------------------------
// Parameters (all optional) = prefix id, check box position , brislider
// prefix id = prefix to apply tag id
// check box position : B/E, Begin/End of light row
// brislider : optional, if true display a brightness slider at the end of a selected row
// -------------------------------------------------------------
function display_lights_groups($prefid="",$cbpos="E",$brislider=false){
	global $HueAPI, $trs;

	echo "<TABLE CLASS=det_table>";
	echo "<THEAD>";
	echo "<TR CLASS=grp>";
	echo "<TD><SPAN CLASS=\"grp ui-icon ui-icon-circle-minus\" gnum=0 open></SPAN>";
	if ($cbpos == "B"){display_td_checkbox($prefid,"all");}
	echo "<TD CLASS=\"label all\"><LABEL FOR=".$prefid."cb_all>".$trs["All"]."</LABEL>";
	echo "<TD><BUTTON ID=".$prefid."allon>On</BUTTON><BUTTON ID=".$prefid."alloff>Off</BUTTON>";
	if ($cbpos == "E"){display_td_checkbox($prefid,"all");}
	if ($brislider){display_bri_slider($prefid,"all","all");}

	echo "<TBODY>";
	foreach ($HueAPI->info['groups'] as $gnum => $gval){ // Existing groups
		if ($gval['type'] != "LightSource"){ // Exclude 'LightSource' groups : included into 'Luminaire' groups
			echo "<TR CLASS=grp gnum=$gnum>";
			echo "<TD><SPAN CLASS=\"grp ui-icon ui-icon-circle-minus\" gnum=$gnum open></SPAN>";
			if ($cbpos == "B"){display_td_checkbox($prefid, "$gnum", "grp", $gnum);}
			echo "<TD CLASS=\"label grp\"><LABEL FOR=".$prefid."cb_$gnum gnum=$gnum>".$gval['name']."</LABEL>";
			echo "<TD><BUTTON CLASS=gron gnum=$gnum>On</BUTTON><BUTTON CLASS=groff gnum=$gnum>Off</BUTTON>";
			if ($cbpos == "E"){display_td_checkbox($prefid, $gnum, "grp", $gnum);}
			if ($brislider){display_bri_slider($prefid,$gnum,$gnum);}
			foreach ($gval['lights'] as $internal => $lnum){display_light_row($prefid,$lnum,$gnum,$cbpos,$brislider);}
		}
	}

	// Lamps without group only if existing
	$othergroup=false;
	foreach ($HueAPI->info['lights'] as $lnum => $lval){if (! isset($lval['grp'])){$othergroup=true;break;}}
	if ($othergroup){
		echo "<TR CLASS=grp gnum=other>";
		echo "<TD><SPAN CLASS=\"grp ui-icon ui-icon-circle-minus\" gnum=other open></SPAN>";
		if ($cbpos == "B"){display_td_checkbox($prefid, "other", "grp", "other");}
		echo "<TD CLASS=\"label grp\"><LABEL FOR=".$prefid."cb_other>".$trs["Lamps"]."</LABEL>";
		echo "<TD><BUTTON ID=".$prefid."otheron>On</BUTTON><BUTTON ID=".$prefid."otheroff>Off</BUTTON>";
		if ($cbpos == "E"){display_td_checkbox($prefid, "other", "grp", "other");}
		if ($brislider){display_bri_slider($prefid,"other","other");}
		foreach ($HueAPI->info['lights'] as $lnum => $lval){if (! isset($lval['grp'])){display_light_row($prefid,$lnum,"other",$cbpos,$brislider);}}
	}

	echo "</TABLE>";
} // display_lights_groups

//-------------------------------------------------------
// Function to get a condition row
// Return the html string
// Parameters :
// sensorid = sensor id
// cond = condition id (0 to 3)
// cval = condition values (if existing)
// create = true/false. If false, display existing cval
// 						If true, create new cval row content (without <TR> tag)
//-------------------------------------------------------
function getCondRow($sensorid, $cond, $cval, $create=false){
	if ($create){ // Init fields if row creation
		$cval['address'] = "";
		$cval['operator'] = "";
		$cval['value'] = "";
	}
	else {echo "<TR>";}
	display_td_checkbox("cond",$cond,"csel");
	echo "<TD>";
	echo "<INPUT TYPE=text ID=caddr$cond VALUE=\"".str_replace("/sensors/$sensorid/","",$cval['address'])."\" CLASS=ui-corner-all>";
	echo "<TD>";
	selOperator("sbcond_".$cond,$cval['operator']);
	echo "<TD>";
	echo "<INPUT TYPE=text ID=cval$cond VALUE=\"";
	if ($cval['operator'] == "dx"){ // No value for dx
		echo "\" style=\"display: none;\"";
	} else {
		echo $cval['value']."\"";
	}
   	echo " CLASS=ui-corner-all>\n";
} // getCondRow

//-------------------------------------------------------
// Function to get an action row
// Return the html string
// Parameters :
// act  = action id (0 to 3)
// aval = action values (if existing)
// create = true/false. If false, display existing aval
// 						If true, create new aval row content (without <TR> tag)
//-------------------------------------------------------
function getActRow($act, $aval, $create=false){
	if ($create){ // Init fields if row creation
		$aval['address'] = "";
		$aval['method'] = "PUT";
		$aval['body'] = "";
	}
	else {echo "<TR>";}
	display_td_checkbox("act",$act,"asel");
	echo "<TD>";
	echo "<INPUT TYPE=text ID=aaddr$act VALUE=\"".$aval['address']."\" CLASS=ui-corner-all>";
	echo "<TD>";
	selMethod("sbact_".$act,$aval['method']);
	echo "<TD>";
	echo "<INPUT TYPE=text ID=abody$act VALUE=\"";
	$comma="";
	if ($aval['body'] != ""){
		foreach ($aval['body'] as $key => $val){
			echo $comma."&quot;$key&quot;:&quot;$val&quot;";
			$comma=", ";
		}
	}
	echo "\" CLASS=ui-corner-all>\n"; 
} // getActRow

//-------------------------------------------------------
// Function to display the operator selbox
// For reminder : eq, gt, lt or dx (has changed)
//-------------------------------------------------------
function selOperator($selboxid,$opval){
	global $trs;
	echo "<SELECT ID=$selboxid CLASS=selope>\n";
	echo "<OPTION VALUE='eq'";
	if ($opval == "eq"){echo " SELECTED";}
	echo "> = </OPTION>\n";
	echo "<OPTION VALUE='gt'";
	if ($opval == "gt"){echo " SELECTED";}
	echo "> &gt; </OPTION>\n";
	echo "<OPTION VALUE='lt'";
	if ($opval == "lt"){echo " SELECTED";}
	echo "> &lt; </OPTION>\n";
	echo "<OPTION VALUE='dx'";
	if ($opval == "dx"){echo " SELECTED";}
	echo ">".$trs["op_dx"]."</OPTION>\n";
	echo "</SELECT>\n";

	echo "<SCRIPT>\$(\"#$selboxid\").selectmenu({width : 'auto'});</SCRIPT>\n";
} // selOperator

//-------------------------------------------------------
// Function to display the action method selbox
// For reminder : put, post, delete
//-------------------------------------------------------
function selMethod($selmethid,$methval){
	echo "<SELECT ID=$selmethid CLASS=selmeth>\n";
	echo "<OPTION VALUE='PUT'";
	if ($methval == "PUT"){echo " SELECTED";}
	echo ">PUT</OPTION>\n";
	echo "<OPTION VALUE='POST'";
	if ($methval == "POST"){echo " SELECTED";}
	echo ">POST</OPTION>\n";
	echo "<OPTION VALUE='DELETE'";
	if ($methval == "DELETE"){echo " SELECTED";}
	echo ">DELETE</OPTION>\n";
	echo "</SELECT>\n";

	echo "<SCRIPT>\$(\"#$selmethid\").selectmenu({width : 'auto'});</SCRIPT>\n";
} // selMethod

?>
