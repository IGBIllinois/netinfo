#!/usr/bin/perl

use DBI;

$mysqluser='root';
$mysqlpass='igb123';
$table='namespace';
$database='macwatch';
$domain='.igb.illinois.edu';
$olddomain='.igb.uiuc.edu';

#pathname to serial number file
$serialpath="/usr/local/bin/namespace/serial.txt";
#$serialpath="serial.txt";

#directory where files are stored
$directory='/var/named/chroot/var/named/';
#$directory="named/";

open(SERIAL,$serialpath) or die "cannot open for reading\n";
$serial=readline(SERIAL);
chomp($serial);
close SERIAL;

unless($serial=~/^\d+$/){
  die "Serial Number not valid\n";
}else{
  $serial++;
  open(SERIAL,">$serialpath") or die "cannot open for writing\n";
  print SERIAL $serial;
  close SERIAL;
}

heredocs();

$dbh = DBI->connect("DBI:mysql:database=macwatch;host=localhost",
                         $mysqluser, $mysqlpass,{'RaiseError' => 1});

$query="select * from $table where aname not like 'spare'";

$sth=$dbh->prepare($query);
$sth->execute or die "$query\n";

@uniquenames=();
$addresses='';
$aliases='';
$oldaliases='';
$inverse124=$inverse125=$inverse126=$inverse127='';

while($row=$sth->fetchrow_hashref()){
  if(in_array(${$row}{aname}, @uniquenames)){
    die "Could not update DNS non-unique alias or cname";
  }else{
    push @uniquenames, ${$row}{aname};
  }
  $addresses.="${$row}{aname}\tIN A\t${$row}{ipnumber}\n";
  @aliases=split /,/, ${$row}{alias};
  foreach $alias (@aliases){
    if(in_array($alias, @uniquenames)){
       
      die "Could not update DNS non-unique alias or cname: $alias";
    }else{
      push @uniquenames, $alias;
    }
    $aliases.="$alias$domain.\tIN CNAME\t${$row}{aname}$domain.\n";
    $oldaliases.="$alias$olddomain.\tIN CNAME\t${$row}{aname}$olddomain.\n";
  }
  
  if(${$row}{ipnumber}=~/128\.174\.124.(\d+)/){
    $inverse124.="$1\tIN PTR ${$row}{aname}$domain.\n";
  }elsif(${$row}{ipnumber}=~/128\.174\.125.(\d+)/){
    $inverse125.="$1\tIN PTR ${$row}{aname}$domain.\n";
  }elsif(${$row}{ipnumber}=~/128\.174\.126.(\d+)/){
    $inverse126.="$1\tIN PTR ${$row}{aname}$domain.\n";
  }elsif(${$row}{ipnumber}=~/128\.174\.127.(\d+)/){
    $inverse127.="$1\tIN PTR ${$row}{aname}$domain.\n";
  }elsif(${$row}{ipnumber}=~/128\.174\.50.(\d+)/){
    $inverse50.="$1\tIN PTR ${$row}{aname}$domain.\n";
  }else{
    warn "IP is not in our subnet\n";
  }
}

$dnsheader=~s/\[SERIAL\]/$serial/;
$dnsillinoisheader=~s/\[SERIAL\]/$serial/;

#db.124.174.128
open(OUTPUT, ">$directory/db.124.174.128");
print OUTPUT $dnsheader."\n\n".$addressheader.$inverse124;
close OUTPUT;

#db.125.174.128
open(OUTPUT, ">$directory/db.125.174.128");
print OUTPUT $dnsheader."\n\n".$addressheader.$inverse125;
close OUTPUT;

#db.126.174.128
open(OUTPUT, ">$directory/db.126.174.128");
print OUTPUT $dnsheader."\n\n".$addressheader.$inverse126;
close OUTPUT;

#db.127.174.128
open(OUTPUT, ">$directory/db.127.174.128");
print OUTPUT $dnsheader."\n\n".$addressheader.$inverse127;
close OUTPUT;

#db.50.174.128
open(OUTPUT, ">$directory/db.50.174.128");
print OUTPUT $dnsheader."\n\n".$addressheader.$inverse50;
close OUTPUT;

#db.igb.illinois.edu
open(OUTPUT, ">$directory/db$domain");
print OUTPUT $dnsillinoisheader.$aliasesheader.$aliases.$addressheader.$addresses;
close OUTPUT;

#db.igb.uiuc.edu
open(OUTPUT, ">$directory/db$olddomain");
print OUTPUT $dnsheader.$aliasesheader.$oldaliases.$addressheader.$addresses;
close OUTPUT;

sub heredocs{

$dnsheader=<<END;
\$TTL 3h

@ IN SOA netsvc.igb.illinois.edu. duplicity.igb.illinois.edu. (
        [SERIAL]      ;serial
        3h      ;refresh after 3 hours
        1h      ;retry after 1 hour (exist)
        1w      ;expire after 1 week
        1h )    ;negative ttl cash 1 hour (doesnt exist)

        IN NS   netsvc.igb.illinois.edu.
        IN NS   duplicity.igb.illinois.edu.
	IN NS	dns1.illinois.edu.
	IN NS	dns2.illinois.edu.
	IN NS	dns1.iu.edu.
	
;
;Mail record
;


igb.uiuc.edu.   IN MX   0       mail.igb.illinois.edu.
                IN MX   10      duplicity.igb.illinois.edu.


END

$dnsillinoisheader=<<END;
\$TTL 3h

@ IN SOA netsvc.igb.illinois.edu. duplicity.igb.illinois.edu. (
        [SERIAL]      ;serial
        3h      ;refresh after 3 hours
        1h      ;retry after 1 hour (exist)
        1w      ;expire after 1 week
        1h )    ;negative ttl cash 1 hour (doesnt exist)

        IN NS   netsvc.igb.illinois.edu.
        IN NS   duplicity.igb.illinois.edu.
	IN NS	dns1.illinois.edu.
	IN NS	dns2.illinois.edu.
	IN NS	dns1.iu.edu.
;
;Mail record
;


igb.illinois.edu.   IN MX   0       mail.igb.illinois.edu.
                IN MX   10      duplicity.igb.illinois.edu.
		IN A 128.174.124.77


END

$aliasesheader=<<END;
;
;Aliases
;
sysbio	IN CNAME	www.cepbrowser.org.
END

$addressheader=<<END;
;
;Addresses for the canonical name (cname)
;
sw-cn-4510	IN A	172.22.87.9
END
}

sub in_array {
  my $needle=shift @_;
  my @haystack=@_;
  foreach my $piece (@haystack){
    if($piece eq $needle){
      return 1;
    }
  }
  return 0;
}
