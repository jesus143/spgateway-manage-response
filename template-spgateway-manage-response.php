<?php
/**
 * Template Name: Spgateway Manage Response
 */

spgateway_payment_response_func_theme();

function spgateway_payment_response_func_theme()
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

    $product_id = $_SESSION['spgateway_args']['Pid1'];
    $status     = strtolower($_POST['Status']);
    $orderId    = $_POST['MerchantOrderNo'];
    $message    = $_POST['Message'];

    /////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////// send right registration /////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    spgateway_mr_add_create_sendright_account_theme($paymentGateway);

    /////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////// clean item and set product to processing /////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    spgateway_mr_set_order_processing_and_empty_cart_theme($status, $orderId, $message);

    /////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////// redirect to thank you page /////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    spgateway_mr_redirect_to_thankyou_page_theme($product_id);

}

function spgateway_mr_set_order_processing_and_empty_cart_theme($status, $orderId, $message)
{
    if ($status == 'success') {
        $order = new WC_Order($orderId);
        // set the cart to empty
        WC()->cart->empty_cart(true);
        // set status to processing, meaning its already paid via credit card
        // update order status
        $order->update_status('processing');
        // print "<br><br><div style='color:green; padding:20px; border:1px solid green'>";
        // print $message;
        // print "</div>";
    } else {

        $link = get_site_url() . "/checkout/";
        print "Ops, something wrong while processing you order, ";
        print " visit " . "<a href='$link'>checkout</a>";
        // print "<br><br><div style='color:red; padding:20px; border:1px solid red'>";
        // print $message;
        // print "</div>";
        // redirect back to checkout
        exit;
    }
}

function spgateway_mr_redirect_to_thankyou_page_theme($product_id) {

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

function spgateway_mr_add_create_sendright_account_theme($paymentGateway)
{
    if($paymentGateway == 'spgateway credit card payment way') {
        // check if sendright and add registration to sendright
        // say please whait while registering your account to
    }

}
