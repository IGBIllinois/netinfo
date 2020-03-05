<?php

class settings {

	const SNMP_COMMUNITY = "public";

	public static function get_version() {
		return __VERSION__;
	}

	public static function get_title() {
		return __TITLE__; 
	}


	public static function get_website_url() {
		return __WEBSITE_URL__;
	}


	public static function get_snmp_community() {
		if (defined(__SNMP_COMMUNITY__) && (__SNMP_COMMUNITY__ != "")) {
			return __SNMP_COMMUNITY__;
		}
		return self::SNMP_COMMUNITY;
	}

	 public static function log_enabled() {
                return __ENABLE_LOG__;
        }

	 public static function get_log_file() {
                if (!file_exists(__LOG_FILE__)) {
                        touch(__LOG_FILE__);
                }
                return __LOG_FILE__;

        }


}
