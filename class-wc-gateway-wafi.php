<?php

if ( ! defined( 'ABSPATH' ) ) exit; 

/**
 * Plugin Name: Wafi Payment for WooCommerce
 * Plugin URI: https://wafi.cash
 * Description: Wafi lets customers pay securely with their bank account and earn cash back every time. With Wafi you can lower your payment processing fees by half, increase checkout rate, reduce chargebacks and boost repeat purchases | <a href="https://docs.google.com/document/d/1Stp_Rd7bYK4w2A4gHvzIkYvlBg1ckVkCcNOQby3c_O0/edit?usp=sharing" target="_blank">Documentation</a>
 * Version:     1.1.0
 * Author:      Wafi
 * Author URI:  https://wafi.cash
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * @class       Wafi_Payment_Gateway
 * @extends     WC_Payment_Gateway
 * WC requires at least: 7.0
 * WC tested up to: 8.3
 * Text Domain: wafi-payment-for-woocommerce
 */



if (! in_array( 'woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) return;
add_action('plugins_loaded','wafi_payment_init');
add_filter('woocommerce_payment_gateways', 'wafi_add_to_woo_payment_gateway');

function wafi_payment_init() {
 
    if( class_exists('WC_Payment_Gateway')){
        /**
        * Wafi_Payment_Gateway Class.
        */
        class Wafi_Payment_Gateway extends WC_Payment_Gateway{
            /**
             * Whether or not logging is enabled
             *
             * @var bool
             */
            public static $log_enabled = false;

            /**
             * Logger instance
             *
             * @var WC_Logger
             */
            public static $log = false;
         
            /**
             * Whether the test mode is enabled.
             *
             * @var bool
             */
            public $testmode;
            /**
             * Endpoint for requests to Wafi.
             *
             * @var string
             */
            protected $endpoint;
            /**
             * Wafi IP address.
             *
             * @var string
             */
            const WAFI_IP_ADDRESS = "18.218.164.177" ;



            public function __construct(){
                $this->id = 'wafi';
                $this->has_fields = false;
                $this->method_title = __('Wafi', 'wafi-payment-for-woocommerce'); 
                $this->method_description = sprintf( __( 'Wafi provides a way for merchant to accept payment via bank accounts. The customer also earns cashback anytime their make payment with Wafi. To get started, <a href="%1$s" target="_blank">Sign up</a> for a Wafi account, and <a href="%2$s" target="_blank">get your API keys and Client ID</a>.', 'wafi-payment-for-woocommerce' ), 'https://dashboard.wafi.cash/signup', 'https://dashboard.wafi.cash' );
                $this->description = __('Lower cost of payment processing with Wafi - a secure way to accept payments from your customers.', 'wafi-payment-for-woocommerce');
                $this->title          = __('Wafi', 'wafi-payment-for-woocommerce');
                $this->testmode       = 'yes' === $this->get_option( 'testmode', 'no' );
                $this->init_form_fields();
                $this->init_settings(); 
                $this->debug          = 'yes' === $this->get_option( 'debug', 'no' );
                $this->enabled            = $this->get_option( 'enabled' );
                self::$log_enabled    = $this->debug;
                $this->test_api_key = $this->get_option( 'test_api_key' );
                $this->live_api_key = $this->get_option( 'api_key' );
                $this->client_name = $this->get_option( 'client_name' );
                $this->api_key = $this->testmode ? $this->test_api_key : $this->live_api_key;
                $this->clientId = $this->get_option('clientId');
                $this->supports = array(
                    'products',
                    'refunds', 
                    
                );
                

                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
                add_action( 'admin_enqueue_scripts', array( $this, 'wafi_admin_scripts' ) );
                add_action( 'admin_notices', array( $this, 'wafi_admin_notices' ) );
                add_action('wp_enqueue_scripts', array( $this, 'wafi_enqueue_custom_scripts' ) );
                add_action('woocommerce_review_order_before_submit', array( $this, 'wafi_custom_button_for_payment_gateway' ),10, 1 );
                add_action( 'woocommerce_api_wafi_payment_gateway', array( $this, 'wafi_verify_transaction' ) );
                add_action( 'woocommerce_api_wafi_payment_gateway_webhook', array( $this, 'wafi_process_webhooks' ) );
                add_filter('woocommerce_gateway_title', array( $this, 'wafi_custom_payment_gateway_title'), 10, 2);
                add_filter('woocommerce_gateway_description', array( $this, 'wafi_custom_payment_gateway_description'), 10, 2);
                add_filter('woocommerce_get_order_item_totals', array($this, 'wafi_modify_payment_method_totals'), 10, 3);
                add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array($this, 'wafi_plugin_action_links'), 10, 3 );

               
                if ( 'yes' === $this->enabled ) {
                    add_filter( 'woocommerce_thankyou_order_received_text', array( $this, 'wafi_order_received_text' ), 10, 2 );
                }
                if ( ! $this->wafi_is_valid_for_use() ) {
                    ?>
                    <div class="inline error">
                        <p style="color: red">
                            <strong><?php echo esc_html__( 'Wafi Payment Gateway Disabled', 'wafi-payment-for-woocommerce' ); ?>:</strong>
                            <?php
                            echo sprintf(
                                esc_html__( 'Sorry, Wafi currently does not support your store currency. To use Wafi, kindly set your store currency to USD ($) %s', 'wafi-payment-for-woocommerce' ),
                                '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=general' ) ) . '">' . esc_html__( 'here', 'wafi-payment-for-woocommerce' ) . '</a>'
                            );
                            ?>
                        </p>
                    </div>
                    <?php
                    $this->enabled = 'no';
                }
                
                
            }

            function wafi_plugin_action_links( $links ) {

                $settings_link = array(
                    'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wafi' ) . '" title="' . __( 'View Wafi WooCommerce Settings', 'wafi-payment-for-woocommerce' ) . '">' . __( 'Settings', 'wafi-payment-for-woocommerce' ) . '</a>',
                );
            
                return array_merge( $settings_link, $links );
            
            }


            // modify the payment method information
            public function wafi_modify_payment_method_totals($total_rows, $order, $tax_display) {
     
                if ($order->get_payment_method() === 'wafi') {
                    $total_rows['payment_method']['value'] = 'Wafi';
                }

                return $total_rows;
            }       

        
            public function wafi_custom_payment_gateway_title($title, $payment_gateway_id) {
                if ($payment_gateway_id === 'wafi' && is_checkout()) {  
                    $title = '<div style="display: flex"><wafi-mark styleType="white" width="100"></wafi-mark></div>';
                } elseif ($payment_gateway_id === 'wafi') {
                    $title = $this->title;
                }
            
                return $title;
            }
            

            public function wafi_custom_payment_gateway_description($description, $payment_gateway_id) {
                if ($payment_gateway_id === 'wafi' && is_checkout()) {
                    if (!empty($this->client_name)) {
                        $description = sprintf(
                            '<wafi-promotion-text clientId="%1$s"></wafi-promotion-text><wafi-checkout-learn-more merchantName="%2$s"></wafi-checkout-learn-more>',
                            esc_attr($this->clientId),
                            esc_attr($this->client_name)
                        );
                    } else {
                        $description = sprintf(
                            '<wafi-promotion-text clientId="%1$s"></wafi-promotion-text><wafi-checkout-learn-more></wafi-checkout-learn-more>',
                            esc_attr($this->clientId)
                        );
                    }
                    
               
                }
                return $description;
            }
            
            public function wafi_admin_scripts() {        
                wp_enqueue_script( 'woocommerce_wafi_admin', plugins_url( '/assets/js/wafi-admin.js', __FILE__ ) );
        
            }


            public function wafi_admin_notices() {
                if ( $this->enabled === 'no' ) {
                    return;
                }
          
          
                if ( !( $this->api_key ) ) {
                  
                    echo '<div class="error"><p style="color: red">' . sprintf( esc_html__( 'To be able to use the Wafi WooCommerce plugin, please provide your Wafi API key here, you can get this from %s. ', 'wafi-payment-for-woocommerce'), 
                    '<a href="https://dashboard.wafi.cash" target="_blank">your Wafi dashboard</a>' ) . '</strong></p></div>';

                    
                    return;
                }

            }
            /**
             * Replace "Place Order" button with custom button for wafi payment gateway
             */
            public function wafi_custom_button_for_payment_gateway() {
        
                
                if ( $this->enabled === 'no' ) {
                    return;
                }
        
         
                echo '<wafi-btn id="wafiCustomPlaceOrder" btnStyle="black" btnType="pay" outline ></wafi-btn>';
                
            }




            public function wafi_enqueue_custom_scripts() {
                if ( $this->enabled === 'no' ) {
                    return;
                }

                wp_enqueue_script('jquery');
                wp_enqueue_script('wafi-checkout', 'https://checkoutscript.wafi.cash/checkout.min.js', array('jquery'), '1.0', true);
                wp_enqueue_script('wafi-custom-scripts', plugins_url('/assets/js/wafi-custom-scripts.js', __FILE__), array('jquery'), '1.0', true);
            
          
            }
            
            public function wafi_is_valid_for_use() {
                $allowed_currencies = apply_filters('woocommerce_wafi_supported_currencies', array('USD'));
            
                if (!in_array(get_woocommerce_currency(), $allowed_currencies)) {
        
                    return false;
                }
            
                return true;
            }

            public function process_admin_options() {
              
                $saved = parent::process_admin_options();
                if ( 'yes' !== $this->get_option( 'debug', 'no' ) ) {
                    if ( empty( self::$log ) ) {
                        self::$log = wc_get_logger();
                    }
                    self::$log->clear( 'wafi' );
                }
        
                return $saved;
            }


            /**
             * Logging method.
             *
             * @param string $message Log message.
             * @param string $level Optional. Default 'info'. Possible values:
             *  emergency|alert|critical|error|warning|notice|info|debug.
             */
            public static function log( $message, $level = 'info' ) {
                if ( self::$log_enabled ) {
                    if ( empty( self::$log ) ) {
                        self::$log = wc_get_logger();
                    }
                    self::$log->log( $level, $message, array( 'source' => 'wafi' ) );
                }
            }


            public function wafi_order_received_text( $text, $order ) {
             
                if ( $order->get_payment_method() === 'wafi' ) {
                    
                    $text = sprintf(
                        esc_html__( 'Thank you for paying with Wafi. ', 'wafi-payment-for-woocommerce' ),
                    );
                }
            
                return $text;
            }
            
            
            public function init_form_fields() {
                $this->form_fields = include __DIR__ . '/includes/settings-wafi.php';
            }
            /**
             * Process transaction
             * @param string order_id WC_order
             * @return void|array
             */
            public function process_payment( $order_id ) { 
                $order = wc_get_order( $order_id );
                
                if ( ! is_a( $order, 'WC_Order' ) ) {  
                    $this->log( 'Order not found ', 'error' );
                    return;
                }
                $chosen_payment_method = $order->get_payment_method();
                $amount = $order->get_total() * 100;
                $ref = wc_clean( strval( $order_id ) . '_woo-Wafi' );
                $callback_url = esc_url_raw(WC()->api_request_url( 'Wafi_Payment_Gateway' ));
                $webhook_url = esc_url_raw(WC()->api_request_url( 'Wafi_Payment_Gateway_Webhook' ));
                if ('wafi' !== $chosen_payment_method ) {
                    $this->log( 'Wrong payment method ', 'error' );
                    return;
                }
                if ( $amount <= 0 ) {
                    $this->log( 'Invalid amount ', 'error' );
                    return;
                }
    
                
                $request_body = array(
                    'currency' => 'USD',
                    'amount' => intval($amount),
                    'success_callback_url' => $callback_url,
                    'cancel_callback_url' =>  $callback_url,
                    'reference' =>$ref,
                    'webhook_url' => $webhook_url,
                    'recipient' => array(
                        'first_name' => wc_clean($order->get_billing_first_name()),
                        'last_name' => wc_clean($order->get_billing_last_name()),
                        'email' => wc_clean($order->get_billing_email()),
                        'phone' => wc_clean($order->get_billing_phone()),

                        "address" => array(
                            'street_address'  => wc_clean($order->shipping_address_1),
                            'apartment_no'  => wc_clean($order->shipping_address_2),
                            'city'       => wc_clean($order->shipping_city),
                            'state_code'      => wc_clean($order->shipping_state),
                            'zip_code'   => wc_clean($order->shipping_postcode),
                            'country_code'    => wc_clean($order->shipping_country),
                        )
                    ),
                );

                $authorization_token = 'Bearer ' . wc_clean($this->api_key);
                
                
                $headers = array(
                    'Authorization' => $authorization_token,
                    'Content-Type' => 'application/json',
                    
                );
                $this->endpoint = $this->testmode ? 'https://sandbox-api.wafi.cash/v1/checkout-ui' : 'https://api.wafi.cash/v1/checkout-ui';  
                $response = wp_safe_remote_post(
                    $this->endpoint,
                    array(
                        'method'  => 'POST',
                        'body'    =>  wp_json_encode($request_body),
                        'headers' => $headers,
                        'timeout' => 70,
                    )
                );

 
                $response_body = wp_remote_retrieve_body( $response );
                $response_data = json_decode( $response_body, true );
                if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) >= 200 && wp_remote_retrieve_response_code( $response ) < 300 ) {

                    if ( $response_data !== null && isset( $response_data['data']['reference'], $response_data['data']['id'] ) ) {
                        $order->add_meta_data( '_wafi_ref', wc_clean( $response_data['data']['reference'] ) );
                        $order->add_meta_data( '_transaction_id', wc_clean( $response_data['data']['id'] ) );
                        $order->save();
            
                        $authorization_url = ( $response_data['data']['authorization_url'] );
                        
                        return array(
                            'result'   => 'success',
                            'redirect' =>    $authorization_url ,
                        );

                        
                    } 
            
                    else {
                        wc_add_notice(esc_html__('Unexpected response from the endpoint.', 'wafi-payment-for-woocommerce'), 'error');
                      
                        return;
                    }  
                    
                    
                }
                elseif ($response_data && isset($response_data['message'])) {

                    wc_add_notice( sprintf( esc_html__( 'Checkout error: %s', 'wafi-payment-for-woocommerce' ), $response_data['message'] ), 'error' );
                    return;
                } else {
                    $this->log( 'Checkout Failed: ' . $response->get_error_message(), 'error' );
                    wc_add_notice( sprintf( esc_html__( 'Checkout error: %s', 'wafi-payment-for-woocommerce' ), $response->get_error_message() ), 'error' );
                    return;
                }
                
            }
            /**
             * Verify a transaction
             * @return void
             */

            public function wafi_verify_transaction() {

                $wafi_txn_ref = isset( $_REQUEST['reference'] ) ? sanitize_text_field( $_REQUEST['reference'] )  : false;
                @ob_clean();
        
                if ( $wafi_txn_ref ) {
               
                    
                    $order_details = explode( '_', sanitize_text_field( $wafi_txn_ref ));
                    $order_id      = (int) $order_details[0];
                    $order         = wc_get_order( $order_id );
                    $transaction_id = $order->get_meta( '_transaction_id', true );
                    
        
                    $trn_response = $this->get_wafi_transaction( $transaction_id );
                    
                       
                    if ( false !== $trn_response ) {
                        
                        
                        $id = $trn_response->id;
                        $ref = $trn_response->reference;
                        if ($ref !== $wafi_txn_ref){
                            exit;
                        }
   
                        if  ('approved' == $trn_response->status) {
        
                            if ( in_array( $order->get_status(), array( 'processing', 'completed', 'on-hold' ) ) ) {
        
                                wp_redirect( $this->get_return_url( $order ) );
        
                                exit;
        
                            }
                           
                            $order->payment_complete( $id );
                            $order->add_order_note( sprintf( __( 'Payment was successful (Transaction ID: %1s), Transaction reference: %2s', 'wafi-payment-for-woocommerce' ), $id, $ref) );
                            $order->save();
                            WC()->cart->empty_cart();
        
                        }
                        
                        elseif ('processing' == $trn_response->status){
                            WC()->cart->empty_cart();

                        }

                        else {
        
                            $order->update_status( 'failed', sprintf(__( 'Unfortunately, payment was declined by Wafi. (Transaction ID: %1s), Transaction reference: %2s', 'wafi-payment-for-woocommerce' ), $id, $ref)  );
        
                        }
                        wp_redirect( $this->get_return_url( $order ) );
                        exit;
                    }
        
                    wp_redirect( $this->get_return_url( $order ) );
        
                    exit;
                }
        
                wp_redirect( wc_get_page_permalink( 'cart' ) );
        
                exit;
        
            }
            /**
             * Get transaction
             * @param string $transaction_id Transaction ID
             * @return bool|array
             */
                    
            private function get_wafi_transaction( $transaction_id ) {
                if ( empty( $transaction_id ) ) {
                    return false;
                }
             
                $endpoint = $this->testmode ? 'https://sandbox-api.wafi.cash/v1/checkout/transaction/' : 'https://api.wafi.cash/v1/checkout/transaction/';
                $endpoint = esc_url_raw( $endpoint );
                $url =  $endpoint . $transaction_id;
                $authorization_token = 'Bearer ' . wc_clean($this->api_key);
                $headers = array(
                    'Authorization' => $authorization_token,
                );
        
                $args = array(
                    'headers' => $headers,
                    'timeout' => 60,
                );
        
                $request = wp_remote_get( esc_url_raw( $url ), $args );
        
                if ( ! is_wp_error( $request ) && 200 === wp_remote_retrieve_response_code( $request ) ) {

                    return json_decode( wp_remote_retrieve_body( $request ) );
                }
                return false;
            }

            /**
             *Process Webhook
             *@return void
             */
            public function wafi_process_webhooks() {
 
            
                if ( ! isset( $_SERVER['HTTP_X_FORWARDED_FOR'], $_SERVER['REQUEST_METHOD'] ) || 'POST' !== sanitize_text_field(strtoupper( $_SERVER['REQUEST_METHOD'] )) ) {
                    exit;
                }
                


                $forwarded_for_header = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']) : '';
                $ip_addresses = explode(', ', sanitize_text_field($forwarded_for_header));
                $ip_address = self::WAFI_IP_ADDRESS;
                $contains_ip = false;
                foreach ($ip_addresses as $ip) {
                    if (trim($ip) === $ip_address) {
                        $contains_ip = true;
                        break;
                    }
                }
                if (!$contains_ip) {
                    $this->log( 'Cannot process webhook' ,'error' );
                    exit;
                } 
                $sanitized_json = wp_unslash(file_get_contents("php://input"));
                $input = json_decode($sanitized_json);
                if ($input === null && json_last_error() !== JSON_ERROR_NONE) {
                    $this->log('Invalid JSON data received from the webhook', 'error');
                    exit;
                }
                if (!isset($input->event) || !isset($input->data->reference)) {
                    $this->log('Invalid or missing properties in the webhook payload', 'error');
                    exit;
                }
                $event = $input->event;
                $input_reference = $input->data->reference;
                

                if ( 'checkout.approved' === strtolower( $event )  ) {
                  
            
                    $order_details = explode( '_',sanitize_text_field( $input_reference) );
            
                    $order_id = (int) $order_details[0];

                    $order = wc_get_order( $order_id );


                    if ( ! $order ) {
                        $this->log( 'Order not found ','error' );
                        return;
                    }
                    $reference = wc_clean($order->get_meta( '_wafi_ref', true ));
                    $transaction_id = wc_clean($order->get_meta( '_transaction_id', true ));
                    sleep(10);
                    $response = $this->get_wafi_transaction( $transaction_id );
                    if ( $response === false ) {
                        $this->log( 'Transaction not found ','error' );
                        return;
                    }
        

                    if ( $response->reference !== $reference ) {
                        $this->log( 'Mismatch in Wafi transaction reference', 'error' );
                        exit;
                    }
                    if ( in_array( strtolower( $order->get_status() ), array( 'processing', 'completed', 'on-hold' ), true ) ) {
                        $this->log( 'Status already updated ' );
                        exit;
                    }
                    $order->payment_complete( $transaction_id );
                    $order->add_order_note( sprintf( __( 'Payment was successful (Transaction ID: %1s), Transaction reference: %2s', 'wafi-payment-for-woocommerce' ), $transaction_id, $reference) );
                    $order->save();
                    WC()->cart->empty_cart();
                    exit;
            
                }
                else{
                    return;
                }
            }
                
        
            /**
             * Process Refund
             * @param int $order_id Woocommerce Order ID.
             * @param float|null $amount Amount to refund.
             * @param string $reason Refund Reason
             * @return bool|WP_Error
             */
            public function process_refund( $order_id, $amount = null, $reason = '' ) {
             
                $order = wc_get_order( $order_id );
                if ( ! $order ) {
                    $this->log( 'Order not found','error' );
                    return false;
                }
                if ( !( $this->api_key ) ) {
                    $this->log( 'Please provide your API key','error' );
                    return false;
                }

                $reference = wc_clean($order->get_meta( '_wafi_ref', true ));
                $transaction_id = wc_clean($order->get_meta( '_transaction_id', true ));
                $response = $this->get_wafi_transaction( $transaction_id );
                if ( false === $response ) {
                    return false;
                }
                if ( $reference  !== $response->reference) {
                    return false;
                }
           

                if ( 'completed' !== $response->status) {
                    $this->log( 'Can only perform a refund for a completed transaction','error' );
                    return false;
                }
      
                $request_body = array(
                    'amount'  => intval($amount * 100) ,
                );
             
                $headers = array(
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Content-Type' => 'application/json',
                );

                


                $base_url = $this->testmode ? 'https://sandbox-api.wafi.cash/v1/checkout/' : 'https://api.wafi.cash/v1/checkout/';
                $refund_url =  $base_url . $transaction_id . '/refund';
              
                $refund_request = wp_safe_remote_post(
                    $refund_url,
                    array(
                        'method'  => 'POST',
                        'body'    =>  wp_json_encode($request_body),
                        'headers' => $headers,
                        'timeout' => 70,
                    )
                );

                if ( ! is_wp_error( $refund_request ) && 200 === wp_remote_retrieve_response_code( $refund_request ) ) {

                    $refund_response = json_decode( wp_remote_retrieve_body( $refund_request ) );
                    if ( $refund_response->status ) {
                     
                        $amount         = wc_price( $amount, array( 'currency' => $order_currency ) );
                        $refund_id      = $refund_response->id;
                        $refund_message = sprintf( __( 'Refunded %1$s. Refund ID: %2$s. Reason: %3$s. You can monitor the status of this refund from your Wafi dashboard.', 'wafi-payment-for-woocommerce' ), $amount, $refund_id, $reason );
                        $order->add_order_note( $refund_message );

                        return true;
                    }

                } else {
                 
                    $refund_response = json_decode( wp_remote_retrieve_body( $refund_request ) );
             
                    if ( isset( $refund_response->error ) ) {
                        return new WP_Error( 'error', $refund_response->error );
                    } else {
                        return new WP_Error( 'error', __( 'Cannot process refund at the moment. Try again later.', 'wafi-payment-for-woocommerce' ) );
                    }
                    return false;
                }

            

            }

        }
    }
}



function wafi_add_to_woo_payment_gateway($gateways){
    $gateways[] = 'Wafi_Payment_Gateway';
    return $gateways;
};


