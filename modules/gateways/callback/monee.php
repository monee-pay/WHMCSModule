<?php
require_once __DIR__ . '/../../../init.php';
App::load_function('gateway');
App::load_function('invoice');

if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $ipAddress = $_SERVER['HTTP_CF_CONNECTING_IP'];
} else {
    $ipAddress = $_SERVER['REMOTE_ADDR'];
}

// Detect module name from filename.
$gatewayModuleName = basename(__FILE__, '.php');

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Die if module is not active.
if (!$gatewayParams['type']) {
	die('Module Not Activated');
}

$content = trim(file_get_contents("php://input"));
$decoded = json_decode($content, true);

if(!is_array($decoded)){
    die();
}

$ips = json_decode(file_get_contents('https://api.monee.pro/ips'));
if (isset($ips->list) && !in_array($ipAddress, $ips->list)) die('Unknown IP');

$invoice = $decoded['custom_fields'];
$invoice_id = explode('-', $invoice);
$invoice_id = $invoice_id[0];

$invoice_id = checkCbInvoiceID($invoice_id, $gatewayParams['name']);
$sum = $decoded['amount'];
$uuid = $decoded['invoice_id'];
$shop_id = $decoded['shop_id'];

$status = $decoded['status'];

if ($status == 'success' && $shop_id = $gatewayParams['shop_uuid']) {
	addInvoicePayment($invoice_id, $uuid, 0, 0, $gatewayModuleName);
} else {
	logTransaction($gatewayParams['name'], 'Payment unsuccessful', 'Failure');
}