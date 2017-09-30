<?php
//====================================================
// Hue effect object
//
// Create an object to run custom effects
//
// Hue api must be available
//----------------------------------------------------
// Available public methods :
//
// construct = new HueEffect([debug_mode=false])
// 		debug_mode : display effect execution step by step if true
//
// runEffect(effect_name) : run effect in parameter 
// getDescription(effect_name) : return an array containing the attribute(s) of tag <effect>
//----------------------------------------------------
// Remark for debug mode :
// Colored output is generated with class effect-tag, -attribute and -value
//----------------------------------------------------
// Available tag for effects :
// <effect></effect> : effect name and comment
// <loop></loop> : loop with repeat attribute
// <var /> : set a var name with a value
// <light /> or <group /> : set light/group id with attributes values
// <scene /> : call a scene by name
// <timer /> : set a timer for n.m second
// <getcolor /> : get light id on+color and save it under a name (in colormode format)
// <setcolor /> : put a saved on+color on a given light (default) or group id (+[transitiontime])
//----------------------------------------------------
// F. Bardin 14/11/2015
// 11/07/2016 : add 'on' state to getcolor/setcolor 
//====================================================
// Anti-hack
if (! defined('ANTI_HACK')){exit;}

//-- Effect Class
class HueEffect {
	//=====================================
	//== Variables ==
	//=====================================
	private $debug;				// Debug mode : true/false (1/0)
	private $path = 'effects';	// Effect files directory
	private $ext = '.xml';		// Effect file extension
	private $ha;				// Internal pointer to HueAPI object
	private $xr = array();		// Internal pointer to XMLReader objects
	private $var;				// Array for var effects
	private $color;				// Array for saved colors

	private $nodelvl = 0;		// Current node level (to manage nested node, ie. loops)
	private $indent;			// Indentation string for debug display

	//=====================================
	//== CONSTRUCTOR ==
	//=====================================
	function __construct($debug=false){
		global $HueAPI;

		$this->debug = $debug;		// Init debug mode

		$this->ha = &$HueAPI;		// HueApi object have to already exist
		$HueAPI->loadInfo("scenes");// pre-load scenes if needed

		$this->xr[$this->nodelvl] = new XMLReader();// Create a new XMLReader object
		$this->indent = str_repeat("&nbsp;",4);
	} // __construct

	//=====================================
	//== PUBLIC METHODS ==
	//=====================================
	//-------------------------------------
	// Run an effect
	//-------------------------------------
	// Param : <effect file name to run>
	//-------------------------------------
	// Effect file name is supplied without its extension.
	// The file must exist within 'effects' directory
	// and must have a '.xml' extension.
	//
	// Remark : it's possible to open and process several different files with the same object
	//-------------------------------------
	function runEffect($effectName){
		if ($this->openEffect($effectName)){
			// Initialize array of var (needed if several effects launched with same object)
			unset($this->var);
			$this->var = array ();

			// Read effect file
			$this->processXMLContent();

			$this->xr[$this->nodelvl]->close();
		}
	} // runEffect

	//-------------------------------------
	// Get effect description from <effect> tag
	//-------------------------------------
	// Param : <effect file name>
	// Return : array containing effect descriptions
	//-------------------------------------
	function getDescription($effectName){
		$desc = array();
		if ($this->openEffect($effectName)){
			// Get Name and Comment attributes from tag effect
			while ($this->xr[$this->nodelvl]->read()){
				if ($this->xr[$this->nodelvl]->nodeType == XMLREADER::ELEMENT){
					if (strtolower($this->xr[$this->nodelvl]->localName) == "effect"){
						while($this->xr[$this->nodelvl]->moveToNextAttribute()){ 
							$attr = strtolower($this->xr[$this->nodelvl]->name);
							if ($attr == "name" || $attr == "comment"){
								$desc[$attr] = $this->getCurrentAttributeValue();
							}
						}
						break;
					}
				}

			}
			$this->xr[$this->nodelvl]->close();
		}

		return $desc;
	} // getDescription

	//=====================================
	//== PRIVATE METHODS ==
	//=====================================
	//-------------------------------------
	// Open an effect file
	// Return : true/false if sucessful or not
	//-------------------------------------
	private function openEffect($effectName){
		global $trs;
		$filename = $this->path."/".$effectName.$this->ext; 
		$ret = false;

		if ($this->xr[$this->nodelvl]->open($filename)){$ret = true;}
	   	else {echo $trs["Problem_for_opening_effect"]." $filename.";}

		return $ret;
	} // openEffect

	//-------------------------------------
	// Get value of current attribute from litteral or variable
	// Return : attribute value
	//-------------------------------------
	private function getCurrentAttributeValue(){
		$val = $this->xr[$this->nodelvl]->value;
		if (substr($val,0,1) == '$'){
			$varname = strtolower(substr($val,1));
			$val = $this->var[$varname];
		}
		return $val;
	} // getCurrentAttributeValue

	//-------------------------------------
	// Process xml content
	//-------------------------------------
	private function processXMLContent(){
		// Read effect file
		while ($this->xr[$this->nodelvl]->read()){
			// Process only Element
			switch($this->xr[$this->nodelvl]->nodeType){
				case (XMLREADER::ELEMENT) :
					switch (strtolower($this->xr[$this->nodelvl]->localName)){
						case "var" : // Assign variable
							$this->processVar();
							break;

						case "light" : // Launch command on lamp
						case "group" :
							$this->processLamp();
							break;

						case "timer" : // Execute timer
							$this->processTimer();
							break;

						case "getcolor" : // Save light color values (x,y,bri)
							$this->getColor();
							break;

						case "setcolor" : // Set light or group color from a previous saved color
							$this->setColor();
							break;

						case "loop" : // Init loop
							$this->processLoop();
							break;

						case "scene" : // Activate a named scene (activation by scene code is made with group tag)
							$this->processScene();
							break;

						case "effect" : // display effect name if debug
							if ($this->debug){
								while($this->xr[$this->nodelvl]->moveToNextAttribute()){ 
									if(strtolower($this->xr[$this->nodelvl]->name) == "name"){
										echo str_repeat($this->indent,$this->nodelvl);
										echo "<SPAN CLASS=effect-tag>effect</SPAN> ";
										echo "<SPAN CLASS=effect-value>".$this->getCurrentAttributeValue()."</SPAN><BR>";
										break;
									}
								}
							}
							break;
					}
					break;
			}
		}
	} // processXMLContent

	//-------------------------------------
	// Start a loop
	//-------------------------------------
	private function processLoop(){
		// Get repeat value
		$repeat = 1;
		while($this->xr[$this->nodelvl]->moveToNextAttribute()){ 
			if(strtolower($this->xr[$this->nodelvl]->name) == "repeat"){
				$repeat = $this->getCurrentAttributeValue();
				break;
			}
		}

		// Init loop
		$xmlstr = $this->xr[$this->nodelvl]->readInnerXML(); // Save loop content
		$this->nodelvl++;
		$this->xr[$this->nodelvl] = new XMLReader();// Create a new XMLReader object for the loop
		$count = 0;

		// Loop
		while ($count < $repeat){
			$count++;
			if ($this->debug){
				echo str_repeat($this->indent,($this->nodelvl - 1));
				echo "&lt;<SPAN CLASS=effect-tag>loop</SPAN> ";
				echo "<SPAN CLASS=effect-attribute>repeat</SPAN>=<SPAN CLASS=effect-value>$repeat</SPAN> ";
				echo "<SPAN CLASS=effect-attribute>count</SPAN>=<SPAN CLASS=effect-value>$count</SPAN>&gt;<BR>";
			}
			$this->xr[$this->nodelvl]->xml("<xml>".$xmlstr."</xml>"); 	// Init loop xml content
			$this->processXMLContent();
			$this->xr[$this->nodelvl]->close();
		}
		// End loop
		unset($this->xr[$this->nodelvl]);
		$this->nodelvl--;

		if ($this->debug){echo str_repeat($this->indent,$this->nodelvl)."&lt;<SPAN CLASS=effect-tag>end loop</SPAN>&gt;<BR>";}

		// Go to next node (skip current loop node content)
		$this->xr[$this->nodelvl]->next();
	} // processLoop

	//-------------------------------------
	// Process a variable
	//-------------------------------------
	private function processVar(){
		while($this->xr[$this->nodelvl]->moveToNextAttribute()){ 
			if(strtolower($this->xr[$this->nodelvl]->name) == "name"){$name = strtolower($this->getCurrentAttributeValue());}
			else {if(strtolower($this->xr[$this->nodelvl]->name) == "value"){$value = $this->getCurrentAttributeValue();}}
   		}
		$this->var[$name] = $value;
		if ($this->debug){
			echo str_repeat($this->indent,$this->nodelvl);
			echo "<SPAN CLASS=effect-tag>var</SPAN> ";
			echo "<SPAN CLASS=effect-attribute>$name</SPAN> = <SPAN CLASS=effect-value>$value</SPAN><BR>";
		}
	} // processVar

	//-------------------------------------
	// Process Hue command for tag light or group
	//-------------------------------------
	private function processLamp(){
		$type = strtolower($this->xr[$this->nodelvl]->localName);
		$json = "";
		while($this->xr[$this->nodelvl]->moveToNextAttribute()){ 
			$value = $this->getCurrentAttributeValue();
			if(strtolower($this->xr[$this->nodelvl]->name) == "id"){$id = $value;}
			else {
				$json .= '"'.$this->xr[$this->nodelvl]->name.'":';
				if (is_numeric($value) || $value == "true" || $value == "false" || substr($value,0,1) == '['){
						$json .= $value;
				} else {$json .= '"'.$value.'"';}
				$json .= ', ';
			}
   		}
		if ($json != ""){$json = '{'.substr($json,0,-2).'}';}
		if ($this->debug){
			echo str_repeat($this->indent,$this->nodelvl);
			echo "<SPAN CLASS=effect-tag>$type</SPAN> ";
			echo "<SPAN CLASS=effect-attribute>id</SPAN>=<SPAN CLASS=effect-value>$id</SPAN> ";
			$this->colorJSON($json,false);
		}

		// Execute hue command
		if ($type == "light")	{$action = $type."s/$id/state";}
		else					{$action = $type."s/$id/action";}
		$this->colorJSON($this->ha->setInfo($action,$json));
	} // processLamp

	//-------------------------------------
	// Process named scene
	// (Use tag <group> with attribute 'scene' for activating a scene by its code)
	//-------------------------------------
	private function processScene(){
		$name = "";
		while($this->xr[$this->nodelvl]->moveToNextAttribute()){ 
			if(strtolower($this->xr[$this->nodelvl]->name) == "name"){
				$name = $this->getCurrentAttributeValue();
				break;
			}
   		}
		// Look for first scene code matching the supplied name
		// This search is required because of the scene naming from the official hue application
		if ($name != ""){
			$code = "";
			$name_len = strlen($name);
			foreach ($this->ha->info['scenes'] as $sceneid => $sval){
				if (substr($sval['name'],0,$name_len) == $name){
					$code = $sceneid;
					break;
				}
			}
			// Activate found scene
			if ($code != ""){
				if ($this->debug){
					echo str_repeat($this->indent,$this->nodelvl);
					echo "<SPAN CLASS=effect-tag>scene</SPAN> ";
					echo "<SPAN CLASS=effect-value>$name</SPAN> ";
					echo "id found=<SPAN CLASS=effect-value>$code</SPAN><BR>";}
				$this->colorJSON($this->ha->setInfo("groups/0/action",'{"scene":"'.$code.'"}'));
			}
		}
	} // processScene

	//-------------------------------------
	// Process Timer
	//-------------------------------------
	private function processTimer(){
		while($this->xr[$this->nodelvl]->moveToNextAttribute()){ 
			if(strtolower($this->xr[$this->nodelvl]->name) == "duration"){
				$duration = $this->getCurrentAttributeValue();
				break;
			}
   		}
		if ($this->debug){echo str_repeat($this->indent,$this->nodelvl)."<SPAN CLASS=effect-tag>timer</SPAN> = <SPAN CLASS=effect-value>$duration</SPAN> s<BR>";}
		usleep ($duration * 1000000);
	} // processTimer

	//-------------------------------------------------------------
	// Get color and 'on' status from a light and save it
	// Remark : color is get from colormode value (hs or xy or ct)
	//-------------------------------------------------------------
	private function getColor(){
		$id = "";
		$name = "";
		while($this->xr[$this->nodelvl]->moveToNextAttribute()){ 
			$attr = strtolower($this->xr[$this->nodelvl]->name);
			$value = $this->getCurrentAttributeValue();
			switch ($attr){
				case "id" :
					$id = $value;
					break;
				case "name" :
					$name = $value;
					break;
			}
   		}
		if ($this->debug){
			echo str_repeat($this->indent,$this->nodelvl);
			echo "<SPAN CLASS=effect-tag>getcolor</SPAN> ";
			echo "<SPAN CLASS=effect-attribute>light_id</SPAN>=<SPAN CLASS=effect-value>$id</SPAN> ";
			echo "<SPAN CLASS=effect-attribute>save_name</SPAN>=<SPAN CLASS=effect-value>$name</SPAN><BR>";
		}
		$this->ha->loadInfo("lights");

		$lstate = &$this->ha->info['lights'][$id]['state'];
		if ($lstate['on'] == ""){$this->color[$name]['on'] = "false";}
		else					{$this->color[$name]['on'] = "true";}
		$this->color[$name]['colormode'] = $lstate['colormode'];
		$this->color[$name]['bri'] = $lstate['bri'];
		switch ($lstate['colormode']){
			case "hs" :
				$this->color[$name]['hue'] = $lstate['hue'];
				$this->color[$name]['sat'] = $lstate['sat'];
				break;
			case "xy" :
				$this->color[$name]['x'] = $lstate['xy']['0'];
				$this->color[$name]['y'] = $lstate['xy']['1'];
				break;
			case "ct" :
				$this->color[$name]['ct'] = $lstate['ct'];
				break;
		}
		if ($this->debug){
			echo str_repeat($this->indent,$this->nodelvl);
			echo $this->indent."<SPAN CLASS=effect-attribute>on</SPAN>=<SPAN CLASS=effect-value>";
			echo $this->color[$name]['on']."</SPAN> ";
			echo "<SPAN CLASS=effect-attribute>colormode</SPAN>=<SPAN CLASS=effect-value>";
			echo $this->color[$name]['colormode']."</SPAN> ";
			echo "<SPAN CLASS=effect-attribute>bri</SPAN>=<SPAN CLASS=effect-value>";
			echo $this->color[$name]['bri']."</SPAN> ";
			switch ($this->color[$name]['colormode']){
				case "hs" :
					echo "<SPAN CLASS=effect-attribute>hue</SPAN>=<SPAN CLASS=effect-value>";
					echo $this->color[$name]['hue']."</SPAN> ";
					echo "<SPAN CLASS=effect-attribute>sat</SPAN>=<SPAN CLASS=effect-value>";
					echo $this->color[$name]['sat']."</SPAN>";
					break;
				case "xy" :
					echo "<SPAN CLASS=effect-attribute>x</SPAN>=<SPAN CLASS=effect-value>";
					echo $this->color[$name]['x']."</SPAN> ";
					echo "<SPAN CLASS=effect-attribute>y</SPAN>=<SPAN CLASS=effect-value>";
					echo $this->color[$name]['y']."</SPAN>";
					break;
				case "ct" :
					echo "<SPAN CLASS=effect-attribute>ct</SPAN>=<SPAN CLASS=effect-value>";
					echo $this->color[$name]['ct']."</SPAN>";
					break;
			}
			echo "<BR>";
		}
	} // getColor

	//-------------------------------------------------
	// Set saved color to a light (default) or a group
	// transitiontime in ms can me passed as attribute too
	//-------------------------------------------------
	private function setColor(){
		$id="";
		$name="";
		$type="light";
		$transitiontime="";
		while($this->xr[$this->nodelvl]->moveToNextAttribute()){ 
			$attr = strtolower($this->xr[$this->nodelvl]->name);
			$value = $this->getCurrentAttributeValue();
			switch ($attr){
				case "id" :
					$id = $value;
					break;
				case "name" :
					$name = $value;
					break;
				case "type" :
					$type = $value;
					break;
				case "transitiontime" :
					$transitiontime = $value;
					break;
			}
   		}
		if ($this->debug){
			echo str_repeat($this->indent,$this->nodelvl);
			echo "<SPAN CLASS=effect-tag>setcolor</SPAN> ";
			echo "<SPAN CLASS=effect-attribute>type</SPAN>=<SPAN CLASS=effect-value>$type</SPAN> ";
			echo "<SPAN CLASS=effect-attribute>id</SPAN>=<SPAN CLASS=effect-value>$id</SPAN> ";
			echo "<SPAN CLASS=effect-attribute>name</SPAN>=<SPAN CLASS=effect-value>$name</SPAN> ";
			echo "<SPAN CLASS=effect-attribute>transitiontime</SPAN>=<SPAN CLASS=effect-value>";
			if ($transitiontime == "")	{echo "4 ms (default)";}
		 	else 						{echo "$transitiontime ms";}
			echo "</SPAN><BR>";
		}

		if ($type == "light")	{$action = $type."s/$id/state";}
		else					{$action = $type."s/$id/action";}

		$json = '{"on" : '.$this->color[$name]['on'].',"bri" : '.$this->color[$name]['bri'].',';
		switch ($this->color[$name]['colormode']){
			case "hs" :
				$json .= '"hue" : '.$this->color[$name]['hue'].',';
				$json .= '"sat" : '.$this->color[$name]['sat'];
				break;
			case "xy" :
				$json .= '"xy" : ['.$this->color[$name]['x'].','.$this->color[$name]['y'].']';
				break;
			case "ct" :
				$json .= '"ct" : '.$this->color[$name]['ct'];
				break;
		}
		if ($transitiontime != ""){
			$json .= ',"transitiontime" : '.$transitiontime;
		}
		$json .= "}";
		if ($this->debug){
			echo str_repeat($this->indent,$this->nodelvl);
			echo $this->indent."json=";
			$this->colorJSON($json,false);
		}

		$this->colorJSON($this->ha->setInfo($action,$json));
	} // setColor

	//------------------------------------------------------------
	// Echo colored json in parameter if debug=true
	// Parameters :
	// json : json string to colorize
	// useIndent : use indentation for echo or not (default=true)
	//------------------------------------------------------------
	private function colorJSON($json,$useIndent=true){
		if ($this->debug){
			$json = preg_replace('/^\[(.*)\]$/','\1',$json); // from hue response
			$json = preg_replace('/{("error"):/','{<SPAN CLASS=error>\1</SPAN>:',$json);
			$json = preg_replace('/("description":")([^"]*")/','\1<SPAN CLASS=error>\2</SPAN>:',$json);
			$json = preg_replace('/([{,])([^:,{}<>]*):/','\1<SPAN CLASS=effect-attribute>\2</SPAN>:',$json);
			$json = preg_replace('/:([^,{}<>]*)([,}])/',':<SPAN CLASS=effect-value>\1</SPAN>\2',$json);
			$json = preg_replace('/,/','<SPAN CLASS=effect-comment>,</SPAN>',$json);
			$json = preg_replace('/([{}])/','<SPAN CLASS=effect-comment>\1</SPAN>',$json);
			if ($useIndent){echo str_repeat($this->indent,($this->nodelvl + 1));}
			echo $json."<BR>";
		}
	} // colorJSON
}// Hue Effect

$HueEffect = new HueEffect(@$debug);
?>
