<?php
// Functions to handle colors with hue
// F. Bardin 07/02/2015
// 29/07/2015 : clean code
// 05/01/2018 : fix for color temperature lights
//-----------------------------------
// Anti-hack
if (! defined('ANTI_HACK')){exit;}

//-----------------------------------
// Convert xy to rgb color
// Parameters = as supplied by the bridge :
// 	            x and y color coordinates
// 	            brightness value
// Return RGB in #XXXXXX format (hexa values)
//-----------------------------------
function xyToRGB($x,$y,$bri){

	// Calculate XYZ values
 	$z = 1 - $x - $y;
 	$Y = $bri / 254; // Brightness coeff.
	if ($y == 0){
		$X = 0;
		$Z = 0;
	} else {
 		$X = ($Y / $y) * $x;
 		$Z = ($Y / $y) * $z;
	}

	// Convert to sRGB D65 (official formula on meethue)
	// old formula 
 	// $r = $X * 3.2406 - $Y * 1.5372 - $Z * 0.4986;
	// $g = - $X * 0.9689 + $Y * 1.8758 + $Z * 0.0415;
 	// $b = $X * 0.0557 - $Y * 0.204 + $Z * 1.057;
	// formula 2016
 	$r =   $X * 1.656492 - $Y * 0.354851 - $Z * 0.255038;
	$g = - $X * 0.707196 + $Y * 1.655397 + $Z * 0.036152;
 	$b =   $X * 0.051713 - $Y * 0.121364 + $Z * 1.011530;

	// Apply reverse gamma correction
    $r = ($r <= 0.0031308 ? 12.92 * $r : (1.055) * pow($r, (1 / 2.4)) - 0.055);
    $g = ($g <= 0.0031308 ? 12.92 * $g : (1.055) * pow($g, (1 / 2.4)) - 0.055);
    $b = ($b <= 0.0031308 ? 12.92 * $b : (1.055) * pow($b, (1 / 2.4)) - 0.055);

	// Calculate final RGB
	$r = ($r < 0 ? 0 : round($r * 255));
	$g = ($g < 0 ? 0 : round($g * 255));
	$b = ($b < 0 ? 0 : round($b * 255));

	$r = ($r > 255 ? 255 : $r);
	$g = ($g > 255 ? 255 : $g);
	$b = ($b > 255 ? 255 : $b);

	// Create a web RGB string (format #xxxxxx)
	$RGB = "#".substr("0".dechex($r),-2).substr("0".dechex($g),-2).substr("0".dechex($b),-2);

	return $RGB;
} // xyToRGB

// ------------------------------------------
// Convert RGB to xy color + bri
// Parameter : RGB in #XXXXXX format (hexa values)
// Return json string : {"x":"xval","y":"yval","bri":"brival"}
// ------------------------------------------
function RGBToXy($RGB){
	// Get decimal RGB
	$r = hexdec(substr($RGB,1,2));
	$g = hexdec(substr($RGB,3,2));
	$b = hexdec(substr($RGB,5,2));

	// Calculate rgb as coef
	$r = $r / 255;
	$g = $g / 255;
	$b = $b / 255;

	// Apply gamma correction
	$r = ($r > 0.04045 ? pow(($r + 0.055) / 1.055, 2.4) : ($r / 12.92));
	$g = ($g > 0.04045 ? pow(($g + 0.055) / 1.055, 2.4) : ($g / 12.92));
	$b = ($b > 0.04045 ? pow(($b + 0.055) / 1.055, 2.4) : ($b / 12.92));

	// Convert to XYZ (official formula on meethue)
	// old formula
	//$X = $r * 0.649926 + $g * 0.103455 + $b * 0.197109;
 	//$Y = $r * 0.234327 + $g * 0.743075 + $b * 0.022598;
 	//$Z = $r * 0        + $g * 0.053077 + $b * 1.035763;
	// formula 2016
	$X = $r * 0.664511 + $g * 0.154324 + $b * 0.162028;
 	$Y = $r * 0.283881 + $g * 0.668433 + $b * 0.047685;
 	$Z = $r * 0.000088 + $g * 0.072310 + $b * 0.986039;

	// Calculate xy and bri
	if (($X+$Y+$Z) == 0){
		$x = 0;
		$y = 0;
	} else { // round to 4 decimal max (=api max size)
		$x = round($X / ($X + $Y + $Z),4);	
		$y = round($Y / ($X + $Y + $Z),4);
	}
	$bri = round($Y * 254);
	if ($bri > 254){$bri = 254;}

	return '{"xy": ['.$x.','.$y.'],"bri": '.$bri.'}';
} // RGBToXy

// ------------------------------------------
// Display a ligh with icon and color
// Remark : HueAPI->info must already be set
// ------------------------------------------
function display_light($lnum){
	global $HueAPI, $trs;

	$linfo = &$HueAPI->info['lights'][$lnum];

	// Init on/off + lamp color
	$unreachable = false;
	$popup = "";
	$lstate = &$linfo['state'];
	if ($linfo['type'] == "Dimmable light" || $linfo['type'] == "Color Temperature Light")
	{ // White and grey : xy are constant
		$rgbcolor=xyToRGB("0.3127","0.329",$lstate['bri']);
	} else {
		$rgbcolor=xyToRGB($lstate['xy']['0'],$lstate['xy']['1'],$lstate['bri']);
	}
	if ($lstate['on'] == "" || $lstate['reachable'] == ""){
		$onoff = "off";
		$lcolor = "transparent";
		if ($lstate['reachable'] == ""){
			$unreachable = true;
			$popup = " TITLE=\"".$trs["Unreachable"]."\"";
		}
	} else { // light on : display rgb color
		$onoff = "on";
		$lcolor = $rgbcolor;
	}

	// Init lamp class
	$lclass = "sw".$onoff." ".$linfo['modelid'];

	// TODO ==> Add msl process if modelid = LLM001
	// cf css --> groups display may require some changes to do so
	
	// Display lamp
	echo "<DIV STYLE=\"background-color:$lcolor;\" RGB=$rgbcolor CLASS=\"$lclass\"$popup>";
	if ($unreachable){echo "<SPAN CLASS=\"ui-state-focus unreachable\"><SPAN CLASS=\"ui-icon ui-icon-alert unreachable\"></SPAN></SPAN>";}
	echo "</DIV>";
} // display_light
?>
