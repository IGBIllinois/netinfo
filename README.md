# Netinfo Database

Netinfo keeps track of dhcp reservations for multiple networks and can then autogenerate the dhcpd and bind configuration files

## Requirements
* Mysql
* PHP
* Composer
* Apache
* DHCPD
* Bind Name Server
* LDAP to login

## Installation
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
* Create an apache alias to point to the html directory
```
Alias /netinfo /var/www/netinfo/html
```
* Copy /conf/settings.inc.php.dist to /conf/settings.inc.php
* Change the settings.inc.php to point to the mysql database and ldap server
* Run composer install to install depedencies from the root folder
```
composer install
```
* Create symlink to vendor folder from html folder
```
cd html
ln -s ../vendor vendor
```

# Initial Setup
* Create domains in the domains table manual.
```
INSERT INTO domains(name,alt_names,serial) VALUES('example.com','example.net',1);
```
* Create networks in the networks table manual.
```
INSERT INTO networks(name,network,netmask,vlan,enabled,domain_id) VALUES('public','192.168.1.0','255.255.255.0',100,1,1);
```
* Add IP addresses to namespace table.  Use the spare for the aname.  This then becomes an available ip address
```
INSERT INTO namespace(aname,ipnumber,network_id) VALUES('spare','192.168.1.1',1);
```
* Create cron job to create the dhcpd and bind conf files
```
0,15,30,45 * * * * root php /usr/local/netinfo/bin/dhcpd.php -n ALL -d /etc/dhcpd/
0,15,30,45 * * * * root php /usr/local/netinfo/bin/bind.php -n ALL -d /var/named/chroot/var/named
```
* Done


