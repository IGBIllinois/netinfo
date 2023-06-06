# Create Network

* Currently we have to create a network manually using an SQL query.  Below are table columns that need populating
* domain_id - ID of domain for the network.  This is from the domains table
* name - Name of the network
* network - IP network (ie 192.168.1.0)
* netmask - Subnet mask of network (ie 255.255.255.0)
* vlan - VLAN of the network
* options - default dhcp options 
```
authoritative;
default-lease-time 7200;
max-lease-time 14400;
option subnet-mask 255.255.252.0;
option broadcast-address 172.16.43.255;
option domain-name "av.igb.illinois.edu";
option ntp-servers 172.16.40.99;
option time-offset -21600;
option routers 172.16.40.1;
option domain-name-servers 128.174.124.16, 128.174.124.17, 130.126.2.131;
```
* enabled - enable or disable dhcp

* Build SQL query
```
INSERT INTO networks(domain_id,name,network,netmask,vlan,enabled,options) 
VALUES('1','example','192.168.1.0','255.255.255.0','10','1','
authoritative;
default-lease-time 7200;
max-lease-time 14400;
option subnet-mask 255.255.255.0;
option broadcast-address 192.168.1.255;
option domain-name "example.net";
option ntp-servers time.nist.gov;
option time-offset -21600;
option routers 192.168.1.1;
option domain-name-servers 128.174.124.16, 128.174.124.17, 130.126.2.131;
')
```
* Add to /etc/dhcp/dhcpd.conf the network file
```
include "/etc/dhcp/static/example.conf"
```

# Add spare IP address
* To add spare IP addresses, get the network_id from networks table of the correct network
* Create SQL query
```
INSERT INTO namespace(ipnumber,network_id) VALUES('192.168.1.1',1);
```
* To add a bunch, you can make a bash script
```
#!/bin/bash
for i in {1...254}
do
	mysql -u root -p netinfo -e "INSERT INTO namespace(ipnumber,name,etwork_id) VALUES('192.168.1.$i','spare',1);"

done
```

