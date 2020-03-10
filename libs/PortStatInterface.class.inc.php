<?php

// This class holds all the info pertaining to a single switchport.
// The way it's set up, it's more like a struct. The work of loading
// the info is done in the NICE_Switch class.
class PortStatInterface {
	public $descriptor;
	public $descArray = array();
	public $index;
	public $adminStatus;
	public $operStatus;
	public $location;
	public $portname;
	public $jackname;
	
	public $macwatch;
	
	public $statusUpdated;
	public $configUpdated;
	
	public $mode = "access";
	public $vlan = 1;
	public $printerfirewall = false;
	public $allowedvlan = null;

	public function __construct($index, $descriptor){
		$this->index = $index;
		$this->descriptor = $descriptor;
		$matches = array();
		preg_match('/.*([0-9]+)\/([0-9]+)\/([0-9]+)$/uUm', $descriptor, $matches);
		$this->descArray = array($matches[1], $matches[2], $matches[3]);
	}
	
	public static function cmp($a,$b){
		if(intval($a->descArray[0])<intval($b->descArray[0])){
			return -1;
		} else if(intval($a->descArray[0])>intval($b->descArray[0])){
			return 1;
		} else {
			// First digit same
			if(intval($a->descArray[1])<intval($b->descArray[1])){
				return -1;
			} else if(intval($a->descArray[1])>intval($b->descArray[1])){
				return 1;
			} else {
				if(intval($a->descArray[2])<intval($b->descArray[2])){
					return -1;
				} else if(intval($a->descArray[2])>intval($b->descArray[2])){
					return 1;
				} else {
					return 0;
				}
			}
		}
	}
}