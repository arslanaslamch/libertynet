<?php
return array(
    'integrations' => array(
        'title' => lang('cryptopay::cryptopay'),
        'description' => '',
        'settings' => array(
            'enable-cryptopay' => array(
                'type' => 'boolean',
                'title' => lang('cryptopay::enable-cryptopay'),
                'description' => lang('cryptopay::enable-cryptopay-desc'),
                'value' => 0,
            ),
            'bitcoin-public-key' => array(
                'title' => lang('cryptopay::bitcoin-public-key'),
                'description' => lang('cryptopay::bitcoin-public-key-desc'),
                'type' => 'text',
                'value' => '25654AAo79c3Bitcoin77BTCPUBqwIefT1j9fqqMwUtMI0huVL',
            ),
            'bitcoin-private-key' => array(
                'title' => lang('cryptopay::bitcoin-private-key'),
                'description' => lang('cryptopay::bitcoin-private-key-desc'),
                'type' => 'text',
                'value' => '25654AAo79c3Bitcoin77BTCPRV0JG7w3jg0Tc5Pfi34U8o5JE',
            ),
            'bitcoincash-public-key' => array(
                'title' => lang('cryptopay::bitcoincash-public-key'),
                'description' => lang('cryptopay::bitcoincash-public-key-desc'),
                'type' => 'text',
                'value' => '25656AAeOGaPBitcoincash77BCHPUBOGF20MLcgvHMoXHmMRx',
            ),
            'bitcoincash-private-key' => array(
                'title' => lang('cryptopay::bitcoincash-private-key'),
                'description' => lang('cryptopay::bitcoincash-private-key-desc'),
                'type' => 'text',
                'value' => '25656AAeOGaPBitcoincash77BCHPRV8quZcxPwfEc93ArGB6D',
            ),
            'bitcoinsv-public-key' => array(
                'title' => lang('cryptopay::bitcoinsv-public-key'),
                'description' => lang('cryptopay::bitcoinsv-public-key-desc'),
                'type' => 'text',
                'value' => '36306AAQUmatBitcoinsv77BSVPUBlK6jR1TDDQUzaQV1AmWAE',
            ),
            'bitcoinsv-private-key' => array(
                'title' => lang('cryptopay::bitcoinsv-private-key'),
                'description' => lang('cryptopay::bitcoinsv-private-key-desc'),
                'type' => 'text',
                'value' => '36306AAQUmatBitcoinsv77BSVPRVJQJx21y8kvd7xxEWzK3zA',
            ),
            'litecoin-public-key' => array(
                'title' => lang('cryptopay::litecoin-public-key'),
                'description' => lang('cryptopay::litecoin-public-key-desc'),
                'type' => 'text',
                'value' => '25657AAOwwzoLitecoin77LTCPUB4PVkUmYCa2dR770wNNstdk',
            ),
            'litecoin-private-key' => array(
                'title' => lang('cryptopay::litecoin-private-key'),
                'description' => lang('cryptopay::litecoin-private-key-desc'),
                'type' => 'text',
                'value' => '25657AAOwwzoLitecoin77LTCPRV7hmp8s3ew6pwgOMgxMq81F',
            ),
            'dogecoin-public-key' => array(
                'title' => lang('cryptopay::dogecoin-public-key'),
                'description' => lang('cryptopay::dogecoin-public-key-desc'),
                'type' => 'text',
                'value' => '25678AACxnGODogecoin77DOGEPUBZEaJlR9W48LUYagmT9LU8',
            ),
            'dogecoin-private-key' => array(
                'title' => lang('cryptopay::dogecoin-private-key'),
                'description' => lang('cryptopay::dogecoin-private-key-desc'),
                'type' => 'text',
                'value' => '25678AACxnGODogecoin77DOGEPRVFvl6IDdisuWHVJLo5m4eq',
            ),
            'dash-public-key' => array(
                'title' => lang('cryptopay::dash-public-key'),
                'description' => lang('cryptopay::dash-public-key-desc'),
                'type' => 'text',
                'value' => '25658AAo79c3Dash77DASHPUBqwIefT1j9fqqMwUtMI0huVL0J',
            ),
            'dash-private-key' => array(
                'title' => lang('cryptopay::dash-private-key'),
                'description' => lang('cryptopay::dash-private-key-desc'),
                'type' => 'text',
                'value' => '25658AAo79c3Dash77DASHPRVG7w3jg0Tc5Pfi34U8o5JEiTss',
            ),
            'speedcoin-public-key' => array(
                'title' => lang('cryptopay::speedcoin-public-key'),
                'description' => lang('cryptopay::speedcoin-public-key-desc'),
                'type' => 'text',
                'value' => '20116AA36hi8Speedcoin77SPDPUBjTMX31yIra1IBRssY7yFy',
            ),
            'speedcoin-private-key' => array(
                'title' => lang('cryptopay::speedcoin-private-key'),
                'description' => lang('cryptopay::speedcoin-private-key-desc'),
                'type' => 'text',
                'value' => '20116AA36hi8Speedcoin77SPDPRVNOwjzYNqVn4Sn5XOwMI2c',
            ),
            'reddcoin-public-key' => array(
                'title' => lang('cryptopay::reddcoin-public-key'),
                'description' => lang('cryptopay::reddcoin-public-key-desc'),
                'type' => 'text',
                'value' => '',
            ),
            'reddcoin-private-key' => array(
                'title' => lang('cryptopay::reddcoin-private-key'),
                'description' => lang('cryptopay::reddcoin-private-key-desc'),
                'type' => 'text',
                'value' => '',
            ),
            'potcoin-public-key' => array(
                'title' => lang('cryptopay::potcoin-public-key'),
                'description' => lang('cryptopay::potcoin-public-key-desc'),
                'type' => 'text',
                'value' => '',
            ),
            'potcoin-private-key' => array(
                'title' => lang('cryptopay::potcoin-private-key'),
                'description' => lang('cryptopay::potcoin-private-key-desc'),
                'type' => 'text',
                'value' => '',
            ),
            'feathercoin-public-key' => array(
                'title' => lang('cryptopay::feathercoin-public-key'),
                'description' => lang('cryptopay::feathercoin-public-key-desc'),
                'type' => 'text',
                'value' => '',
            ),
            'feathercoin-private-key' => array(
                'title' => lang('cryptopay::feathercoin-private-key'),
                'description' => lang('cryptopay::feathercoin-private-key-desc'),
                'type' => 'text',
                'value' => '',
            ),
            'vertcoin-public-key' => array(
                'title' => lang('cryptopay::vertcoin-public-key'),
                'description' => lang('cryptopay::vertcoin-public-key-desc'),
                'type' => 'text',
                'value' => '',
            ),
            'vertcoin-private-key' => array(
                'title' => lang('cryptopay::vertcoin-private-key'),
                'description' => lang('cryptopay::vertcoin-private-key-desc'),
                'type' => 'text',
                'value' => '',
            ),
            'peercoin-public-key' => array(
                'title' => lang('cryptopay::peercoin-public-key'),
                'description' => lang('cryptopay::peercoin-public-key-desc'),
                'type' => 'text',
                'value' => '',
            ),
            'peercoin-private-key' => array(
                'title' => lang('cryptopay::peercoin-private-key'),
                'description' => lang('cryptopay::peercoin-private-key-desc'),
                'type' => 'text',
                'value' => '',
            ),
            'monetaryunit-public-key' => array(
                'title' => lang('cryptopay::monetaryunit-public-key'),
                'description' => lang('cryptopay::monetaryunit-public-key-desc'),
                'type' => 'text',
                'value' => '',
            ),
            'monetaryunit-private-key' => array(
                'title' => lang('cryptopay::monetaryunit-private-key'),
                'description' => lang('cryptopay::monetaryunit-private-key-desc'),
                'type' => 'text',
                'value' => '',
            ),
            'universalcurrency-public-key' => array(
                'title' => lang('cryptopay::universalcurrency-public-key'),
                'description' => lang('cryptopay::universalcurrency-public-key-desc'),
                'type' => 'text',
                'value' => '',
            ),
            'universalcurrency-private-key' => array(
                'title' => lang('cryptopay::universalcurrency-private-key'),
                'description' => lang('cryptopay::universalcurrency-private-key-desc'),
                'type' => 'text',
                'value' => '',
            ),
            'gourl-webdev-key' => array(
                'title' => lang('cryptopay::gourl-webdev-key'),
                'description' => lang('cryptopay::gourl-webdev-key-desc'),
                'type' => 'text',
                'value' => '',
            )
        )
    )
);