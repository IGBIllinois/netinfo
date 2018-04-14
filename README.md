# Netinfo Database

Netinfo keeps track of dhcp reservations for multiple networks and can then autogenerate the dhcpd and bind configuration files

## Requirements
* Mysql
* PHP
* Apache
* DHCPD
* Bind Name Server
* LDAP to login

## Installation
* git clone https://github.com/IGB-UIUC/netinfo
* Create mysql database and mysql user which has insert,update,select,delete privileges on the database
* Import sql files from the sql directory
* Create an apache alias to point to the html directory
```
alias /usr/local/netinfo/html /netinfo
```
* Copy /conf/settings.inc.php.original to /conf/settings.inc.php
* Change the settings.inc.php to point to the mysql database and ldap server

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
0,15,30,45 * * * * root php /usr/local/netinfo/bin/dhcpd.php ALL /etc/dhcpd/
0,15,30,45 * * * * root php /usr/local/netinfo/bin/bind.php ALL /var/named/chroot/var/named
```
* Done


