<?php
if (!defined("WHMCS")) {
	die("This file cannot be accessed directly");
}

/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related capabilities and
 * settings.
 *
 * @see https://developers.whmcs.com/payment-gateways/meta-data-params/
 *
 * @return array
 */
function monee_MetaData() {
	return array(
		'DisplayName' => 'Модуль для приема платежей через Monee',
		'APIVersion' => '1.2',
		'DisableLocalCredtCardInput' => true,
		'TokenisedStorage' => false,
	);
}

/**
 * Define gateway configuration options.
 *
 * The fields you define here determine the configuration options that are
 * presented to administrator users when activating and configuring your
 * payment gateway module for use.
 *
 * Supported field types include:
 * * text
 * * password
 * * yesno
 * * dropdown
 * * radio
 * * textarea
 *
 * Examples of each field type and their possible configuration parameters are
 * provided in the sample function below.
 *
 * @see https://developers.whmcs.com/payment-gateways/configuration/
 *
 * @return array
 */
function monee_config() {
	return array(
		// the friendly display name for a payment gateway should be
		// defined here for backwards compatibility
		'FriendlyName' => array(
			'Type' => 'System',
			'Value' => 'Monee',
		),
		'shop_uuid' => array(
			'FriendlyName' => 'UUID мерчанта',
			'Type' => 'text',
			'Size' => '25',
			'Default' => '',
			'Description' => '',
		),
	);
}

/**
 * Payment link.
 *
 * Required by third party payment gateway modules only.
 *
 * Defines the HTML output displayed on an invoice. Typically consists of an
 * HTML form that will take the user to the payment gateway endpoint.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/third-party-gateway/
 *
 * @return string
 */
function monee_link($params) {
	$shop_uuid = $params['shop_uuid'];
	$sum = $params['amount'];

	$comment = $params['description'];
	$invoice = $params['invoiceid'].'-'.time();

	$url = "https://api.monee.pro/payment/create";

	$data = [
		'shop_to' => $shop_uuid,
		'sum' => $sum,
		'custom_fields' => $invoice,
		'comment' => $comment,
		'expire' => 1900,
		'hook_url' => $params['systemurl'].'modules/gateways/callback/monee.php',
		'success_url' => $params['returnurl']
	];
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$response = json_decode(curl_exec($ch), true);
	curl_close($ch);

	return '<form action="'.$response['url'].'"><input type="submit" value="'.$params['langpaynow'].'"></form>';
}
