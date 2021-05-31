<?php

//define('PPL_LANG', 'EN');

if(config('enable-paypal-sandbox')){
    define('PPL_MODE', 'sandbox');
    define('PPL_API_USER', config('donation-paypal-api-user'));
    define('PPL_API_PASSWORD', config('donation-paypal-api-password'));
    define('PPL_API_SIGNATURE', config('donation-paypal-api-signature'));
}else{
    define('PPL_MODE', 'production');
    define('PPL_API_USER', config('donation-paypal-api-user'));
    define('PPL_API_PASSWORD', config('donation-paypal-api-password'));
    define('PPL_API_SIGNATURE', config('donation-paypal-api-signature'));
}

$logo = config('site-logo');
$logo_url = !$logo ? img('images/logo.png') : url_img($logo);
define('PPL_LOGO_IMG', $logo_url);

define('PPL_RETURN_URL', 'http://localhost/crea8social/new-crea8socialpro/plugins/donation/lib/paypal/process.php');
define('PPL_CANCEL_URL', 'http://localhost/crea8social/new-crea8socialpro/plugins/donation/lib/paypal/cancel_url.php');

define('PPL_CURRENCY_CODE', config("default-currency", "USD"));

class DonationPayPal
{

    function GetItemTotalPrice($item)
    {

        //(Item Price x Quantity = Total) Get total amount of product;
        return $item['ItemPrice'] * $item['ItemQty'];
    }

    function GetProductsTotalAmount($products)
    {

        $ProductsTotalAmount = 0;

        foreach ($products as $p => $item) {

            $ProductsTotalAmount = $ProductsTotalAmount + $this->GetItemTotalPrice($item);
        }

        return $ProductsTotalAmount;
    }

    function GetGrandTotal($products, $charges)
    {

        //Grand total including all tax, insurance, shipping cost and discount

        $GrandTotal = $this->GetProductsTotalAmount($products);

        foreach ($charges as $charge) {

            $GrandTotal = $GrandTotal + $charge;
        }

        return $GrandTotal;
    }

    function SetExpressCheckout($amount, $email, $donation, $rid)
    {

        //Parameters for SetExpressCheckout, which will be sent to PayPal
        $did = $donation['id'];
        $amount1 = $amount;
        $amount2 = 0;
        if ($commission = dCommissionEnabled()) {
            $amount2 = ($commission / 100) * $amount;
            $amount1 = $amount - $amount2;  //this is the new amount donor will collect;
        }
        $padata = '&METHOD=SetExpressCheckout';

        $padata .= '&RETURNURL=' . urlencode(url_to_pager('donate-now') . '?action=paypal&type=requestn&rid=' . $rid . '&did=' . $did);
        $padata .= '&CANCELURL=' . urlencode(url_to_pager('donate-now') . '?action=paypal&type=cancel&rid=' . $rid . '&did=' . $did);

        $padata .= '&PAYMENTREQUEST_0_PAYMENTACTION=' . urlencode("SALE");
        $padata .= '&PAYMENTREQUEST_0_PAYMENTREQUESTID=' . urlencode(uniqid() . '1');
        $padata .= '&PAYMENTREQUEST_0_SELLERPAYPALACCOUNTID=' . urlencode($email);
        //$padata .= '&PAYMENTREQUEST_0_SELLERPAYPALACCOUNTID=' . urlencode('tests@gmail.com');
        $padata .=	'&L_PAYMENTREQUEST_0_NAME0='.urlencode($donation['title']);
        $padata .=	'&L_PAYMENTREQUEST_0_NUMBER0='.urlencode($donation['id']);
        //$padata .=	'&L_PAYMENTREQUEST_0_DESC0='.urlencode(strip_tags($donation['description']));
        $padata .=	'&L_PAYMENTREQUEST_0_AMT0='.urlencode($amount1);
        $padata .=	'&L_PAYMENTREQUEST_0_QTY0='. urlencode(1);
        $padata .=	'&L_PAYMENTREQUEST_0_TAXAMT0='. urlencode(0);
        $padata .=	'&PAYMENTREQUEST_0_ITEMAMT='.urlencode($amount1);
        $padata .=	'&PAYMENTREQUEST_0_TAXAMT='.urlencode(0);
        /*$padata .=	'&PAYMENTREQUEST_0_SHIPPINGAMT='.urlencode(0);
        $padata .=	'&PAYMENTREQUEST_0_HANDLINGAMT='.urlencode(0);
        $padata .=	'&PAYMENTREQUEST_0_SHIPDISCAMT='.urlencode(0);
        $padata .=	'&PAYMENTREQUEST_0_INSURANCEAMT='.urlencode(0);*/
        //$padata .=	'&PAYMENTREQUEST_0_DESC='.urlencode(strip_tags($donation['description']));
        $padata .=	'&PAYMENTREQUEST_0_AMT='.urlencode($amount1);
        $padata .=	'&PAYMENTREQUEST_0_CURRENCYCODE='.urlencode(PPL_CURRENCY_CODE);
        //$padata .= '&L_PAYMENTREQUEST_0_QTY=' . urlencode(1);

        if ($amount2) {
            $site_owner_email = config('paypal-corporate-email');
            //$site_owner_email = PPL_API_USER;
            $padata .= '&PAYMENTREQUEST_1_PAYMENTACTION=' . urlencode("SALE");
            $padata .= '&PAYMENTREQUEST_1_PAYMENTREQUESTID=' . urlencode(uniqid() . '2');
            $padata .= '&PAYMENTREQUEST_1_SELLERPAYPALACCOUNTID=' . urlencode($site_owner_email);
            $padata .=	'&L_PAYMENTREQUEST_1_NAME0='.urlencode(lang("donation::transaction-fee"));
            $padata .=	'&L_PAYMENTREQUEST_1_NUMBER0='.urlencode($donation['id']);
            //$padata .=	'&L_PAYMENTREQUEST_1_DESC0='.urlencode(lang("donation::transaction-fee"));
            $padata .=	'&L_PAYMENTREQUEST_1_AMT0='.urlencode($amount2);
            $padata .=	'&L_PAYMENTREQUEST_1_QTY0='. urlencode(1);
            $padata .=	'&L_PAYMENTREQUEST_1_TAXAMT0='. urlencode(0);
            $padata .=	'&PAYMENTREQUEST_1_ITEMAMT='.urlencode($amount2);
            $padata .=	'&PAYMENTREQUEST_1_TAXAMT='.urlencode(0);
            /*$padata .=	'&PAYMENTREQUEST_1_SHIPPINGAMT='.urlencode(0);
            $padata .=	'&PAYMENTREQUEST_1_HANDLINGAMT='.urlencode(0);
            $padata .=	'&PAYMENTREQUEST_1_SHIPDISCAMT='.urlencode(0);
            $padata .=	'&PAYMENTREQUEST_1_INSURANCEAMT='.urlencode(0);*/
            $padata .=	'&PAYMENTREQUEST_1_AMT='.urlencode($amount2);
            $padata .=	'&PAYMENTREQUEST_1_CURRENCYCODE='.urlencode(PPL_CURRENCY_CODE);
        }

        $padata .= '&NOSHIPPING=1'; //set 1 to hide buyer's shipping address, in-case products that does not require shipping

        //$padata .=	'&LOCALECODE='.PPL_LANG; //PayPal pages to match the language on your website;
        $padata .= '&LOGOIMG=' . PPL_LOGO_IMG; //site logo
        $padata .= '&CARTBORDERCOLOR=FFFFFF'; //border color of cart
        $padata .= '&ALLOWNOTE=1';

        ############# set session variable we need later for "DoExpressCheckoutPayment" #######
        //$amount,$email,$donation,$rid
        $_SESSION['ppl_amt'] = $amount;
        $_SESSION['pppl_donation'] = $donation;
        $_SESSION['ppl_email'] = $email;
        $_SESSION['ppl_rid'] = $rid;
        //$_SESSION['ppl_charges'] 	=  $charges;

       /* $str = urldecode(html_entity_decode($padata));
        parse_str($str, $get_array);
        echo '<pre>', print_r($get_array),'</pre>';
        die();*/
        $httpParsedResponseAr = $this->PPHttpPost('SetExpressCheckout', $padata);
        //print_r($httpParsedResponseAr);die();
        //Respond according to message we receive from Paypal
        if ("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) {

            $paypalmode = (PPL_MODE == 'sandbox') ? '.sandbox' : '';

            //Redirect user to PayPal store with Token received.

            $paypalurl = 'https://www' . $paypalmode . '.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $httpParsedResponseAr["TOKEN"] . '';

            return redirect($paypalurl);
            //header('Location: ' . $paypalurl);
        } else {

            //Show error message

            echo '<div style="color:red"><b> Error : </b>' . urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]) . '</div>';

            echo '<pre>';

            print_r($httpParsedResponseAr);

            echo '</pre>';
        }
        die();
    }


    function DoExpressCheckoutPayment()
    {

        if (!empty($_SESSION['pppl_donation']) && !empty($_SESSION['ppl_amt'])) {

            //set item info here, otherwise we won't see product details later
            $amount = $_SESSION['ppl_amt'] ;
            $donation = $_SESSION['pppl_donation'];
            $email = $_SESSION['ppl_email'];
            $rid = $_SESSION['ppl_rid'];


            $did = $donation['id'];
            $amount1 = $amount;
            $amount2 = 0;
            if ($commission = dCommissionEnabled()) {
                $amount2 = ($commission / 100) * $amount;
                $amount1 = $amount - $amount2;  //this is the new amount donor will collect;
            }

            $padata = '&TOKEN=' . urlencode($_GET['token']);
            $padata .= '&PAYERID=' . urlencode($_GET['PayerID']);
            //$padata .= '&PAYMENTREQUEST_0_PAYMENTACTION=' . urlencode("SALE");


            $padata .= '&PAYMENTREQUEST_0_PAYMENTACTION=' . urlencode("SALE");
            $padata .= '&PAYMENTREQUEST_0_PAYMENTREQUESTID=' . urlencode(uniqid() . '1');
            $padata .= '&PAYMENTREQUEST_0_SELLERPAYPALACCOUNTID=' . urlencode($email);
            $padata .=	'&L_PAYMENTREQUEST_0_NAME0='.urlencode($donation['title']);
            $padata .=	'&L_PAYMENTREQUEST_0_NUMBER0='.urlencode($donation['id']);
            //$padata .=	'&L_PAYMENTREQUEST_0_DESC0='.urlencode(strip_tags($donation['description']));
            $padata .=	'&L_PAYMENTREQUEST_0_AMT0='.urlencode($amount1);
            $padata .=	'&L_PAYMENTREQUEST_0_QTY0='. urlencode(1);
            $padata .=	'&PAYMENTREQUEST_0_ITEMAMT='.urlencode($amount1);
            $padata .=	'&L_PAYMENTREQUEST_0_TAXAMT0='. urlencode(0);
            $padata .=	'&PAYMENTREQUEST_0_TAXAMT='.urlencode(0);
            /*$padata .=	'&PAYMENTREQUEST_0_SHIPPINGAMT='.urlencode(0);
            $padata .=	'&PAYMENTREQUEST_0_HANDLINGAMT='.urlencode(0);
            $padata .=	'&PAYMENTREQUEST_0_SHIPDISCAMT='.urlencode(0);
            $padata .=	'&PAYMENTREQUEST_0_INSURANCEAMT='.urlencode(0);*/
            $padata .=	'&PAYMENTREQUEST_0_AMT='.urlencode($amount1);
            $padata .=	'&PAYMENTREQUEST_0_CURRENCYCODE='.urlencode(PPL_CURRENCY_CODE);
        //$padata .= '&L_PAYMENTREQUEST_0_QTY=' . urlencode(1);

        if ($amount2) {
            $site_owner_email = config('paypal-corporate-email');
            //$site_owner_email = PPL_API_USER;
            $padata .= '&PAYMENTREQUEST_1_PAYMENTACTION=' . urlencode("SALE");
            $padata .= '&PAYMENTREQUEST_1_PAYMENTREQUESTID=' . urlencode(uniqid() . '2');
            $padata .= '&PAYMENTREQUEST_1_SELLERPAYPALACCOUNTID=' . urlencode($site_owner_email);
            $padata .=	'&L_PAYMENTREQUEST_1_NAME0='.urlencode(lang("donation::transaction-fee"));
            $padata .=	'&L_PAYMENTREQUEST_1_NUMBER0='.urlencode($donation['id']);
            //$padata .=	'&L_PAYMENTREQUEST_1_DESC0='.urlencode(lang("donation::transaction-fee"));
            $padata .=	'&L_PAYMENTREQUEST_1_AMT0='.urlencode($amount2);
            $padata .=	'&L_PAYMENTREQUEST_1_QTY0='. urlencode(1);
            $padata .=	'&L_PAYMENTREQUEST_1_TAXAMT0='. urlencode(0);
            $padata .=	'&PAYMENTREQUEST_1_ITEMAMT='.urlencode($amount2);
            $padata .=	'&PAYMENTREQUEST_1_TAXAMT='.urlencode(0);
            /*$padata .=	'&PAYMENTREQUEST_1_SHIPPINGAMT='.urlencode(0);
            $padata .=	'&PAYMENTREQUEST_1_HANDLINGAMT='.urlencode(0);
            $padata .=	'&PAYMENTREQUEST_1_SHIPDISCAMT='.urlencode(0);
            $padata .=	'&PAYMENTREQUEST_1_INSURANCEAMT='.urlencode(0);*/
            $padata .=	'&PAYMENTREQUEST_1_AMT='.urlencode($amount2);
            $padata .=	'&PAYMENTREQUEST_1_CURRENCYCODE='.urlencode(PPL_CURRENCY_CODE);
        }

            //We need to execute the "DoExpressCheckoutPayment" at this point to Receive payment from user.

            $httpParsedResponseAr = $this->PPHttpPost('DoExpressCheckoutPayment', $padata);

            //vdump($httpParsedResponseAr);

            //Check if everything went ok..
            if ("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) {

                //echo '<h2>Success</h2>';
                //echo 'Your Transaction ID : ' . urldecode($httpParsedResponseAr["PAYMENTINFO_0_TRANSACTIONID"]);

                /*
                //Sometimes Payment are kept pending even when transaction is complete.
                //hence we need to notify user about it and ask him manually approve the transiction
                */

                if ('Completed' == $httpParsedResponseAr["PAYMENTINFO_0_PAYMENTSTATUS"]) {
                    $amount = $_SESSION['ppl_amt'] ;
                    $donation = $_SESSION['pppl_donation'];
                    $email = $_SESSION['ppl_email'];
                    $rid = $_SESSION['ppl_rid'];

                    $don = Donation::getInstance();
                    //we have recived the money now
                    $raised_id = $rid;
                    $id = $donation['id'];
                    $don->raisedActive($raised_id);
                    $val = array();
                    $donation = $don->getSingle($id);
                    $donation = $donation[0];
                    $raised = $don->getRaisedArr($raised_id);
                    $val['full_name'] = $raised['full_name'];
                    if($raised['anonymous']){
                        $val['full_name'] = lang("donation::anonymous");
                    }
                    if($raised['to_feed']){
                        //this one from paypal show autopost to feed
                        //the owner want to show on feed, let us help him
                        $don->tofeed($raised_id);
                    }
                    $val['email'] = $raised['email_address'];
                    $val['amount'] = $raised['amount'];
                    $don->sendThankyouMail($val,$donation);

                   return redirect(url_to_pager('donate-now') . '?type=success&did='.$id);


                } elseif ('Pending' == $httpParsedResponseAr["PAYMENTINFO_0_PAYMENTSTATUS"]) {
                    return redirect(url_to_pager('donate-now') . '?type=pending&did='.$id);
                }

                //$this->GetTransactionDetails();
            } else {
                return redirect(url_to_pager('donate-now') . '?type=error');
                //return redirect(url_to_pager('donate-now') . '?type=cancel&did='.$id);
            }
        } else {

            // Request Transaction Details
            return redirect(url_to_pager('donate-now') . '?type=error');
        }
        //die("Do express checkout");die();
    }

    function GetTransactionDetails()
    {

        // we can retrive transection details using either GetTransactionDetails or GetExpressCheckoutDetails
        // GetTransactionDetails requires a Transaction ID, and GetExpressCheckoutDetails requires Token returned by SetExpressCheckOut

        $padata = '&TOKEN=' . urlencode($_GET['token']);

        $httpParsedResponseAr = $this->PPHttpPost('GetExpressCheckoutDetails', $padata, PPL_API_USER, PPL_API_PASSWORD, PPL_API_SIGNATURE, PPL_MODE);

        if ("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) {

            echo '<br /><b>Stuff to store in database :</b><br /><pre>';
            /*
            #### SAVE BUYER INFORMATION IN DATABASE ###
            //see (http://www.sanwebe.com/2013/03/basic-php-mysqli-usage) for mysqli usage

            $buyerName = $httpParsedResponseAr["FIRSTNAME"].' '.$httpParsedResponseAr["LASTNAME"];
            $buyerEmail = $httpParsedResponseAr["EMAIL"];

            //Open a new connection to the MySQL server
            $mysqli = new mysqli('host','username','password','database_name');

            //Output any connection error
            if ($mysqli->connect_error) {
                die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
            }

            $insert_row = $mysqli->query("INSERT INTO BuyerTable
            (BuyerName,BuyerEmail,TransactionID,ItemName,ItemNumber, ItemAmount,ItemQTY)
            VALUES ('$buyerName','$buyerEmail','$transactionID','$products[0]['ItemName']',$products[0]['ItemNumber'], $products[0]['ItemTotalPrice'],$ItemQTY)");

            if($insert_row){
                print 'Success! ID of last inserted record is : ' .$mysqli->insert_id .'<br />';
            }else{
                die('Error : ('. $mysqli->errno .') '. $mysqli->error);
            }

            */

            echo '<pre>';

            print_r($httpParsedResponseAr);

            echo '</pre>';
        } else {

            echo '<div style="color:red"><b>GetTransactionDetails failed:</b>' . urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]) . '</div>';

            echo '<pre>';

            print_r($httpParsedResponseAr);

            echo '</pre>';

        }
        die();
    }

    function PPHttpPost($methodName_, $nvpStr_)
    {

        // Set up your API credentials, PayPal end point, and API version.
        $API_UserName = urlencode(PPL_API_USER);
        $API_Password = urlencode(PPL_API_PASSWORD);
        $API_Signature = urlencode(PPL_API_SIGNATURE);

        $paypalmode = (PPL_MODE == 'sandbox') ? '.sandbox' : '';

        $API_Endpoint = "https://api-3t" . $paypalmode . ".paypal.com/nvp";
        $version = urlencode('109.0');

        // Set the curl parameters.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        //curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');

        // Turn off the server and peer verification (TrustManager Concept).
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        // Set the API operation, version, and API signature in the request.
        $nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";

        // Set the request as a POST FIELD for curl.
        curl_setopt($ch, CURLOPT_POSTFIELDS, urldecode($nvpreq));
        //curl_setopt($ch,CURLOPT_POSTFIELDS , http_build_query($request));
        // Get response from the server.
        $httpResponse = curl_exec($ch);

        if (!$httpResponse) {
            exit("$methodName_ failed: " . curl_error($ch) . '(' . curl_errno($ch) . ')');
        }

        // Extract the response details.
        $httpResponseAr = explode("&", $httpResponse);

        $httpParsedResponseAr = array();
        foreach ($httpResponseAr as $i => $value) {

            $tmpAr = explode("=", $value);

            if (sizeof($tmpAr) > 1) {

                $httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
            }
        }

        if ((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {

            exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
        }

        return $httpParsedResponseAr;
    }
}
