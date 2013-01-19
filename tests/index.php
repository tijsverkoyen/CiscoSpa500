<?php

//require
require_once '../../../autoload.php';
require_once 'config.php';

use \TijsVerkoyen\CiscoSpa500\CiscoSpa500;

// create instance
$cisco = new CiscoSpa500(IP);

try {
    $response = $cisco->getCallLog();
    $response = $cisco->updatePersonalDirectory(
        array(
            array('n' => 'John Doe', 'p' => '01234567890'),
            array('n' => 'Foo Bar', 'p' => '1234567890'),
        )
    );
} catch (Exception $e) {
    var_dump($e);
}

// output
var_dump($response);
