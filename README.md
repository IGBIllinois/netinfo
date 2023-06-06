# Netinfo Database

[![Build Status](https://github.com/IGBIllinois/netinfo/actions/workflows/main.yml/badge.svg)](https://github.com/IGBIllinois/netinfo/actions/workflows/main.yml)

Netinfo keeps track of dhcp reservations for multiple networks and can then autogenerate the dhcpd and bind configuration files

## Requirements
* MySQL/MariaDB
* PHP >=7.2 with php-snmp,php-pdo,php-ldap,php-cli mdules
* Composer
* Apache
* DHCPD
* Bind Name Server
* LDAP to login

## Installation

* For Redhat/CentOS 8
```
yum install php php-cli php-pdo php-snmp php-ldap mariadb bind bind-chroot dhcp-server
```
* git clone https://github.com/IGB-UIUC/netinfo or download tagged tar.gz
```
git clone https://github.com/IGB-UIUC/netinfo netinfo
```
* Create mysql database
```
CREATE DATABASE netinfo CHARACTER SET utf8;
```
* Create mysql user with insert,update,select,delete privileges on the database
```
CREATE USER 'netinfo'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';
GRANT SELECT,INSERT,DELETE,UPDATE ON netinfo.* to 'netinfo'@'localhost';
```
* Import sql files from the sql directory
```
mysql -u root -p netinfo < sql/netinfo.sql
```
* Add apache config to apache configuration to point to the html directory
```
Alias /netinfo /var/www/netinfo/html
<Location /netinfo>
	AllowOverride None
	Require all granted
</Location>
```
* Copy /conf/settings.inc.php.dist to /conf/settings.inc.php
* Change the settings.inc.php to point to the mysql database and ldap server
* Run composer install to install dependencies from the root folder
```
composer install
```

# Initial Setup
* Copy conf/settings.inc.php.dist to conf/setttings.inc.php
```
cp conf/settings.inc.php.dist conf/settings.inc.php
```
* Edit conf/settings.inc.php for your environment
```
define("TITLE","Network Information Database");
define("FOOTER","");
define("SESSION_NAME","netinfo");
define("SESSION_TIMEOUT",14400);
define("COUNT",30);
define("TIMEZONE","America/Chicago");
//////////Mysql Settings//////////////
define("MYSQL_HOST","localhost");
define("MYSQL_USER","netinfo");
define("MYSQL_PASSWORD","XXXXXXXXXXXXXXXX");
define("MYSQL_DATABASE","netinfo");
define("MYSQL_PORT",3306);
define("MYSQL_SSL",false);
//////////Authentication Settings///////////////
define("LDAP_HOST","ldap.example.net");
define("LDAP_BASE_DN","dc=ldap,dc=example,dc=net");
define("LDAP_GROUP","group");
define("LDAP_BIND_USER","");
define("LDAP_BIND_PASS","");
define("LDAP_SSL",FALSE);
define("LDAP_TLS",TRUE);
define("LDAP_PORT",389);
define("ENABLE_LOG",TRUE);
define("LOG_FILE","/var/www/netinfo/log/netinfo.log");
define("SNMP_COMMUNITY","public");
```

* For cron jobs, copy conf/cron.dist to conf/cron
```
cp conf/cron.dist to conf/cron
```
* Edit conf/cron for the schedule you want
* Make symlink of conf/cron to /etc/cron.d/netinfo
```
ln -s /var/www/netinfo/conf/cron /etc/cron.d/netinfo
```
* Copy conf/log_rotate.conf.dist to conf/log_rotate.conf
```
cp conf/log_rotate.conf.dist conf/log_rorate.conf
```
* Edit conf/log_rotate.conf 
* Make symlink of conf/log_rotate.conf to /etc/logrotate.d/netinfo
```
ln -s /var/www/netinfo/conf/log_rotate.conf /etc/logrotate.d/netinfo
```

# Adding Networks and Domains
* Create domains by following guide at [docs/domain.md](docs/domain.md)
* Create networks by following guide at [docs/network.md](docs/network.md)


