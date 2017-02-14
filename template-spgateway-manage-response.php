<?php
/**
 * Template Name: Spgateway Manage Response
 */

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
    //         print "session";
    //         PRINT_R($_SESSION);
    //         print "cookie";
    //         PRINT_R($_COOKIE);
    //     print "</pre>";

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

    /**
     * Invoice pay2go
     */

    spgateway_pay2go_invoice_trigger_invoice($orderId);

    /////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////// redirect to thank you page /////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////
    spgateway_mr_redirect_to_thankyou_page_theme($product_id);
}


function spgateway_pay2go_invoice_trigger_invoice($orderId) {

    require ABSPATH . '/wp-content/plugins/cw-pay2go-ei/includes/class-cw-pay2goe-ei-spgateway.php';
    $date = new DateTime();
    $data = new CWP2GEI_SPGATEWAY();

    $session  = $_SESSION['spgateway_args'];
    $post     = $_POST;

    //    print "<pre>";
    //    print " session <br>";
    //    print_r($session);
    //    print "<br> post <br>";
    //    print_r($post);
    //    print "</pre>";

    $order = new WC_Order($orderId);


    $buyerUbn = '';

    if($data->taxtype == 1) {
        $TaxRatePercent = 0.05;
        $TaxType = 1;
    } else if ($data->taxtype == 1.1) {
        $TaxRatePercent = 0;
        $TaxType = 2;
    } else {
        $TaxRatePercent = 0;
        $TaxType = 2;
    }

    if(!empty($order->billing_company) and !empty($order->billing_uniform_numbers)) {
        $Category = 'B2B';
        $buyerName = $order->billing_company;
        $buyerUbn  = $order->billing_uniform_numbers;

    } else {
        $buyerName = $order->billing_first_name;
        $Category  = 'B2C';
    }


    $billingAddressArray = $order->get_address();
    $count = $session['Count'];
    $TaxRate = ($data->taxtype == 1) ? 5 : 0;
    $Amt   =  $order->get_total() - ($order->get_total() * $TaxRatePercent); //490;
    $TaxAmt = ($order->get_total() * $TaxRatePercent);
    $TotalAmt =  $order->get_total(); //500;
    $CarrierType = '';
    $CarrierNum = rawurlencode("");
    $LoveCode = '';
    $PrintFlag = "Y";
    $ItemName =   spgateway_separate_order_results($count, 'Title', $session);       //"商品一|商品二";
    $ItemCount =  spgateway_separate_order_results($count, 'Item Count', $session);  // "1|2";
    $ItemUnit =   spgateway_separate_order_results($count, 'Item Unit', $session);  // "個|個";
    $ItemPrice =  spgateway_separate_order_results($count, 'Price', $session); //"300|100";
    $ItemAmt =    spgateway_separate_order_results($count, 'Item Amount', $session); //"300|200";
    $Comment = "";
    $Status = "1";
    $CreateStatusTime = '';
    $NotifyEmail =   ($data->enable === true) ? 1 : 0;
    $BuyerAddress = $billingAddressArray['address_1']; //. ', ' . $billingAddressArray['address_2'] . ', ' .  $billingAddressArray['city'] . ', ' .  $billingAddressArray['city'] . ', ' .  $billingAddressArray['postcode'] . ', ' . $billingAddressArray['country'];

    $testData = [
        "RespondType" => "JSON",
        "Version" => "1.4",
        "TimeStamp" => time(), //請以  time()  格式
        "TransNum" => $post['TradeNo'],
        "MerchantOrderNo" => $post['MerchantOrderNo'],  //"201409170000009",
        "BuyerName" =>$buyerName, ///$order->get_formatted_billing_full_name(),
        "BuyerUBN" => $buyerUbn,
        "BuyerAddress" => $BuyerAddress,
        "BuyerEmail" => $order->billing_email,
        "BuyerPhone" => $order->billing_phone,
        "Category" => $Category,
        "TaxType" => $TaxType,
        "TaxRate" => $TaxRate,
        "Amt" => $Amt,
        "TaxAmt" => $TaxAmt,
        "TotalAmt" => $TotalAmt,
        "CarrierType" => $CarrierType,
        "CarrierNum" => $CarrierNum,
        "LoveCode" => $LoveCode,
        "PrintFlag" => $PrintFlag,
        "ItemName" => $ItemName, //多項商品時，以「|」分開
        "ItemCount" => $ItemCount, //多項商品時，以「|」分開
        "ItemUnit" => $ItemUnit, //多項商品時，以「|」分開
        "ItemPrice" => $ItemPrice, //多項商品時，以「|」分開
        "ItemAmt" => $ItemAmt, //多項商品時，以「|」分開
        "Comment" => $Comment,
        "Status" => $Status, //1=立即開立，0=待開立，3=延遲開立
        "CreateStatusTime" => $CreateStatusTime,
        "NotifyEmail" => $NotifyEmail, //1=通知，0=不通知
    ];

    $data->setParameter($testData);
        // print "<pre>";
        // print_r($testData);
        // print_r($data->post_data_array);
        // print "</pre>";
        $data->postInvoice();
        // print "exit???";
        // exit;
}



function spgateway_separate_order_results($count, $fieldName, $post) {

    $str = '';

    // print " count  $count field name  $fieldName ";

    for($i=1; $i<=$count; $i++) {

        if($fieldName == 'Item Count') {
            $str .=  $post['Qty' . $i];
        } else if($fieldName == 'Item Unit') {
            $str .= '個';
        } else if ($fieldName == 'Item Amount') {
            $quantity     = $post['Qty' . $i];
            $price        = $post['Price' . $i];
            $subTotal     = $quantity * $price;
            $str         .= $subTotal;
        } else {
            $str .= $post[$fieldName . $i];
        }

        if ($i != $count) {
            $str .= '|';
        }
    }
    // print " str compose " . $str;
    return $str;
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
    // print " product id " . $product_id  . ' <br>';
    $thank_you_page_id = get_post_meta($product_id, 'thankyou_page', true);
    // print " page id " . $thank_you_page_id . ' <br>';
    $url = get_permalink( $thank_you_page_id );
    // $post_7 = get_post( $thank_you_page_id );
    // $url = $post_7->guid;
    // print "redirect to url $url";
    // exit;
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
