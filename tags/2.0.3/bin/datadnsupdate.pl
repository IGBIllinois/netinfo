#!/usr/bin/perl

use DBI;

$mysqluser='root';
$mysqlpass='igb123';
$table='data';
$database='macwatch';
$domain='data.igb.illinois.edu';

#pathname to serial number file
$serialpath="/usr/local/bin/namespace/dataserial.txt";
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
  }
 
#  if(${$row}{ipnumber}=~/128\.174\.124.(\d+)/){
#    $inverse124.="$1\tIN PTR ${$row}{aname}$domain.\n";
#  }elsif(${$row}{ipnumber}=~/128\.174\.125.(\d+)/){
#    $inverse125.="$1\tIN PTR ${$row}{aname}$domain.\n";
#  }elsif(${$row}{ipnumber}=~/128\.174\.126.(\d+)/){
#    $inverse126.="$1\tIN PTR ${$row}{aname}$domain.\n";
#  }elsif(${$row}{ipnumber}=~/128\.174\.127.(\d+)/){
#    $inverse127.="$1\tIN PTR ${$row}{aname}$domain.\n";
#  }elsif(${$row}{ipnumber}=~/128\.174\.50.(\d+)/){
#    $inverse50.="$1\tIN PTR ${$row}{aname}$domain.\n";
#  }else{
#    die "IP is not in our subnet\n";
#  }

}


$dnsdataheader=~s/\[SERIAL\]/$serial/;

#db.igb.illinois.edu
open(OUTPUT, ">$directory/db.$domain");
print OUTPUT $dnsdataheader.$aliasesheader.$aliases.$addressheader.$addresses;
close OUTPUT;
#print $dnsdataheader.$aliasesheader.$aliases.$addressheader.$addresses;

sub heredocs{

$dnsdataheader=<<END;
\$TTL 3h

@ IN SOA netsvc.igb.illinois.edu. duplicity.igb.illinois.edu. (
        [SERIAL]      ;serial
        3h      ;refresh after 3 hours
        1h      ;retry after 1 hour (exist)
        1w      ;expire after 1 week
        1h )    ;negative ttl cash 1 hour (doesnt exist)

        IN NS   netsvc.igb.illinois.edu.
        IN NS   duplicity.igb.illinois.edu.

;
;Mail record
;


data.igb.illinois.edu.   IN MX   0       mail.igb.illinois.edu.
                IN MX   10      duplicity.igb.illinois.edu.


END

$aliasesheader=<<END;
;
;Aliases
;
END

$addressheader=<<END;
;
;Addresses for the canonical name (cname)
;
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
