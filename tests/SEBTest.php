<?php

namespace RKD\BanklinkTests;

use RKD\Banklink;
use RKD\Banklink\Protocol\Helper\ProtocolHelper;
use RKD\Banklink\Protocol\IPizza;
use RKD\Banklink\Response\PaymentResponse;
use RKD\Banklink\Request\PaymentRequest;

/**
 * Test suite for SEB banklink.
 *
 * @author  Rene Korss <rene.korss@gmail.com>
 */
class SEBTest extends \PHPUnit_Framework_TestCase
{
    protected $bankClass = "RKD\Banklink\SEB";

    protected $requestUrl = 'https://www.seb.ee/cgi-bin/unet3.sh/un3min.r';
    protected $testRequestUrl = 'http://localhost:8080/banklink/seb-common';

    private $protocol;
    private $seb;

    private $sellerId;
    private $sellerName;
    private $sellerAccount;

    private $senderName;

    private $orderId;
    private $amount;
    private $message;
    private $language;
    private $currency;
    private $timezone;
    private $datetime;
    private $expectedData;

    private $customRequestUrl;

    /**
     * Set test data.
     */
    public function setUp()
    {
        $this->sellerId = 'id2000';
        $this->sellerName = 'Ülo Pääsuke';
        $this->sellerAccount = '1010342342354345435';

        $this->senderName = 'Toomas Jäär';

        $this->orderId = 100;
        $this->amount = 10.00;
        $this->message = 'First payment';
        $this->language = 'EST';
        $this->currency = 'EUR';
        $this->timezone = 'Europe/Tallinn';

        // From ENV variable
        $this->datetime = getenv('TEST_DATETIME');

        $this->customRequestUrl = 'http://example.com';

        $this->protocol = new IPizza(
            $this->sellerId,
            __DIR__.'/keys/IPizza/private_key.pem',
            '',
            __DIR__.'/keys/IPizza/public_key.pem',
            $this->customRequestUrl
        );

        $this->seb = new $this->bankClass($this->protocol);

        $this->expectedData = array(
            'VK_SERVICE' => '1012',
            'VK_VERSION' => '008',
            'VK_SND_ID' => $this->sellerId,
            'VK_STAMP' => $this->orderId,
            'VK_AMOUNT' => $this->amount,
            'VK_CURR' => $this->currency,
            'VK_REF' => ProtocolHelper::calculateReference($this->orderId),
            'VK_MSG' => $this->message,
            'VK_RETURN' => $this->customRequestUrl,
            'VK_CANCEL' => $this->customRequestUrl,
            'VK_LANG' => $this->language,
            'VK_MAC' => 'PmAB256IR1FzTKZHNn5LBPso/KyLAhNcTOMq82lhpYn0mXKYtVtpNkolQxyETnTcIn1TcYOmekJEATe86Bz2MRljEQqllkaIl7bNuLCtuBPtAOYWNLmQHoop+5QSiguJEmEV+JJU3w4BApjWcsHA5HYlYze+3L09UO6na0lB/Zs=',
            'VK_DATETIME' => $this->datetime,
            'VK_ENCODING' => 'UTF-8',
        );
    }

    /**
     * Test for correctly generated request data for service 1012.
     */
    public function testGetPaymentRequestService1012()
    {

        // Test service 1012
        $request = $this->seb->getPaymentRequest($this->orderId, $this->amount, $this->message, $this->language, $this->currency, $this->timezone);

        // Instance of PaymentRequest and data is same
        $this->assertInstanceOf('RKD\Banklink\Request\PaymentRequest', $request);
        $this->assertEquals($this->expectedData, $request->getRequestData());

        // Production env url
        $this->assertEquals($this->requestUrl, $request->getRequestUrl());
    }

    /**
     * Test for correctly generated request data for service 1011
     * Test debug url.
     */
    public function testGetPaymentRequestService1011()
    {

        // Test service 1011
        $this->protocol = new IPizza(
            $this->sellerId,
            __DIR__.'/keys/IPizza/private_key.pem',
            '',
            __DIR__.'/keys/IPizza/public_key.pem',
            $this->customRequestUrl,
            $this->sellerName,
            $this->sellerAccount
        );

        $this->seb = new $this->bankClass($this->protocol);
        $this->seb->debugMode();

        // New expected values
        $this->expectedData['VK_SERVICE'] = '1011';
        $this->expectedData['VK_ACC'] = $this->sellerAccount;
        $this->expectedData['VK_NAME'] = $this->sellerName;
        $this->expectedData['VK_MAC'] = 'PuJTjADqHeArALfzTo2ZsynckTOVRFZMnOnbv9tv30KrF2a9m/yJuRn9vcd3JuaSjgzKoS7DRSouDgXAe6GNLZnduhXZrYx5JtVMmnlgooQ+/pJqO6ZOzwsEjaXooTLCCnKA5P9zWoxXpe8Al4IC9pj7jLNFG3dCeG9XO5uRZEs=';
        $this->expectedData['VK_DATETIME'] = $this->datetime;

        $request = $this->seb->getPaymentRequest($this->orderId, $this->amount, $this->message, $this->language, $this->currency, $this->timezone);

        // Instance of PaymentRequest and data is same
        $this->assertInstanceOf('RKD\Banklink\Request\PaymentRequest', $request);
        $this->assertEquals($this->expectedData, $request->getRequestData());

        // Test env url
        $this->assertEquals($this->testRequestUrl, $request->getRequestUrl());

        // Get HTML
        $this->assertContains('<input type="hidden"', $request->getRequestInputs());
    }

    /**
     * Test successful payment response.
     */
    public function testHandlePaymentResponseSuccess()
    {
        $responseData = array(
            'VK_SERVICE' => '1111',
            'VK_VERSION' => '008',
            'VK_SND_ID' => $this->senderName,
            'VK_REC_ID' => $this->sellerId,
            'VK_STAMP' => $this->orderId,
            'VK_T_NO' => 100,
            'VK_AMOUNT' => $this->amount,
            'VK_CURR' => $this->currency,
            'VK_REC_ACC' => $this->sellerAccount,
            'VK_REC_NAME' => $this->sellerName,
            'VK_SND_ACC' => '101032423434543',
            'VK_SND_NAME' => 'Mart Mets',
            'VK_REF' => $this->orderId,
            'VK_MSG' => $this->message,
            'VK_MAC' => 'Sp0VzYSPyZviiCewmwbtqny8cYRcnYU4Noh0cwxOYoZ5IpQwHuolNbFI+1Kkuk5n6cWs2X48IYYOUMRi9VTqdsfSN7z5jpUwEwjLsCMDUDdro421Je7eXXkEkbZlEcgY8wtR5H+OO955aqxDdZeS0dkuuxTN70Z9Esv5feXYxsw=',
            'VK_T_DATETIME' => $this->datetime,
            'VK_ENCODING' => 'UTF-8',
        );

        $response = $this->seb->handleResponse($responseData);

        $this->assertInstanceOf('RKD\Banklink\Response\PaymentResponse', $response);
        $this->assertEquals(PaymentResponse::STATUS_SUCCESS, $response->getStatus());

        // This is valid response
        $this->assertTrue($response->wasSuccessful());
    }

    /**
     * Test failed payment response.
     */
    public function testHandlePaymentResponseError()
    {
        $responseData = array(
            'VK_SERVICE' => '1911',
            'VK_VERSION' => '008',
            'VK_SND_ID' => $this->senderName,
            'VK_REC_ID' => $this->sellerId,
            'VK_STAMP' => $this->orderId,
            'VK_REF' => $this->orderId,
            'VK_MSG' => $this->message,
            'VK_MAC' => 'o4rju0oEwITuIheUdtDjp2njKhBzvQv8RjKg+rdCB+fwGiUS8zpXzr0I+wj0vl13h+ACGAR1LO9gR2+IG1yq+AJdQdVszJIbeA1jcg1GFtl1xyLN8LXYfubHHUB/7EWwiEGZKcHrNp3pAsADlLwySQLRWatheMLPqRRk2FX96Ko=',
            'VK_DATETIME' => $this->datetime,
        );

        $response = $this->seb->handleResponse($responseData);

        $this->assertInstanceOf('RKD\Banklink\Response\PaymentResponse', $response);
        $this->assertEquals(PaymentResponse::STATUS_ERROR, $response->getStatus());

        // This is not valid response, so validation should fail
        $this->assertFalse($response->wasSuccessful());
    }

    /**
     * Test authentication request data
     * Test service 4011.
     */
    public function testGetAuthRequest4011()
    {
        $expectedData = array(
            'VK_SERVICE' => '4011',
            'VK_VERSION' => '008',
            'VK_SND_ID' => 'id2000',
            'VK_RETURN' => 'http://example.com',
            'VK_DATETIME' => '2015-09-29T15:00:00+0300',
            'VK_RID' => '',
            'VK_LANG' => 'EST',
            'VK_REPLY' => '3012',
            'VK_ENCODING' => 'UTF-8',
            'VK_MAC' => 'tCzsgSP0NVlNDvzsPnDZpwfPDwlrWoLFOUDSJ80sYDMbPsXBiid0M8xKT9ep0KVmj8BBUwWOGGjENSkaNXcZKAoqw0h1V1J7Hxuy1/gnIgkAkiY1OQftMYNuyrmKj1xVP4JGH3kp4ZEiyXJ0ySj/VGW4P1Vyv2oMUVHN+vDqHR0=',
        );

        $request = $this->seb->getAuthRequest();

        $this->assertInstanceOf('RKD\Banklink\Request\AuthRequest', $request);
        $this->assertEquals($expectedData, $request->getRequestData());

        // Test env url
        $this->assertEquals($this->requestUrl, $request->getRequestUrl());

        // Get HTML
        $this->assertContains('<input type="hidden"', $request->getRequestInputs());
    }

    /**
     * Test authentication request data
     * Test service 4012.
     */
    public function testGetAuthRequest4012()
    {
        $expectedData = array(
            'VK_SERVICE' => '4012',
            'VK_VERSION' => '008',
            'VK_SND_ID' => 'id2000',
            'VK_REC_ID' => 'bank-id',
            'VK_NONCE' => 'random-nonce',
            'VK_RETURN' => 'http://example.com',
            'VK_DATETIME' => $this->datetime,
            'VK_RID' => 'random-rid',
            'VK_LANG' => 'EST',
            'VK_ENCODING' => 'UTF-8',
            'VK_MAC' => 'MtmH+8VgmKhw/Q6kO4EZdgNMP9ZWhCXfO0OHUgyHd74ofhdkvhLnzSWxqHZgWv9lCo3ZSrZ1mHJEf1rezBod7QQDcPmMVHl9iijJug2oySgT27Re89oytVN3Zlzmko9LFEaE8JIYnvxN4B9mc/bWfW0hvHSyBehpWdlVO5HIO+c=',
        );

        $request = $this->seb->getAuthRequest('bank-id', 'random-nonce', 'random-rid');

        $this->assertInstanceOf('RKD\Banklink\Request\AuthRequest', $request);
        $this->assertEquals($expectedData, $request->getRequestData());

        // Test env url
        $this->assertEquals($this->requestUrl, $request->getRequestUrl());

        // Get HTML
        $this->assertContains('<input type="hidden"', $request->getRequestInputs());

        // Get same data again, already exists
        $request = $this->seb->getAuthRequest('bank-id', 'random-nonce', 'random-rid');

        $this->assertInstanceOf('RKD\Banklink\Request\AuthRequest', $request);
        $this->assertEquals($expectedData, $request->getRequestData());
    }

    /**
     * Test custom request url.
     */
    public function testCustomRequestUrl()
    {
        $this->seb = new $this->bankClass($this->protocol);
        $this->seb->setRequestUrl('http://google.com');

        $request = $this->seb->getPaymentRequest($this->orderId, $this->amount, $this->message, $this->language, $this->currency, $this->timezone);

        // Get same data again, already exists
        $request = $this->seb->getPaymentRequest($this->orderId, $this->amount, $this->message, $this->language, $this->currency, $this->timezone);

        // Custom url
        $this->assertEquals('http://google.com', $request->getRequestUrl());
    }
}
