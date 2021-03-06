<?php
// code modified from source: https://cms.paypal.com/cms_content/US/en_US/files/developer/nvp_MassPay_php.txt
// documentation: https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/howto_api_masspay
// sample code: https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/library_code

// eMail subject to receivers
$vEmailSubject = 'Pagamento Paypal';

/** MassPay NVP example.
 *
 *  Pay one or more recipients.
 */

// For testing environment: use 'sandbox' option. Otherwise, use 'live'.
// Go to www.x.com (PayPal Integration center) for more information.
$environment = 'sandbox'; // or 'beta-sandbox' or 'live'.

/**
 * Send HTTP POST Request
 *
 * @param string The API method name
 * @param string The POST Message fields in &name=value pair format
 * @return array Parsed HTTP Response body
 */
function PPHttpPost($methodName_, $nvpStr_,$environment='sandbox')
{
    ;

    // Set up your API credentials, PayPal end point, and API version.
    // How to obtain API credentials:
    // https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_NVPAPIBasics#id084E30I30RO
    $API_UserName = urlencode('seundudu_api1.gmail.com');
    $API_Password = urlencode('SRP49LPKAJZRTZEJ');
    $API_Signature = urlencode('AFcWxV21C7fd0v3bYYYRCpSSRl31Al4cw0zUzmc73qebzhpd8xnxkYg5');
    $API_Endpoint = "https://api-3t.paypal.com/nvp";
    if("sandbox" == $environment || "beta-sandbox" === $environment)
    {
        $API_Endpoint = "https://api-3t.$environment.paypal.com/nvp";
        //https://api-3t.sandbox.paypal.com/nvp
    }
   // echo $API_Endpoint; die();
    $version = urlencode('51.0');

    // Set the curl parameters.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);

   // curl_setopt($ch, CURLOPT_SSLVERSION, 6);
   // curl_setopt($ch, CURLOPT_SSLVERSION, 4);
   // curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'SSLv4');
    //curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
    // Turn off the server and peer verification (TrustManager Concept).
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);


    // Set the API operation, version, and API signature in the request.

    $nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";

    // Set the request as a POST FIELD for curl.
    curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq."&".$nvpStr_);

    // Get response from the server.
    $httpResponse = curl_exec($ch);

    if( !$httpResponse)
    {
        echo $methodName_ . ' failed: ' . curl_error($ch) . '(' . curl_errno($ch) .')';
    }

    // Extract the response details.
    $httpResponseAr = explode("&", $httpResponse);

    $httpParsedResponseAr = array();
    foreach ($httpResponseAr as $i => $value)
    {
        $tmpAr = explode("=", $value);
        if(sizeof($tmpAr) > 1)
        {
            $httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
        }
    }

    if((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr))
    {
        exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
    }
    print_r($httpParsedResponseAr);

    return $httpParsedResponseAr;
}

// Set request-specific fields.
$emailSubject = urlencode($vEmailSubject);
$receiverType = urlencode('EmailAddress');
$currency = urlencode(config('default-currency')); // or other currency ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')

// Receivers
// Use '0' for a single receiver. In order to add new ones: (0, 1, 2, 3...)
// Here you can modify to obtain array data from database.
$receivers = array();
$store_and_total = rearrange_product_quantity($pq);
foreach($store_and_total as $store_id=>$total){
    $store = lpGetStoreById($store_id);
    $storePaypalemail = $store['corporate_email'];
    $receivers[] = array(
        'receiverEmail' => $storePaypalemail,
        'amount' => $total,
        'uniqueID' => "storeid_".$store_id // 13 chars max
        //'note' => " pagamento de comiss??o Fashiontuts" // I recommend use of space at beginning of string.
    );
}
//$receivers = array();
/*$receivers = array(
    0 => array(
        'receiverEmail' => "ricardoglrj@yahoo.com.br",
        'amount' => "0.01",
        'uniqueID' => "id_001", // 13 chars max
        'note' => " pagamento de comiss??o Fashiontuts"), // I recommend use of space at beginning of string.
    1 => array(
        'receiverEmail' => "ricardo@brgweb.com.br",
        'amount' => "0.02",
        'uniqueID' => "id_002", // 13 chars max, available in 'My Account/Overview/Transaction details' when the transaction is made
        'note' => " pagamento de comiss??o fashiontuts"  // space again at beginning.
    )
);*/
$receiversLenght = count($receivers);

// Add request-specific fields to the request string.
$nvpStr="&EMAILSUBJECT=$emailSubject&RECEIVERTYPE=$receiverType&CURRENCYCODE=$currency";

//add response


/*$nvpStr .="&RETURN=".urlencode(url_to_pager('orders-checkout-payment').'?action=paypal&type=success');
$nvpStr .="&CANCEL_RETURN=".urlencode(url_to_pager('orders-checkout-payment').'?action=paypal&type=cancel');
$nvpStr .="&NOTIFY_URL=".urlencode(url_to_pager('product-paypal-notify'));
$nvpStr .="&INVOICE=".urlencode($invoice);*/

$receiversArray = array();

for($i = 0; $i < $receiversLenght; $i++)
{
    $receiversArray[$i] = $receivers[$i];
}

foreach($receiversArray as $i => $receiverData)
{
    $receiverEmail = urlencode($receiverData['receiverEmail']);
    $amount = urlencode($receiverData['amount']);
    $uniqueID = urlencode($receiverData['uniqueID']);
    //$note = urlencode($receiverData['note']);
    $nvpStr .= "&L_EMAIL$i=$receiverEmail&L_Amt$i=$amount&L_UNIQUEID$i=$uniqueID";
}

// Execute the API operation; see the PPHttpPost function above.
$httpParsedResponseAr = PPHttpPost('MassPay', $nvpStr);

if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"]))
{
    //echo 'MassPay Completed Successfully: ' . print_r($httpParsedResponseAr);
    //the order
    $order = getSingleOrder($lastInsertedOrder);
    updateOrderStatus($order,'successful');
    $p = url_to_pager('orders-checkout-payment').'?action=paypal&type=success';
    redirect($p);
  // return redirect_to_pager($p);
}
else
{
   /* echo '\nMassPay failed: ';
    print_r($httpParsedResponseAr);*/
    $p = url_to_pager('orders-checkout-payment').'?action=paypal&type=cancel';
    redirect($p);
  //  return redirect_to_pager($p);
}

?>