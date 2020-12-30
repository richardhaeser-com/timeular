<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/../.env');

$apiKey = $_ENV['TIMEULAR_API_KEY'];
$apiSecret = $_ENV['TIMEULAR_API_SECRET'];
