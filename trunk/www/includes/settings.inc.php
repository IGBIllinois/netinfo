<?php
////////////////////////////////////
//
//	settings.inc.php
//
//	Settings for the scripts.
//
//	David Slater
//	May 2009
//
////////////////////////////////////

define("__VERSION__","2.0Beta");
define("__TITLE__","Institute for Genomic Biology - Network Information Database");
define("__BUILDINGS__","IGB,ERML");
define("__COUNT__",30);
//////////Mysql Settings//////////////
define("__MYSQL_HOST__","127.0.0.1");
define("__MYSQL_USER__","netinfo_user");
define("__MYSQL_PASSWORD__","ruSPE6utrAK67r");
define("__MYSQL_DATABASE__","netinfo");

//////////Authentication Settings///////////////
define("__LDAP_HOST__","authen.igb.uiuc.edu");
define("__LDAP_BASE_DN__","dc=igb,dc=uiuc,dc=edu");
define("__LDAP_PEOPLE_OU__","ou=people,dc=igb,dc=uiuc,dc=edu");
define("__LDAP_GROUP_OU__","ou=group,dc=igb,dc=uiuc,dc=edu");
define("__LDAP_GROUP__","cnrg");
define("__LDAP_BIND_USER__","");
define("__LDAP_BIND_PASS__","");
define("__LDAP_SSL__",FALSE);
define("__LDAP_PORT__",389);
?>
