<?php
/**
 * Template Name: Spgateway Manage Response
 */

require_once( ABSPATH . "wp-content/plugins/cw-pay2go-ei/includes/class-cw-pay2goe-ei-spgateway.php" );
require_once( ABSPATH . "wp-content/plugins/cw-pay2go-ei/includes/helper.php" );

spgateway_payment_response_func_theme();

function spgateway_payment_response_func_theme()
{
    $paymentGateway = 'spgateway credit card payment way';
    $status         = 'success';
    $orderId        = 199;
    $product_id     = 197;
    $message        = 'Authenticated';

    //     print "<pre>";
    //         print "post";
    //         print_r($_POST);
    //         PRINT_R($_SESSION);
    //         print "cookie";
    //         PRINT_R($_COOKIE);
    //     print "</pre>";
    //    print "session";



    $product_id = $_SESSION['spgateway_args']['Pid1'];
    $status     = strtolower($_POST['Status']);
    $orderId    = $_POST['MerchantOrderNo'];
    $message    = $_POST['Message'];


    /////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////// save session and post to wp_postmeta /////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////


    $key_1_value = get_post_meta( $orderId, '_order_spgateway_response_session', true );
    if (  empty( $key_1_value ) ) {
        add_post_meta( $orderId, '_order_spgateway_response_session', serialize($_SESSION) );
    } else {
        update_post_meta($orderId, '_order_spgateway_response_session', serialize($_SESSION) );
    }

    $key_1_value = get_post_meta( $orderId, '_order_spgateway_response_post', true );
    if (  empty( $key_1_value ) ) {
        add_post_meta( $orderId, '_order_spgateway_response_post', serialize($_POST) );
    }else {
        update_post_meta($orderId, '_order_spgateway_response_session', serialize($_SESSION) );
    }


    /////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////// send right registration /////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    payshortcut_create_member_and_order();





    //    exit;

    /////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////// clean item and set product to processing /////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    spgateway_mr_set_order_processing_and_empty_cart_theme($status, $orderId, $message);




    /**
     * Send invoice to customer when settings is when order is processing
     */
    helper_spgateway_pay2go_invoice_trigger_invoice($orderId, $_SESSION, $_POST);



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
//     print " product id " . $product_id  . ' <br>';
    $thank_you_page_id = get_post_meta($product_id, 'thankyou_page', true);
//     print " page id " . $thank_you_page_id . ' <br>';
    $url = get_permalink( $thank_you_page_id );
    // $post_7 = get_post( $thank_you_page_id );
    // $url = $post_7->guid;
//     print "redirect to url $url";
//     exit;
    ?>
    <script>
        //            setTimeout(
        //                function(){
        //        alert('<?php //print $url; ?>//');
        document.location ='<?php print $url; ?>';
        //                },3000);
    </script>
    <?php
}

function payshortcut_create_member_and_order()
{

    $orderId    = $_POST['MerchantOrderNo'];
    $session    = $_SESSION;
    $post       = $_POST;
    $count      = $session['spgateway_args']['Count'];

    $order      = new WC_Order($orderId);

    $postMember = [
        'first_name' => $order->billing_first_name,
        'last_name' => $order->billing_last_name,
        'email' =>   $order->billing_email, //'mrjesuserwinsuarez@gmail.com',
        'telephone' => $order->billing_phone, // '+639069262984',
        'country' =>  $order->billing_country, // 'Philippines',
        'post_code' => $order->billing_postcode,  //'9200',
        'address' =>  $order->billing_address_1, // 'Mimbalot Buru un, Iligan City',
        'look_up' =>  $order->billing_company, // 'Nothing to look up',
        'uniform_number' =>  $order->billing_uniform_numbers, // '1234567890',
        'status' => 'subscribed',
    ];

    $postOrder = [
        'status' => $post['Message'] . ' - ' . $post['Status'], // 'success',
        'merchant_id' => $post['MerchantID'], //'1234567',
        'title' => helper_spgateway_separate_order_results($count, 'Title', $session['spgateway_args']) ,
        'description' =>  '',
        'version' =>  '1.1',
        'response_type' => $post['RespondType'], //'String',
        'check_value' => $session['spgateway_args']['CheckValue'], //'1234456789',
        'time_stamp' => $session['spgateway_args']['TimeStampdate'], //("Y-m-d h:i:s"),
        'merchant_order_no' =>$post['MerchantOrderNo'], // '123',
        'amt' => $post['Amt'], //'100',
        'hash_key' => '', //$post['Amt'],  //'1234dasda',
        'hash_iv' =>  '', //'ASD123',
        'trade_no' => $post['TradeNo'],// '12321',
        'token_value' => $post['TokenValue'], //'2asdasd',
        'token_life' => $post['TokenLife'], // '1233232',
        'content_post' => serialize($post),
        'content_session' => serialize($session),
    ];

    $payShortCut = new PayShortCut();

    $response = $payShortCut->createOrUpdateMemberAndCreateOrder(
        $postMember,
        $postOrder
    );

    //    print "<pre>";
    //    print "<br>response information <br>";
    //    print_r($postOrder);
    //    print_r($response);
    return $response;
}
