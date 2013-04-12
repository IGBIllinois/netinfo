#!/usr/bin/perl

use DBI;

$mysqluser='root';
$mysqlpass='igb123';
$file='/usr/local/bin/namespace/modified.txt';
$table='namespace';
$database='macwatch';
$filepath='/usr/local/bin/namespace';

$dbh = DBI->connect("DBI:mysql:database=macwatch;host=localhost",
                         $mysqluser, $mysqlpass,{'RaiseError' => 1});

$query="select modified from $table order by modified desc limit 1";

$sth=$dbh->prepare($query);
$sth->execute or die "$query\n";

$row=$sth->fetchrow_hashref();

open(TIME,$file) or die "cannot open $file for reading\n";
$time=readline(TIME);
chomp($time);
close TIME;

if($time eq ${$row}{modified}){
  #print "No Update\n";
}else{
  open(TIME,">$file") or die "cannot write to $file\n";
  print TIME ${$row}{modified};
  close TIME;
  print "Running DHCP Update\n";
  system("$filepath/dhcpupdate.pl");
  print "Check DHCP conifg\n";
  $test=`/etc/init.d/dhcpd configtest 2>&1`;
  unless($test=~/^Syntax: OK/){
     die "$test\n";
  }
  system("/etc/init.d/dhcpd restart");
  print "Running DNS Update\n";
  system("$filepath/dnsupdate.pl");
  system("/etc/init.d/named reload");
  print "Updates Finished\n";
}
