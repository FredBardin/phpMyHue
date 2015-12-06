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
// getDescription(effect_name) : get effect attribute(s) of tag <effect>
//----------------------------------------------------
// F. Bardin 14/11/2015
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

	private $nodelvl = 0;		// Current node level (to manage nested node, ie. loops)

	//=====================================
	//== CONSTRUCTOR ==
	//=====================================
	function __construct($debug=false){
		global $HueAPI;

		$this->debug = $debug;		// Init debug mode

		$this->ha = &$HueAPI;		// HueApi object have to already exist
		$HueAPI->loadInfo("scenes");// pre-load scenes if needed

		$this->xr[$this->nodelvl] = new XMLReader();// Create a new XMLReader object
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
						case ("effect") : // display effect name if debug
							if ($this->debug){
								while($this->xr[$this->nodelvl]->moveToNextAttribute()){ 
									if(strtolower($this->xr[$this->nodelvl]->name) == "name"){
										echo "Effect ".$this->getCurrentAttributeValue()."<BR>";
										break;
									}
								}
							}
							break;

						case ("loop") : // Init loop
							$name = $this->xr[$this->nodelvl]->localName;
							$this->startLoop();

							// Go to end of loop (=go to next tag, then to loop node if not already on it)
							$this->xr[$this->nodelvl]->read();
							if (strtolower($this->xr[$this->nodelvl]->localName != "loop"))
								{$this->xr[$this->nodelvl]->next($name);}
							break;

						case ("var") : // Assign variable
							$this->processVar();
							break;

						case ("light") : // Launch command on lamp
						case ("group") :
							$this->processCmd();
							break;

						case ("scene") : // Activate a named scene (activation by scene code is made with group tag)
							$this->processScene();
							break;

						case ("timer") : // Execute timer
							$this->processTimer();
							break;
					}
					break;
			}
		}
	} // processXMLContent

	//-------------------------------------
	// Start a loop
	//-------------------------------------
	private function startLoop(){
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
		$counter = 0;

		// Loop
		while ($counter < $repeat){
			$counter++;
			if ($this->debug){echo "&lt;loop repeat=$repeat count=$counter&gt;<BR>";}
			$this->xr[$this->nodelvl]->xml("<xml>".$xmlstr."</xml>"); 	// Init loop xml content
			$this->processXMLContent();
			$this->xr[$this->nodelvl]->close();
		}

		// End loop
		unset($this->xr[$this->nodelvl]);
		$this->nodelvl--;
	} // startLoop

	//-------------------------------------
	// Process a variable
	//-------------------------------------
	private function processVar(){
		while($this->xr[$this->nodelvl]->moveToNextAttribute()){ 
			if(strtolower($this->xr[$this->nodelvl]->name) == "name"){$name = strtolower($this->getCurrentAttributeValue());}
			else {if(strtolower($this->xr[$this->nodelvl]->name) == "value"){$value = $this->getCurrentAttributeValue();}}
   		}
		$this->var[$name] = $value;
		if ($this->debug){echo "var $name = $value<BR>";}
	} // processVar

	//-------------------------------------
	// Process Hue command for tag light or group
	//-------------------------------------
	private function processCmd(){
		$type = strtolower($this->xr[$this->nodelvl]->localName);
		$json = "";
		while($this->xr[$this->nodelvl]->moveToNextAttribute()){ 
			$value = $this->getCurrentAttributeValue();
			if(strtolower($this->xr[$this->nodelvl]->name) == "id"){$id = $value;}
			else {
				$json .= '"'.$this->xr[$this->nodelvl]->name.'":';
				if (is_numeric($value) || $value == "true" || $value == "false"){$json .= $value;}
				else                   {$json .= '"'.$value.'"';}
				$json .= ', ';
			}
   		}
		if ($json != ""){$json = '{'.substr($json,0,-2).'}';}
		if ($this->debug){echo "$type id=$id $json<BR>";}

		// Execute hue command
		if ($type == "light")	{$action = $type."s/$id/state";}
		else					{$action = $type."s/$id/action";}
		$this->ha->setInfo($action,$json);
	} // processCmd

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
			if ($code != ""){$this->ha->setInfo("groups/0/action",'{"scene":"'.$code.'"}');}
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
		if ($this->debug){echo "timer = $duration s<BR>";}
		usleep ($duration * 1000000);
	} // processTimer
}// Hue Effect

$HueEffect = new HueEffect(@$debug);
?>
