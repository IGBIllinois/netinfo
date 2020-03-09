<?php

class settings {

	private const SNMP_COMMUNITY = "public";
	private const TIMEZONE = "UTC";
	private const DEBUG = false;

	public static function get_version() {
		return VERSION;
	}

	public static function get_title() {
		return TITLE; 
	}


	public static function get_website_url() {
		return WEBSITE_URL;
	}


	public static function get_snmp_community() {
		if (defined("SNMP_COMMUNITY") && (SNMP_COMMUNITY != "")) {
			return SNMP_COMMUNITY;
		}
		return self::SNMP_COMMUNITY;
	}

	 public static function log_enabled() {
                return ENABLE_LOG;
        }

	 public static function get_log_file() {
                if (self::log_enabled() && !file_exists(LOG_FILE)) {
                        touch(LOG_FILE);
                }
                return LOG_FILE;

        }

	public static function get_debug() {
		if (defined("DEBUG") && (DEBUG != "")) {
			return DEBUG;
		}
		return self::DEBUG;
	}
	public static function get_timezone() {
		if (defined("TIMEZONE") && (TIMEZONE != "")) {
			return TIMEZONE;
		}
		return self::TIMEZONE;
	}

}
