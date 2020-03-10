<?php

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';
require_once 'includes/header.inc.php';

$switchResult = $db->query("select * from switches where type='building'");
$switches = [];
foreach ($switchResult as $row){
    $switches[] = ['hostname'=>$row['hostname']];
}

$vlanResult = $db->query('select * from vlans');
$vlans = [];
foreach ($vlanResult as $row){
    $vlans[$row['id']] = $row['name'];
}

PortStatMacwatchHelper::$db = $db;
for($i=0; $i<count($switches); $i++){
    $switch = new PortStatSwitch($db, $switches[$i]['hostname']);
    $switch->loadInterfaces();
    $switches[$i]['interfaces'] = $switch->interfaces;
}

$loader = new FilesystemLoader('../templates');
$twig = new Environment($loader, []);

$filter = new TwigFilter('ksort', function($array){
    ksort($array);
    return $array;
});
$twig->addFilter($filter);

$template = $twig->load('status.html.twig');
echo $template->render([
    'switches' => $switches,
    'vlans' => $vlans,
                       ]);

require_once 'includes/footer.inc.php';