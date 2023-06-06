# Create Domain
* To the domains table need to create a SQL query to add a new netowrk
* name - name of the domain (ie example.com)
* alt_names - alternatives names, optional (ie example.net)
* serial - 1
* enabled - boolean enables or disables the creation of bind config file
* header - THe [SERIAL] is a placeholder which will be replaced by the serial number when the bind config gets generated 
```
$TTL 3h

@ IN SOA netsvc.igb.illinois.edu. duplicity.igb.illinois.edu. (
        [SERIAL]      ;serial
        3h      ;refresh after 3 hours
        1h      ;retry after 1 hour (exist)
        1w      ;expire after 1 week
        1h )    ;negative ttl cash 1 hour (doesnt exist)

        IN NS   netsvc.igb.illinois.edu.
        IN NS   duplicity.igb.illinois.edu.
```
* enabled - 1
```
INSERT INTO domains(name,serial,enabled,header) VALUES('example.com','1','1','
$TTL 3h

@ IN SOA netsvc.igb.illinois.edu. duplicity.igb.illinois.edu. (
        [SERIAL]      ;serial
        3h      ;refresh after 3 hours
        1h      ;retry after 1 hour (exist)
        1w      ;expire after 1 week
        1h )    ;negative ttl cash 1 hour (doesnt exist)

        IN NS   netsvc.igb.illinois.edu.
        IN NS   duplicity.igb.illinois.edu.
`);
```

