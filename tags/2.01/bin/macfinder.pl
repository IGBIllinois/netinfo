#!/usr/bin/perl

use Net::SNMP;
use DBI;
use Net::MAC::Vendor;

$mysqluser='root';
$mysqlpass='igb123';

$community='morpheus';
$vlan=281;
$fullcom="$community\@$vlan";
#print "$fullcom\n";
#print "running macfinder\n";

#each key of machines is associated with an array
#that array starts with the cummunity name, and is followed by all ports to be ignored
#ignored ports are usually uplinks or downlinks

%machines=(
	"sw-gate-4510"=>[$fullcom,'Te1/1','Te1/2','Gi3/1','Gi3/2','Gi3/3','Gi3/4','Gi3/5','Gi3/6'],
	"sw-gate-4510-2"=>[$fullcom,'Te1/1','Te1/2','Gi3/1','Gi3/2','Gi3/4','Gi3/5','Gi3/6'],
	"sw-cn-4510"=>[$fullcom,'Te1/1','Te1/2','Gi3/1','Gi3/2','Gi3/3','Gi3/4','Gi3/5','Gi3/6'],
	"sw-cs-4510"=>[$fullcom,'Te1/1','Te1/2','Gi3/1','Gi3/2','Gi3/3','Gi3/4','Gi3/5','Gi3/6'],
	"sw-1n-4510"=>[$fullcom,'Te1/1','Te1/2','Gi3/1','Gi3/2','Gi3/3','Gi3/4','Gi3/5','Gi3/6'],
	"sw-1s-4510"=>[$fullcom,'Te1/1','Te1/2','Gi3/1','Gi3/2','Gi3/3','Gi3/4','Gi3/5','Gi3/6'],
	"sw-2n-4510"=>[$fullcom,'Te1/1','Te1/2','Gi3/1','Gi3/2','Gi3/3','Gi3/4','Gi3/5','Gi3/6'],
	"sw-2s-4510"=>[$fullcom,'Te1/1','Te1/2','Gi3/1','Gi3/2','Gi3/3','Gi3/4','Gi3/5','Gi3/6'],
	"sw-3n-4510"=>[$fullcom,'Te1/1','Te1/2','Gi3/1','Gi3/2','Gi3/3','Gi3/4','Gi3/5','Gi3/6'],
	"sw-3s-4510"=>[$fullcom,'Te1/1','Te1/2','Gi3/1','Gi3/2','Gi3/3','Gi3/4','Gi3/5','Gi3/6']
	);

$macoid='.1.3.6.1.2.1.17.4.3.1.1';
$bridgeoid='.1.3.6.1.2.1.17.4.3.1.2';
$ifoid='.1.3.6.1.2.1.17.1.4.1.2';
$nameoid='.1.3.6.1.2.1.31.1.1.1.1';

Net::MAC::Vendor::load_cache();

#system("date");

$dbh = DBI->connect("DBI:mysql:database=macwatch;host=localhost",
                         $mysqluser, $mysqlpass,{'RaiseError' => 1});

foreach $machine (keys %machines){
  #print "$machine\n";
  $password=shift @{$machines{$machine}};
  ($session, $error) = Net::SNMP->session(
	-hostname => $machine,
	-community => $password,
	);

  if(!defined($session)) {
    printf("Session ERROR: %s.\n", $error);
    exit 1;
  }

  $result = $session->get_table(
	-baseoid	=> $macoid
	);

  if(!defined($result)) {
    printf("Result ERROR: %s.\n", $session->error);
    $session->close;
    exit 1;
  }

  @keylist=keys %{$session->var_bind_list};
  %macaddresses=%{$session->var_bind_list};

  $result = $session->get_table(
	-baseoid	=> $bridgeoid
	);

  if(!defined($result)) {
    printf("Result ERROR: %s.\n", $session->error);
    $session->close;
    exit 1;
  }

  %bridgenumbers=%{$session->var_bind_list};

  $result = $session->get_table(
	-baseoid	=> $ifoid
	);

  if(!defined($result)) {
    printf("Result ERROR: %s.\n", $session->error);
    $session->close;
    exit 1;
  }

  %ifnumbers=%{$session->var_bind_list};

  $result = $session->get_table(
	-baseoid	=> $nameoid
	);

  if(!defined($result)) {
    printf("Result ERROR: %s.\n", $session->error);
    $session->close;
    exit 1;
  }

  %names=%{$session->var_bind_list};

  foreach my $key (@keylist){
#    print "key: $key\n";
    my $bridgekey=$key;
    $bridgekey=~s/^$macoid/$bridgeoid/;
    $macaddresses{$key}=~s/^0x//;
    unless(!$names{$nameoid.'.'.$ifnumbers{$ifoid.'.'.$bridgenumbers{$bridgekey}}} or in_array($names{$nameoid.'.'.$ifnumbers{$ifoid.'.'.$bridgenumbers{$bridgekey}}},@{$machines{$machine}}) or !($macaddresses{$key}=~/[A-Fa-f0-9]{12}/)){
      my @thismac=split /(\w\w)/ , $macaddresses{$key};
      my $thismac=join ":", @thismac[1],@thismac[3],@thismac[5],@thismac[7],@thismac[9],@thismac[11];
      #print $macaddresses{$key}."\t$thismac\t".@{Net::MAC::Vendor::lookup( $thismac )}[0]."\n";
      my $vendor=@{Net::MAC::Vendor::lookup( $thismac )}[0];
      $vendor=~s/\'/\\\'/;
      my $query="replace into macwatch (switch,port,mac,vendor) values('$machine','".$names{$nameoid.'.'.$ifnumbers{$ifoid.'.'.$bridgenumbers{$bridgekey}}}."','".$macaddresses{$key}."','".$vendor."')";
      $sth=$dbh->prepare($query);
      $sth->execute or die "$query\n";
    }
  }
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

