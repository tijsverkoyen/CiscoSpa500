<?php

//require
require_once '../../../autoload.php';
require_once 'config.php';

use \TijsVerkoyen\CiscoSpa500\CiscoSpa500;

// create instance
$cisco = new CiscoSpa500(IP);

try {
//	$response = $cisco->getCallLog();
} catch (Exception $e) {
    var_dump($e);
}

// output
var_dump($response);
