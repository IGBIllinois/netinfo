<?php
	// Pulls in the macwatch data from the netinfo database
	class PortStatMacwatchHelper {
		public static $db = null;
		private static $macwatch = null;
		
		// Populates the macwatch array
		private static function loadMacWatch(){
			$data = self::$db->query("select m.*, n.aname, n.ipnumber from macwatch m left join namespace n on m.mac=n.hardware order by date desc");

			self::$macwatch = array();
			for($i=0; $i<count($data); $i++){
				self::$macwatch[$data[$i]['switch']][$data[$i]['port']][] = array('mac'=>$data[$i]['mac'],'ip'=>$data[$i]['ipnumber'],'date'=>$data[$i]['date'],'aname'=>$data[$i]['aname'],'vendor'=>$data[$i]['vendor']);
			}
		}
		
		// Grabs IP, MAC, and ANAME data for the given switchport
		// Required port form: GiX/0/Y
		public static function getMacWatchInfo($switch,$port){
			if(self::$macwatch == null){
				self::loadMacWatch();
			}
			if(isset(self::$macwatch[$switch]) && isset(self::$macwatch[$switch][$port])){
				return self::$macwatch[$switch][$port];
			} else {
				return null;
			}
		}
	}