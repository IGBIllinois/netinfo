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
		if ((isset(__SNMP_COMMUNITY__) && (__SNMP_COMMUNITY__ != "")) {
			return __SNMP_COMMUNITY__;
		}
		else {
			return self::SNMP_COMMUNITY;
		}
	}




}
