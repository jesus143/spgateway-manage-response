<?php
/**
 * Template Name: Spgateway Manage Notify Url Request
 */

$order_id = $_GET['order_id'];

print " order id " . $order_id;

add_option("test", 'this is a test request order id ' . $order_id);






