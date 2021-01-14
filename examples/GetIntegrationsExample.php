<?php
require_once 'Common.php';

$client = new \RichardHaeser\Timeular\Client($apiKey, $apiSecret);
var_dump($client->getIntegrations());
