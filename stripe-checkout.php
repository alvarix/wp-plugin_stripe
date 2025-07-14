<?php
/**
 * Plugin Name: Alvar's Stripe Checkout
 * Description: REST API endpoint to create a Stripe Checkout session
 * Version: 1.0
 * Author: Alvar Sirlin
 * Author URI: https://alvarsirlin.dev
 */
// Load Stripe PHP SDK
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
// Use secret key from wp-config.php
\Stripe\Stripe::setApiKey(defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : '');
add_action('rest_api_init', function () {
  register_rest_route('stripe-checkout/v1', '/create-session', [
    'methods' => 'POST',
    'callback' => 'stripe_checkout_create_checkout_session',
    'permission_callback' => '__return_true',
  ]);
});
/**
 * Creates a Stripe Checkout session.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function stripe_checkout_create_checkout_session($request) {
  $params = $request->get_json_params();
  $shippingRateUS = sanitize_text_field($params['shippingRateUS']);
  $shippingRateINTL = sanitize_text_field($params['shippingRateINTL']);
  
  try {
    $session = \Stripe\Checkout\Session::create([
      'mode' => 'payment',
      'line_items' => [[
        'price' => 'price_1RklJPKsvaxLGOVJTE53e3YA', 
        // dev
        // 'price' => 'price_1Rf0Iz4IwG1sBeJEmSf3Lbt0', 
        'quantity' => 1,
      ]],
      'shipping_address_collection' => [
        'allowed_countries' => ['US', 'CA', 'AU', 'GB', 'IE', 'FR', 'DE', 'NL', 'NZ'],
      ],
      'shipping_options' => [
        ['shipping_rate' => $shippingRateUS],
        ['shipping_rate' => $shippingRateINTL],
      ],
      // Enable automatic tax calculation
      'automatic_tax' => [
        'enabled' => true,
      ],
      'success_url' => home_url('/success'),
      'cancel_url' => home_url('/cancel'),
    ]);
    return new WP_REST_Response(['url' => $session->url], 200);
  } catch (Exception $e) {
    error_log('Stripe error: ' . $e->getMessage());
    return new WP_REST_Response(['error' => 'Stripe Checkout error.'], 500);
  }
}