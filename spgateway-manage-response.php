<?php
/**
 * Plugin URI: http://www.spgateway.com/
 * Description: spgateway 收款模組
 * Version: 1.0.0
 * Author URI: http://www.spgateway.com/
 * Author: 智付通 spgateway
 * Plugin Name: Spgateway Manage Response
 * @class 		spgateway
 * @extends		WC_Payment_Gateway
 * @version
 * @author 	Pya2go Libby
 * @author 	Pya2go Chael
 * @author  Spgateway Geoff
 */

add_shortcode('spgateway_payment_response', 'spgateway_payment_response_func');

require_once(ABSPATH . "wp-includes/post.php");
function spgateway_payment_response_func()
{
    $paymentGateway = 'spgateway credit card payment way';
    $status         = 'success';
    $orderId        = 129;
    $product_id     = 66;
    $message        = 'Authenticated';

    //    print "<pre>";
    //        print "post";
    //        print_r($_POST);
    //        print "session";
    //        PRINT_R($_SESSION);
    //        print "cookie";
    //        PRINT_R($_COOKIE);
    //    print "</pre>";

    //    $product_id = $_SESSION['spgateway_args']['Pid1'];
    //    $status     = strtolower($_POST['Status']);
    //    $orderId    = $_POST['MerchantOrderNo'];
    //    $message    = $_POST['Message'];

    /////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////// send right registration /////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    spgateway_mr_add_create_sendright_account($paymentGateway);

    /////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////// clean item and set product to processing /////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    spgateway_mr_set_order_processing_and_empty_cart($status, $orderId, $message);

    /////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////// redirect to thank you page /////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    spgateway_mr_redirect_to_thankyou_page($product_id);

}

function spgateway_mr_set_order_processing_and_empty_cart($status, $orderId, $message)
{
    if ($status == 'success') {
        $order = new WC_Order($orderId);

        // set the cart to empty
        WC()->cart->empty_cart(true);

        // set status to processing, meaning its already paid via credit card
        // update order status
        $order->update_status('processing');

        print "<br><br><div style='color:green; padding:20px; border:1px solid green'>";
        print $message;
        print "</div>";
        print "<br><br> <div><b>Redirect to thank you page in 3 seconds..</b></div>";

    } else {

        $link = get_site_url() . "/checkout/";

        print "Ops, something wrong while processing you order, ";
        print " visit " . "<a href='$link'>checkout</a>";

        print "<br><br><div style='color:red; padding:20px; border:1px solid red'>";
        print $message;
        print "</div>";

        // redirect back to checkout
        exit;
    }
}

function spgateway_mr_redirect_to_thankyou_page($product_id) {

    $thank_you_page_id = get_post_meta($product_id, 'thankyou_page', true);
    $post_7 = get_post( $thank_you_page_id );
    $url = $post_7->guid;


    ?>
        <script>

//            setTimeout(
//                function(){
                    document.location ='<?php print $url; ?>';
//                },3000);


        </script>

    <?php
}

function spgateway_mr_add_create_sendright_account($paymentGateway)
{
    if($paymentGateway == 'spgateway credit card payment way') {
        // check if sendright and add registration to sendright
        // say please whait while registering your account to
    }

}




