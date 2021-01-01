<?php
require_once 'Common.php';

$client = new \Haassie\Timeular\Client($apiKey, $apiSecret);
$startDate = new \DateTime();
$startDate->modify('-3 days');

$endDate = new \DateTime();

var_dump($startDate->format('d-m-Y H:i:s'));
var_dump($endDate->format('d-m-Y H:i:s'));
var_dump($client->getTimeEntries($startDate, $endDate));
