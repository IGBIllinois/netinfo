<?php

class PortStatSwitch
{
    /**@var db $db*/
    private $db;
    private $hostname;
    private $community;
    private $snmp;
    public $interfaces = [];

    /**
     * PortStatSwitch constructor.
     * @param db   $db
     * @param      $hostname
     * @param null $community
     */
    public function __construct(db $db, $hostname, $community = null) {
        $this->db = $db;
        $this->hostname = $hostname;
        $this->community = $community;
        if ( $community != null ) {
            $this->snmp = new SNMP(SNMP::VERSION_2C, $this->hostname, $this->community);
        }
    }

    /* DISPLAY FUNCTIONS */

    // Load list of interfaces from database
    public function loadInterfaces() {
        $ports = $this->db->query('
            select 
                   snmpindex,
                   ports.descriptor,
                   ports.name,
                   adminStatus,
                   operStatus,
                   mode,
                   vlan,
                   printerfirewall,
                   allowedvlan,
                   locations.room,
                   locations.jack_number,
                   portstatus.lastUpdateTime as statusUpdated,
                   portconfig.lastUpdateTime as configUpdated
            from ports 
                left join portstatus on ports.switchstack=portstatus.switchstack and ports.descriptor=portstatus.descriptor 
                left join portconfig on ports.switchstack=portconfig.switchstack and ports.descriptor=portconfig.descriptor 
                left join switches on ports.switchstack=switches.hostname
                left join locations on ports.name = locations.port and switches.switch_id=locations.switch_id 
            where ports.switchstack=:stack
            ',
            array(':stack' => $this->hostname));
        for ( $i = 0; $i < count($ports); $i++ ) {
            $if = new PortStatInterface($ports[$i]['snmpindex'], $ports[$i]['descriptor']);
            $if->adminStatus = $ports[$i]['adminStatus'];
            $if->operStatus = $ports[$i]['operStatus'];
            $if->mode = $ports[$i]['mode'];
            $if->vlan = $ports[$i]['vlan'];
            $if->printerfirewall = $ports[$i]['printerfirewall'] == 1;
            $if->allowedvlan = $ports[$i]['allowedvlan'];
            $if->location = $ports[$i]['room'];
            $if->portname = $ports[$i]['name'];
            $if->jackname = $ports[$i]['jack_number'];
            $if->statusUpdated = $ports[$i]['statusUpdated'];
            $if->configUpdated = $ports[$i]['configUpdated'];

            // Pull in mac, ip info from the netinfo database
            $macwatch = PortStatMacwatchHelper::getMacWatchInfo($this->hostname,$if->portname);
            if($macwatch != null){
                $if->macwatch = $macwatch;
            } else {
                $if->macwatch = [];
            }

            if ( !isset($this->interfaces[$if->descArray[0]]) ) {
                $this->interfaces[$if->descArray[0]] = [];
            }
            if ( !isset($this->interfaces[$if->descArray[0]][$if->descArray[1]]) ) {
                $this->interfaces[$if->descArray[0]][$if->descArray[1]] = [];
            }
            $this->interfaces[$if->descArray[0]][$if->descArray[1]][$if->descArray[2]] = $if;
        }
    }

    /* SNMP UPDATE FUNCTIONS */

    // Load list of interfaces into the database from SNMP
    public function pollInterfaces() {
        $this->interfaces = [];
        $interfaces = $this->snmp->walk("IF-MIB::ifDescr");
        $ifnames = $this->snmp->walk("IF-MIB::ifName");
        $keys = array_keys($interfaces);
        for ( $i = 0; $i < count($keys); $i++ ) {
            $matches = [];
            if ( preg_match(
                    '/STRING: (GigabitEthernet[0-9]+\/[0-9]+\/[0-9]+)$/uUm', $interfaces[$keys[$i]], $matches) === 1 ) {
                $index = [];
                if ( preg_match('/IF-MIB::ifDescr.([0-9]+)$/uUm', $keys[$i], $index) ) {
                    preg_match('/STRING: (Gi[0-9]+\/[0-9]+\/[0-9]+)$/uUm', $ifnames["IF-MIB::ifName.".$index[1]], $portname);
                    $int = new PortStatInterface($index[1], $matches[1]);
                    $int->portname = $portname[1];
                    $this->interfaces[] = $int;
                }
            }
        }

        // Add in the port info entry
        $existingportquery = $this->db->get_link()
                                      ->prepare(
                                          'select * from ports where switchstack=:stack and descriptor=:descriptor');
        $insertquery = $this->db->get_link()
                                ->prepare(
                                    'insert into ports (switchstack,snmpindex,descriptor,desc1,desc2,desc3,name) values (:stack,:snmpindex,:descriptor,:desc1,:desc2,:desc3,:name)');
        for ( $i = 0; $i < count($this->interfaces); $i++ ) {
            $existingportquery->execute(
                [':stack' => $this->hostname, ':descriptor' => $this->interfaces[$i]->descriptor]);
            if ( $existingportquery->rowCount() == 0 ) {
                $insertquery->execute(
                    [
                        ':stack' => $this->hostname,
                        ':snmpindex' => $this->interfaces[$i]->index,
                        ':descriptor' => $this->interfaces[$i]->descriptor,
                        ':desc1' => $this->interfaces[$i]->descArray[0],
                        ':desc2' => $this->interfaces[$i]->descArray[1],
                        ':desc3' => $this->interfaces[$i]->descArray[2],
                        ':name' => $this->interfaces[$i]->portname,
                    ]);
            }
        }
    }

    public function pollAdminStatus() {
        $stati = $this->snmp->walk("IF-MIB::ifAdminStatus");
        $insertquery = $this->db->get_link()
                                ->prepare(
                                    "insert into portstatus (switchstack,descriptor,adminStatus,lastUpdateTime) values (:stack,:descriptor,:adminstatus,NOW()) on duplicate key update adminStatus=:adminstatus, lastUpdateTime=NOW()");
        for ( $i = 0; $i < count($this->interfaces); $i++ ) {
            $status = 0;
            if ( $stati["IF-MIB::ifAdminStatus." . $this->interfaces[$i]->index] == 'INTEGER: up(1)' ) {
                $status = 1;
            }
            $this->interfaces[$i]->adminStatus = $status;
            $insertquery->execute(
                [
                    ':stack' => $this->hostname,
                    ':descriptor' => $this->interfaces[$i]->descriptor,
                    ':adminstatus' => $status,
                ]);
        }
    }

    public function pollOperStatus() {
        $stati = $this->snmp->walk("IF-MIB::ifOperStatus");
        $insertquery = $this->db->get_link()
                                ->prepare(
                                    "insert into portstatus (switchstack,descriptor,operStatus,lastUpdateTime) values (:stack,:descriptor,:operstatus,NOW()) on duplicate key update operStatus=:operstatus, lastUpdateTime=NOW()");
        for ( $i = 0; $i < count($this->interfaces); $i++ ) {
            $status = 0;
            if ( $stati["IF-MIB::ifOperStatus." . $this->interfaces[$i]->index] == 'INTEGER: up(1)' ) {
                $status = 1;
            }
            $this->interfaces[$i]->operStatus = $status;
            $insertquery->execute(
                array(
                    ':stack' => $this->hostname,
                    ':descriptor' => $this->interfaces[$i]->descriptor,
                    ':operstatus' => $status,
                ));
        }
    }

    public function sortInterfaces() {
        usort($this->interfaces, "NICE_Interface::cmp");
    }

    public function dumpIF() {
        var_dump($this->interfaces);
    }

    /**
     * @return mixed
     */
    public function getHostname() {
        return $this->hostname;
    }


}