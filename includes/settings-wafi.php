<?php
/**
 * Settings for Wafi Gateway.
 *
 * @package WooCommerce\Classes\Payment
 */

defined( 'ABSPATH' ) || exit;

return array(

	
	'enabled'               => array(
		'title'   => __( 'Enable/Disable', 'wafi-payment-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable Wafi', 'wafi-payment-for-woocommerce' ),
		'default' => 'no',
	),

	'debug'                 => array(
		'title'       => __( 'Debug log', 'wafi-payment-for-woocommerce' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable logging', 'wafi-payment-for-woocommerce' ),
		'default'     => 'no',
		'description' => sprintf( __( 'Log Wafi events'))
	),

	'testmode'              => array(
		'title'       => __( 'Test mode', 'wafi-payment-for-woocommerce' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable test mode', 'wafi-payment-for-woocommerce' ),
		'default'     => 'no',
		'description' => sprintf( __( 'To test your Woocommerce installation, you can perform test transactions	'))
	),
	 'api_details'           => array(
	 	'title'       => __( 'API Credentials', 'wafi-payment-for-woocommerce' ),
	 	'type'        => 'title',
	 	'description' => sprintf( __( 'Please Provide your Wafi API credentials' )
	 ),
	),
	 'api_key'          => array(
	 	'title'       => __( 'Live API key', 'wafi-payment-for-woocommerce' ),
	 	'type'        => 'text',
	 	'description' => __( 'Get your live API key from your Wafi dashboard .', 'wafi-payment-for-woocommerce' ),
	 	'default'     => '',
	 	'desc_tip'    => true,
	 
	 ),
	 
	 'test_api_key'  => array(
	 	'title'       => __( 'Test API key', 'wafi-payment-for-woocommerce' ),
	 	'type'        => 'text',
	 	'description' => __( 'Get your test API key from your Wafi dashboard .', 'wafi-payment-for-woocommerce' ),
	 	'default'     => '',
	 	'desc_tip'    => true,
	
	 ),
	 'clientId'  => array(
	 	'title'       => __( 'Client ID', 'wafi-payment-for-woocommerce' ),
	 	'type'        => 'text',
	 	'description' => __( 'Get your client ID from your Wafi dashboard .', 'wafi-payment-for-woocommerce' ),
	 	'default'     => '',
	 	'desc_tip'    => true,

	 ),
	 'client_name'  => array(
	 	'title'       => __( 'Client Name', 'wafi-payment-for-woocommerce' ),
	 	'type'        => 'text',
	 	'description' => __( 'Please provide your name to be displayed on the learn more modal .', 'wafi-payment-for-woocommerce' ),
	 	'default'     => '',
	 	'desc_tip'    => true,

	 ),

	);