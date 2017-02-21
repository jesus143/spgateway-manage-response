<?php
/**
 * Template Name: Spgateway Manage Notify Url Request
 */
//
require_once( ABSPATH . "wp-content/plugins/cw-pay2go-ei/includes/class-cw-pay2goe-ei-spgateway.php" );
require_once( ABSPATH . "wp-content/plugins/cw-pay2go-ei/includes/helper.php" );
require_once( ABSPATH . "wp-includes/post.php" );

// get order id from spgateway request
$orderId = $_GET['order_id'];

// set order completed
helper_spgateway_pay2go_invoice_set_order_completed($orderId);

//exit;
// get the session and post status saved in wp_postmeta during manage response spgateway processed order
// and unserialized the result so that it can be called as the orginal text format like array.

$session = unserialize(get_post_meta( $orderId, '_order_spgateway_response_session' , true));
$post    = unserialize(get_post_meta( $orderId, '_order_spgateway_response_post', true));

// print "<pre>";
// $session = unserialize($session);
// $post    = unserialize($post);
//  $session = unserialized(session);
// print "session<br>";
// print_r($session);
// print "post<br>";
// print_r($post);
// exit;
//
// send check or send invoice to customer if found match the condition
// or the settings of the invoice pay2go is send invoice to customer when order completed
helper_spgateway_pay2go_invoice_trigger_invoice($orderId, $session, $post);

