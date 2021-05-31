<?php

$arr = array(
    'title' => 'Store Plugin',
    'description' => lang("store::store-setting-description"),
    'settings' => array(
        'allow-members-create-store' => array(
            'type' => 'boolean',
            'title' => lang("store::allow-members-to-create-store"),
            'description' => '',
            'value' => 1
        ),
        'enable-admin-approve-product' => array(
            'type' => 'boolean',
            'title' => lang("store::allow-admin-approve-new-products"),
            'description' => lang("store::allow-admin-approve-new-products-desc"),
            'value' => 1
        ),
        'allow-pay-store-on-delivery' => array(
            'type' => 'boolean',
            'title' => lang("store::allow-pay-store-on-delivery"),
            'description' => lang("store::allow-pay-store-on-delivery-desc"),
            'value' => 1
        ),
        'enable-product-social-sharing' => array(
            'type' => 'boolean',
            'title' => lang("store::allow-product-social-sharing"),
            'description' => lang("store::allow-product-social-sharing-desc"),
            'value' => 0
        ),
        'enable-guest-product-purchase' => array(
            'type' => 'boolean',
            'title' => lang("store::allow-guest-product-purchase"),
            'description' => lang("store::allow-guest-product-purchase-desc"),
            'value' => 1
        ),

        /* 'notify-admin-by-email-on-withdrawal-request' => array(
             'type' => 'boolean',
             'title' => lang("store::notify-admin-by-email-on-withdrawal-request"),
             'description' => '',
             'value' => 0
         ),*/

        'currency-sign' => array(
            'type' => 'text',
            'title' => lang('store::currency-sign'),
            'description' => '',
            'value' => '$'
        ),

        'enable-two-checkout' => array(
            'type' => 'boolean',
            'title' => lang('store::enable-two-checkout'),
            'description' => '',
            'value' => 1
        ),
        '2checkout-secret' => array(
            'type' => 'text',
            'title' => lang('store::2checkout-secret'),
            'description' => 'This can be set on your 2checkout client page',
            'value' => ''
        ),

        'Two-checkout-seller-ID' => array(
            'type' => 'text',
            'title' => lang('store::Two-checkout-seller-id'),
            'description' => '',
            'value' => ''
        ),

        'send_notification_on_new_order' => array(
            'type' => 'boolean',
            'title' => lang('store::send-notification-to-store-owner'),
            'description' => '',
            'value' => 1
        ),


        'minimum-withdrawal-limit' => array(
            'type' => 'text',
            'title' => lang('store::minimum-withdrawal-limit'),
            'description' => '',
            'value' => '50'
        ),

        'commission-per-sale' => array(
            'type' => 'text',
            'title' => lang('store::percentage-commission-per-sale'),
            'description' => lang("store::set-percentage-commision-on-each-sale"),
            'value' => 0
        ),
        'allow-members-to-boost-product' => array(
            'type' => 'boolean',
            'title' => lang('store::allow-member-to-boost-product'),
            'description' => 'This works with Booster plugin',
            'value' => 1
        ),

        'send_seller_sms_notification' => array(
            'type' => 'boolean',
            'title' => lang('store::send_seller_sms_notification'),
            'description' => 'This works with SMS plugin',
            'value' => 1
        ),
        'enable-sms-for-succesful-order-notification' => array(
            'type' => 'boolean',
            'title' => 'Enable seller to recieve SMS notification on successful order',
            'description' => 'Send SMS to Store owner on successful order',
            'value' => 1
        ),
        'disable-store-shipping' => array(
            'type' => 'boolean',
            'title' => 'Disable Shipping Setting for Vendors, ',
            'description' => 'This is suitable for Digital Products when shipping is not involved',
            'value' => 0
        ),

        /*'show-sidebar-menu-only-when-product-is-added-to-cart' => array(
            'type' => 'boolean',
            'title' => 'Show side bar Menu Only when product is added to cart',
            'description' => 'If enabled, it will pnly show side bar menu Only when product is added to cart',
            'value' => 0
        ),*/
        /*'cart-side-bar-position' => array(
            'type' => 'selection',
            'title' => 'Cart Side Bar Menu Position',
            'description' => 'If enabled, it will pnly show side bar menu Only when product is added to cart',
            'value' => 2,
            'data' => array(
                '1' => 'Left',
                '2' => 'Right'
            )
        ),
        'cart-side-bar-top' => array(
            'type' => 'text',
            'title' => 'Cart SideBar Position to the Top',
            'description' => 'Set the top position of the Cart Menu sidebar',
            'value' => '200px'
        ),*/
    )
);
$v = app()->version;
if ($v > 7.1) {
    return array(
        'site-other-settings' => $arr
    );
} else {
    return $arr;
}
