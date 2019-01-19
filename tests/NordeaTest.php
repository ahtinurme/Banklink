<?php

namespace RKD\BanklinkTests;

/**
 * Test suite for Nordea banklink.
 *
 * @author  Rene Korss <rene.korss@gmail.com>
 */
class NordeaTest extends SEBTest
{
    protected $bankClass = "RKD\Banklink\Nordea";

    protected $requestUrl = [
        'payment' => 'https://netbank.nordea.com/pnbepay/epayp.jsp',
        'auth' => 'https://netbank.nordea.com/pnbepay/epayp.jsp'
    ];
    protected $testRequestUrl = [
        'payment' => 'https://netbank.nordea.com/pnbepaytest/epayp.jsp',
        'auth' => 'https://netbank.nordea.com/pnbepaytest/epayp.jsp'
    ];
}
