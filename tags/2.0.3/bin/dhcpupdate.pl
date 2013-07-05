#!/usr/bin/perl

use DBI;

$mysqluser='root';
$mysqlpass='igb123';
$table='namespace';
$database='macwatch';
$outputfile='/etc/dhcpd.conf';

$dbh = DBI->connect("DBI:mysql:database=macwatch;host=localhost",
                         $mysqluser, $mysqlpass,{'RaiseError' => 1});

$query="select * from $table where aname not like 'spare' and ipnumber like '128.174.12%'";

$sth=$dbh->prepare($query);
$sth->execute or die "$query\n";

$igbbody='';
while($row=$sth->fetchrow_hashref){
  ${$row}{hardware}=~s/(\w\w)/$1:/g;
  chop(${$row}{hardware});
  $igbbody.="  host ".${$row}{aname}." {\n    hardware ethernet ${$row}{hardware};\n    fixed-address ${$row}{ipnumber};\n  }\n";
}

$query="select * from $table where aname not like 'spare' and ipnumber like '128.174.50.%'";

$sth=$dbh->prepare($query);
$sth->execute or die "$query\n";

$icytbody='';
while($row=$sth->fetchrow_hashref){
  ${$row}{hardware}=~s/(\w\w)/$1:/g;
  chop(${$row}{hardware});
  $icytbody.="  host ".${$row}{aname}." {\n    hardware ethernet ${$row}{hardware};\n    fixed-address ${$row}{ipnumber};\n  }\n";
}

$igboutput= <<END;
ddns-update-style ad-hoc; 
ignore client-updates;

subnet 128.174.124.0 netmask 255.255.252.0{
  default-lease-time 7200;
  max-lease-time 14400;
  option subnet-mask 255.255.252.0;
  option broadcast-address 128.174.127.255;
  option routers 128.174.124.1;
  option domain-name-servers 128.174.124.16, 128.174.124.17, 128.174.5.58;
  option domain-name "igb.uiuc.edu";

[BODYTEXT]
}
END

$icytoutput= <<END;
subnet 128.174.50.0 netmask 255.255.255.0{
  default-lease-time 7200;
  max-lease-time 14400;
  option subnet-mask 255.255.255.0;
  option broadcast-address 128.174.50.255;
  option routers 128.174.50.1;
  option domain-name-servers 128.174.124.16, 128.174.124.17, 128.174.5.58;
  option domain-name "igb.uiuc.edu";

[BODYTEXT]
}
END

$igboutput=~s/\[BODYTEXT\]/$igbbody/;
$icytoutput=~s/\[BODYTEXT\]/$icytbody/;


open(OUTPUT, ">$outputfile");
print OUTPUT $igboutput.$icytoutput;
close(OUTPUT);