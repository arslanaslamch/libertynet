<?php

function get_store_shipping_settings($store_id, $type = 'single')
{
    $result = array();
    $sql = db()->query("SELECT * FROM lp_shipping_settings WHERE store_id='{$store_id}'");
    if ($type == 'all') {
        $sql = db()->query("SELECT * FROM lp_shipping_settings");
    }
    if ($sql->num_rows > 0) {
        while ($r = $sql->fetch_assoc()) {
            $result[] = $r;
        }
    }
    return $result;
}


function save_shipping_settings($val)
{
    $expected = array(
        'zone' => '',
        'shipping_method' => '',
        'regions' => serialize(array()),
        'amount' => '0',
        'save_type' => 'new_shipping'

    );
    /**
     * @var $zone
     * @var $shipping_method
     * @var $regions
     * @var $amount
     * @var $save_type
     *
     */
    extract(array_merge($expected, $val));
    $zone = sanitizeText($zone);
    $shipping_method = sanitizeText($shipping_method);
    $amount = sanitizeText($amount);
    $store_id = $val['store_id'];
    $time = time();
    //print_r($val['products']); die();
    $regions = serialize($val['regions']);
    $userid = get_userid();

    if ($save_type == 'new_shipping') {
        $sql = db()->query("INSERT INTO lp_shipping_settings(store_id,user_id,zone,shipping_method,amount,time,regions)
         VALUES('{$store_id}','{$userid}','{$zone}','{$shipping_method}','{$amount}','{$time}','{$regions}')");
        $inserted_id = db()->insert_id;
        fire_hook('shipping.added', null, array($inserted_id, $val));
    }
    if ($save_type == 'update_shipping') {
        $shipping_id = $val['shipping_id'];
        $sql = db()->query("UPDATE lp_shipping_settings SET shipping_method='{$shipping_method}',amount='{$amount}',
        regions='{$regions}',zone='{$zone}' WHERE id='{$shipping_id}'");
        //echo db()->error;die();
        fire_hook('shipping.updated', null, array($shipping_id, $val));
    }
    return true;
}

function getSingleShipping($id)
{
    $sql = db()->query("SELECT * FROM lp_shipping_settings WHERE id='{$id}'");
    if ($sql->num_rows > 0) {
        return $sql->fetch_assoc();
    }
    return false;
}

function deleteShipping($id)
{
    db()->query("DELETE FROM lp_shipping_settings WHERE id='{$id}'");
    return true;
}

function isShippingSettingsOwner($shipping)
{
    // print_r($shipping);die();
    if ($shipping['user_id'] == get_userid()) return true;
    return false;
}

function getShippingCostFromThisStores($products_arr, $just_sum = false)
{
    if(config('disable-store-shipping',false)) return 0;
    //store_ids now contain pid and product key
    $html = "";
    if ($products_arr) {
        $sum = array();
        $stores = array();
        //this user shipping details
        $shipping_address = getBillingOrShippingDetails('lp_shipping_details', 'mumu', get_userid());
        foreach ($products_arr as $k => $p) {
            //print_r($p);die();
            //let us check if the product is shippable
            /*print_r($p);
            die();*/
            $id = $p['store_id']; //store the id
            if ($p['type'] == 'tangible') {
                //now it make sense for us to get shipping price
                //$shipping_settings =  getSingleShipping($id);
                $shipping_address = getBillingOrShippingDetails('lp_shipping_details', 'mumu', get_userid());
                $country = $shipping_address['country'];
                $shipping_settings = getShippingSettingsFromOfThisCountry($id, $country);
                if ($shipping_settings) {
                    //we don't want to add many shiping price for of the same store twice
                    if (!in_array($id, $stores)) {
                        $stores[] = $id;
                        //it is only once of the shipping price of a store that will be added.
                        $sum[] = $shipping_settings['amount'];
                    }

                } else {
                    //the store owner does not ship to this address
                    $html .= '<b>' . $p['name'] . '</b> ' . lang("store::can-not-be-shipped-to-your-address") . '<br/>';
                }
            }

        }
        if ($sum) {
            $sum = array_sum($sum);
            if($just_sum) return $sum;
            if ($html) {
                //if one or more product can not be shipped, then return an array
                $result['html'] = $html;
                $result['sum'] = $sum;
                return $result;
            }
            return $sum;
        } else {
            return "<span class='alert-danger'>" . $html . "</span>";
        }
    }
    return "";
}

function getShippingSettingsFromOfThisCountry($s_id, $country)
{
    $sql = db()->query("SELECT * FROM lp_shipping_settings WHERE store_id='{$s_id}' AND regions LIKE '%{$country}%'");
    if ($sql->num_rows > 0) {
        return $sql->fetch_assoc();
    }
    return false;
}

