<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once __DIR__ . '/vendor/autoload.php';

$baseUri = 'http://10.100.1.70:1401/';
$username = 'ct';
$password = 'ct2017';

try {
    $jasminApiClient = new \Coriolis\Jasmin\ApiClient($baseUri, $username, $password);
    $messageId = $jasminApiClient->sendSms('0683171556', 'test depuis jasmin client http api', 'Coriolis');
    var_dump($messageId);
} catch (\Exception $e) {
    var_dump('ICI : ' . $e->getMessage() . ' | ' . $e->getCode() . ' | ' . $e->getPrevious());
}