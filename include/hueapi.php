<?php
//================================================================
// Hue API object
//
// Create an object to handle Hue API
//----------------------------------------------------------------
// For all methods, '$action' refers to subset of the bridge url after 'api/<username>'
// ie : /lights, /groups/<group num>/action, /config, ...
//
// Public var
// - info : array containing the result of loadInfo method
//
// Public methods 
// - loadInfo($action) : load informations from bridge.
//   return a json string (depending of called method).
// 
// - setInfo($action, $content[, $method]) : set bridge informations
// 	 $content : Pair(s) of parameter/value supplied either in a php array of in a json string
// 	 $method : Optional parameter for the type of rest command, default=PUT
// 	           For reminder : PUT=update, POST=insert and DELETE=delete
// 	           Remark : if DELETE is used, $content must be empty (array or string)
//   Return command result in a php array or a json string depending on type of $content parameter.
//
// - assignLightsGroup() : add group id to lights (if several, only the biggest id)
//   Lights and groups have to be loaded (see loadinfo()) before to use this method.
//
// - loadNameIndex($action) : create a name index from a bridge object (key=name, value=id)
//   Target objects must be loaded (see loadinfo()) before to use this method.
//   The index is created with "<object>names" as entry key into 'info' array.
//
// - loadGroupsLightsNameIndex : create name index for lights of a group
// 
// F. Bardin 07/02/2015
// 20/02/2015 : Add both php array or json input/output parameters
// 20/09/2015 : Don't set config environment if init in progress
// 17/11/2020 : Add methods to create name indexes
//================================================================
// Anti-hack
if (! defined('ANTI_HACK')){exit;}

// Load config if init not in progress
if (! defined('INIT')){include "include/config.php";}

// Load translations
$trs = json_decode(implode(file('lang/text_'.$lang.'.json')),true);

//-- API Class
class HueAPI {
	//=====================================
	//== Variables ==
	//=====================================
	private $apiurl; // Shortcut for api url
	var $info = array(); // Array with loaded info

	//=====================================
	//== CONSTRUCTOR ==
	//=====================================
	function __construct(){
		global $bridgeip, $username;
		$this->apiurl = "http://$bridgeip/api/$username";
	} // __construct

	//=====================================
	//== PUBLIC METHODS ==
	//=====================================
	//-------------------------------------
	// Load hue information
	//-------------------------------------
	// Param : argument to pass to api url
	// Return a json string
	// Array info is loaded
	//-------------------------------------
	function loadInfo($action){
		$json_info = $this->getInfo($action);

		// Store info array with action result
		$ar_action = explode("/",$action);
		$current_info = &$this->info;
		foreach($ar_action as $key){
			$current_info = &$current_info[$key];
		}
		$current_info = json_decode($json_info,true);

		return $json_info;
	} // loadInfo

	//-------------------------------------
	// Set hue information
	//-------------------------------------
	// Param : argument to pass to api url, command data in an array or json [, send method (default=PUT)]
	// Return : cmd result in an array or json, depending on input content type
	//-------------------------------------
	function setInfo($action,$content,$method="PUT"){
		if (is_array($content))	{
			return json_decode($this->sendCmd($action,json_encode($content),$method),true);
		} else {
			return $this->sendCmd($action,$content,$method);
		}
	} // setInfo

	//-------------------------------------
	// Assign group id to lights array
	//-------------------------------------
	// Allow to identify lights without a group
	// Groups info must be loaded before to call the method
	// Lights info is not mandatory.
	// Updating the lights info will delete the grp id.
	// If several groups exist for a light, only the biggest id is recorded
	//-------------------------------------
	function assignLightsGroup(){
		foreach ($this->info['groups'] as $gnum => $gval){
			foreach ($gval['lights'] as $lnum){$this->info['lights'][$lnum]['grp'] = $gnum;}
		}
	} // assignLightsGroup

	//------------------------------------------
	// Load a name index for a loaded object
	//------------------------------------------
	// Target object info must be loaded before to call the method
	// Key of created index is "'$action'names"
	// Each index name contains its associated ID.
	//------------------------------------------
	function loadNameIndex($action){
		$idxname = $action."names";
		foreach ($this->info[$action] as $gnum => $gval){$this->info[$idxname][$gval['name']] = $gnum;}
		if (isset($this->info[$idxname])){ksort($this->info[$idxname]);}
	} // loadNameIndex

	//------------------------------------------
	// Load name index for groups lights
	//------------------------------------------
	// Key of created index is names 'ligthsnames' and is at same level that 'lights' in a group.
	// Each index name contains its associated ID.
	//------------------------------------------
	function loadGroupsLightsNameIndex(){
		$idxnameok = true;
		foreach ($this->info['groups'] as $gnum => $gval){
			foreach ($gval['lights'] as $lnum){
				$this->info['groups'][$gnum]['lightsnames'][$this->info['lights'][$lnum]['name']] = $lnum;
			}
			if (isset($this->info['groups'][$gnum]['lightsnames'])){ksort($this->info['groups'][$gnum]['lightsnames']);}
		}
	} // loadGroupsLightsNameIndex

	//=====================================
	//== PRIVATE METHODS ==
	//=====================================
	//-------------------------------------
	// Get informations from bridge
	//-------------------------------------
	// Param : argument to pass to api url
	// Return : json with the requested content
	//-------------------------------------
	private function getInfo($action){
		return @file("$this->apiurl/$action")[0];
	} // getInfo

	//-------------------------------------
	// Send command to bridge
	//-------------------------------------
	// Param : argument to pass to api url, command data in an array or json, send method
	// Return : json response
	//-------------------------------------
	private function sendCmd($action,$content_js,$method){
		$context = array('http'=>array(
                   	'method'=>$method,
                   	'header'=>'Content-type: application/x-www-form-urlencoded',
					'content'=>$content_js
                	)
				);
		return @file("$this->apiurl/$action",false,stream_context_create($context))[0]; 
	} // sendCmd
}// HueAPI


$HueAPI = new HueAPI();
?>
