<?php

namespace RKD\Banklink\Test\LT;

/**
 * Test suite for Swedbank banklink.
 *
 * @author  Rene Korss <rene.korss@gmail.com>
 */
class SwedbankTest extends SEBTest
{
    protected $bankClass = "RKD\Banklink\LT\Swedbank";

    protected $requestUrl = [
        'payment' => 'https://www.swedbank.lt/banklink/',
        'auth' => 'https://www.swedbank.lt/banklink/'
    ];
    protected $testRequestUrl = [
        'payment' => 'https://www.swedbank.lt/banklink/',
        'auth' => 'https://www.swedbank.lt/banklink/'
    ];

    /**
     * Set test data.
     */
    public function setUp() : void
    {
        parent::setUp();

        // Swedbank has encoding
        $this->expectedData['VK_SERVICE'] = '1002';
        $this->expectedData['VK_ENCODING'] = 'UTF-8';
        $this->expectedData['VK_MAC'] = 'CyAQksE1zTLWXvIJ2yCbn1UMQPwSdpS0Ql/Jc9VMIwuobeM9stWAM7dV6XEmb4U8x8AxUlkiWZEpK+lOKz/+jIqq4xbUDVR6M/lGUXXSurt4IZgmf13Kjniwoy8PKbN8bla0WPoAFeNK3oLEKBmRsqJfMXkXe3Bhle63XCrIb+Y=';

        unset($this->expectedData['VK_NAME']);
        unset($this->expectedData['VK_ACC']);


        $this->setUpBanklink();
    }

    /**
     * Test custom request data
     */
    public function testCustomRequestData()
    {
        $this->bank = new $this->bankClass($this->protocol);

        $customData = [
            'INAPP' => 1, // new data
            'VK_REF' => 'mycustomref', // override data
            'VK_MAC' => 'H99NKHaAoHr0Dmj4xJJBIVdiKkzWCMn5oUGKulTKWVgMrSFVFyUrXPVXTRl+t7f9PmQMYQ+LHbeENwhCiLGstlEjm53l61UD9pGMJv+YJFTOh2tOPifnzEE1wsck5wJlnOUY8yz64dGD2igR35S6GKdOyauie3MFcfEsFYSbkFw='
        ];

        $this->expectedData = array_merge($this->expectedData, $customData);

        $request = $this->bank->getPaymentRequest($this->orderId, $this->amount, $this->message, $this->language, $this->currency, $customData, $this->timezone);

        // Custom url
        $this->assertEquals($this->expectedData, $request->getRequestData());
    }
}
