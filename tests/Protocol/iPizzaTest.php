<?php
namespace RKD\Banklink\iPizza;

use RKD\Banklink;
use RKD\Banklink\Protocol\Helper\ProtocolHelper;
use RKD\Banklink\Protocol\iPizza;
use RKD\Banklink\Response\PaymentResponse;
use RKD\Banklink\Response\AuthResponse;
use RKD\Banklink\Request\PaymentRequest;

/**
 * Test suite for iPizza protocol
 *
 * @author  Rene Korss <rene.korss@gmail.com>
 */

class iPizzaTest extends \PHPUnit_Framework_TestCase{

    private $protocol;

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

    private $requestUrl;

    /**
     * Set test data
     */

    public function setUp(){

        $this->sellerId      = 'id2000';
        $this->sellerAccount = '1010342342354345435';
        $this->sellerName    = 'Ülo Pääsuke';

        $this->senderName    = 'Toomas Jäär';

        $this->orderId       = 100;
        $this->amount        = 10.00;
        $this->message       = 'First payment';
        $this->language      = 'EST';
        $this->currency      = 'EUR';
        $this->timezone      = 'Europe/Tallinn';

        // From ENV variable
        $this->datetime      = getenv('TEST_DATETIME');

        $this->requestUrl    = 'http://example.com';

        $this->protocol = new iPizza(
            $this->sellerId,
            __DIR__.'/../keys/iPizza/private_key.pem',
            '',
            __DIR__.'/../keys/iPizza/public_key.pem',
            $this->requestUrl
        );

        // Test data
        $this->expectedData = array(
            'VK_SERVICE'  => '1012',
            'VK_VERSION'  => '008',
            'VK_SND_ID'   => $this->sellerId,
            'VK_STAMP'    => $this->orderId,
            'VK_AMOUNT'   => $this->amount,
            'VK_CURR'     => $this->currency,
            'VK_REF'      => ProtocolHelper::calculateReference($this->orderId),
            'VK_MSG'      => $this->message,
            'VK_RETURN'   => $this->requestUrl,
            'VK_CANCEL'   => $this->requestUrl,
            'VK_LANG'     => $this->language,
            'VK_MAC'      => 'PmAB256IR1FzTKZHNn5LBPso/KyLAhNcTOMq82lhpYn0mXKYtVtpNkolQxyETnTcIn1TcYOmekJEATe86Bz2MRljEQqllkaIl7bNuLCtuBPtAOYWNLmQHoop+5QSiguJEmEV+JJU3w4BApjWcsHA5HYlYze+3L09UO6na0lB/Zs=',
            'VK_DATETIME' => $this->datetime
        );
    }

    /**
     * Test for correctly generated request data for service 1012
     */

    public function testGetPaymentRequestService1012(){

        // Test service 1012
        $requestData = $this->protocol->getPaymentRequest($this->orderId, $this->amount, $this->message, 'UTF-8', $this->language, $this->currency, $this->timezone);

        // We should have exactly same data
        $this->assertEquals($this->expectedData, $requestData);

    }

    /**
     * Test for correctly generated request data for service 1011
     * Test keys as strings
     */

    public function testGetPaymentRequestService1011(){

        // Create new protocol, with keys as strings
        $this->protocol = new iPizza(
            $this->sellerId,
            file_get_contents(__DIR__.'/../keys/iPizza/private_key.pem'),
            '',
            file_get_contents(__DIR__.'/../keys/iPizza/public_key.pem'),
            $this->requestUrl,
            $this->sellerName,
            $this->sellerAccount
        );

        // New expected values
        $this->expectedData['VK_SERVICE']  = '1011';
        $this->expectedData['VK_ACC']      = $this->sellerAccount;
        $this->expectedData['VK_NAME']     = $this->sellerName;
        $this->expectedData['VK_MAC']      = 'PuJTjADqHeArALfzTo2ZsynckTOVRFZMnOnbv9tv30KrF2a9m/yJuRn9vcd3JuaSjgzKoS7DRSouDgXAe6GNLZnduhXZrYx5JtVMmnlgooQ+/pJqO6ZOzwsEjaXooTLCCnKA5P9zWoxXpe8Al4IC9pj7jLNFG3dCeG9XO5uRZEs=';
        $this->expectedData['VK_DATETIME'] = $this->datetime;

        $requestData = $this->protocol->getPaymentRequest($this->orderId, $this->amount, $this->message, 'UTF-8', $this->language, $this->currency, $this->timezone);

        // We should have exactly same data
        $this->assertEquals($this->expectedData, $requestData);
    }

    /**
     * Test successful payment response
     */

    public function testHandlePaymentResponseSuccess(){
        $responseData = array(
            'VK_SERVICE'    => '1111',
            'VK_VERSION'    => '008',
            'VK_SND_ID'     => $this->senderName,
            'VK_REC_ID'     => $this->sellerId,
            'VK_STAMP'      => $this->orderId,
            'VK_T_NO'       => 100,
            'VK_AMOUNT'     => $this->amount,
            'VK_CURR'       => $this->currency,
            'VK_REC_ACC'    => $this->sellerAccount,
            'VK_REC_NAME'   => $this->sellerName,
            'VK_SND_ACC'    => '101032423434543',
            'VK_SND_NAME'   => 'Mart Mets',
            'VK_REF'        => $this->orderId,
            'VK_MSG'        => $this->message,
            'VK_MAC'        => 'Sp0VzYSPyZviiCewmwbtqny8cYRcnYU4Noh0cwxOYoZ5IpQwHuolNbFI+1Kkuk5n6cWs2X48IYYOUMRi9VTqdsfSN7z5jpUwEwjLsCMDUDdro421Je7eXXkEkbZlEcgY8wtR5H+OO955aqxDdZeS0dkuuxTN70Z9Esv5feXYxsw=',
            'VK_T_DATETIME' => $this->datetime,
            'VK_LANG'       => 'EST'
        );

        $response = $this->protocol->handleResponse($responseData);

        $this->assertInstanceOf('RKD\Banklink\Response\PaymentResponse', $response);
        $this->assertEquals(PaymentResponse::STATUS_SUCCESS, $response->getStatus());

        // This is valid response
        $this->assertTrue($response->wasSuccessful());

        // We should have exactly same data
        $this->assertEquals($responseData, $response->getResponseData());

        // Order id is set to every response
        $this->assertEquals($this->orderId, $response->getOrderId());

        // We should get same prefered language
        $this->assertEquals('EST', $response->getLanguage());

        $expextedSender          = new \stdClass();
        $expextedSender->name    = 'Mart Mets';
        $expextedSender->account = '101032423434543';

        // Test correct data
        $this->assertEquals($this->amount, $response->getSum());
        $this->assertEquals($this->currency, $response->getCurrency());
        $this->assertEquals($expextedSender, $response->getSender());
        $this->assertEquals(100, $response->getTransactionId());
        $this->assertEquals($this->datetime, $response->getTransactionDate());

    }

    /**
     * Test failed payment response
     */

    public function testHandlePaymentResponseError(){
        $responseData = array(
            'VK_SERVICE'  => '1911',
            'VK_VERSION'  => '008',
            'VK_SND_ID'   => $this->senderName,
            'VK_REC_ID'   => $this->sellerId,
            'VK_STAMP'    => $this->orderId,
            'VK_REF'      => $this->orderId,
            'VK_MSG'      => $this->message,
            'VK_MAC'      => 'o4rju0oEwITuIheUdtDjp2njKhBzvQv8RjKg+rdCB+fwGiUS8zpXzr0I+wj0vl13h+ACGAR1LO9gR2+IG1yq+AJdQdVszJIbeA1jcg1GFtl1xyLN8LXYfubHHUB/7EWwiEGZKcHrNp3pAsADlLwySQLRWatheMLPqRRk2FX96Ko=',
            'VK_DATETIME' => $this->datetime,
        );

        $response = $this->protocol->handleResponse($responseData);

        $this->assertInstanceOf('RKD\Banklink\Response\PaymentResponse', $response);
        $this->assertEquals(PaymentResponse::STATUS_ERROR, $response->getStatus());

        // This is not valid response, so validation should fail
        $this->assertFalse($response->wasSuccessful());

        // We should have exactly same data
        $this->assertEquals($responseData, $response->getResponseData());

        // Order id is set to every response
        $this->assertEquals($this->orderId, $response->getOrderId());

        // Failed request is not settings response data
        $this->assertNull($response->getSum());
        $this->assertNull($response->getCurrency());
        $this->assertNull($response->getSender());
        $this->assertNull($response->getTransactionId());
        $this->assertNull($response->getTransactionDate());

    }

    /**
     * Test authentication request data
     * Test service 4011
     */

    public function testGetAuthRequest4011(){

        $expectedData = array(
            'VK_SERVICE'  => '4011',
            'VK_VERSION'  => '008',
            'VK_SND_ID'   => 'id2000',
            'VK_RETURN'   => 'http://example.com',
            'VK_DATETIME' => '2015-09-29T15:00:00+0300',
            'VK_RID'      => '',
            'VK_LANG'     => 'EST',
            'VK_REPLY'    => '3012',
            'VK_MAC'      => 'tCzsgSP0NVlNDvzsPnDZpwfPDwlrWoLFOUDSJ80sYDMbPsXBiid0M8xKT9ep0KVmj8BBUwWOGGjENSkaNXcZKAoqw0h1V1J7Hxuy1/gnIgkAkiY1OQftMYNuyrmKj1xVP4JGH3kp4ZEiyXJ0ySj/VGW4P1Vyv2oMUVHN+vDqHR0=',
        );

        $requestData = $this->protocol->getAuthRequest();

        // We should have exactly same data
        $this->assertEquals($expectedData, $requestData);
    }

    /**
     * Test authentication request data
     * Test service 4012
     */

    public function testGetAuthRequest4012(){

        $expectedData = array(
            'VK_SERVICE'  => '4012',
            'VK_VERSION'  => '008',
            'VK_SND_ID'   => 'id2000',
            'VK_REC_ID'   => 'bank-id',
            'VK_NONCE'    => 'random-nonce',
            'VK_RETURN'   => 'http://example.com',
            'VK_DATETIME' => $this->datetime,
            'VK_RID'      => 'random-rid',
            'VK_LANG'     => 'EST',
            'VK_MAC'      => 'MtmH+8VgmKhw/Q6kO4EZdgNMP9ZWhCXfO0OHUgyHd74ofhdkvhLnzSWxqHZgWv9lCo3ZSrZ1mHJEf1rezBod7QQDcPmMVHl9iijJug2oySgT27Re89oytVN3Zlzmko9LFEaE8JIYnvxN4B9mc/bWfW0hvHSyBehpWdlVO5HIO+c=',
        );

        $requestData = $this->protocol->getAuthRequest('bank-id', 'random-nonce', 'random-rid');

        // We should have exactly same data
        $this->assertEquals($expectedData, $requestData);
    }

    /**
     * Test successful authentication response
     */

    public function testHandleAuthResponseSuccess(){

        $responseData = array(
            'VK_SERVICE'   => '3013',
            'VK_VERSION'   => '008',
            'VK_DATETIME'  => '2015-10-12T08:47:15+0300',
            'VK_SND_ID'    => 'uid100010',
            'VK_REC_ID'    => 'EYP',
            'VK_RID'       => 'random-rid',
            'VK_NONCE'     => 'random-nonce',
            'VK_USER_NAME' => 'Tõõger Leõpäöld',
            'VK_USER_ID'   => '37602294565',
            'VK_COUNTRY'   => 'EE',
            'VK_OTHER'     => '',
            'VK_TOKEN'     => '1',
            'VK_ENCODING'  => 'UTF-8',
            'VK_LANG'      => 'EST',
            'VK_MAC'       => 'RBkszGx+hP/B24Bziuq+vAJx0saRILcoc8BRQt8WYaq5mK6PdfOimZ3cTz9/t+4AQyZJfvA+Nv7NUxtieDKPorp4P1jzlbcR4K6lkit286H+TptIlWbPvcD2dj7Q7UapNtEB5FmMc62IMbbQCiTVyV5bs6f3DJYr3kOrOV/LHTY='
        );

        $response = $this->protocol->handleResponse($responseData);

        $this->assertInstanceOf('RKD\Banklink\Response\AuthResponse', $response);
        $this->assertEquals(AuthResponse::STATUS_SUCCESS, $response->getStatus());

        // This is valid response
        $this->assertTrue($response->wasSuccessful());

        // We should have exactly same data
        $this->assertEquals($responseData, $response->getResponseData());

        // We should get same prefered language
        $this->assertEquals('EST', $response->getLanguage());

        // Test user data
        $this->assertEquals($responseData['VK_USER_ID'], $response->getUserId());
        $this->assertEquals($responseData['VK_USER_NAME'], $response->getUserName());
        $this->assertEquals($responseData['VK_COUNTRY'], $response->getUserCountry());
        $this->assertEquals($responseData['VK_TOKEN'], $response->getToken());
        $this->assertEquals($responseData['VK_NONCE'], $response->getNonce());
        $this->assertEquals($responseData['VK_RID'], $response->getRid());
        $this->assertEquals($responseData['VK_DATETIME'], $response->getAuthDate());

        // Test all auth methods
        $this->assertEquals('ID card', $response->getAuthMethod());

        $response->setToken(2);
        $this->assertEquals('Mobile ID', $response->getAuthMethod());

        $response->setToken(5);
        $this->assertEquals('One-off code card', $response->getAuthMethod());

        $response->setToken(6);
        $this->assertEquals('PIN-calculator', $response->getAuthMethod());

        $response->setToken(7);
        $this->assertEquals('Code card', $response->getAuthMethod());

        $response->setToken(0);
        $this->assertEquals('unknown', $response->getAuthMethod());

    }

    /**
     * Test failed authentication response
     */

    public function testHandleAuthResponseError(){

        $responseData = array(
            'VK_SERVICE'   => '3012',
            'VK_VERSION'   => '008',
            'VK_USER'      => '',
            'VK_DATETIME'  => '2015-10-12T08:47:15+0300',
            'VK_SND_ID'    => 'uid100010',
            'VK_REC_ID'    => 'EYP',
            'VK_RID'       => 'random-rid',
            'VK_NONCE'     => 'random-nonce',
            'VK_USER_NAME' => 'Tõõger Leõpäöld',
            'VK_USER_ID'   => '37602294565',
            'VK_COUNTRY'   => 'EE',
            'VK_OTHER'     => '',
            'VK_TOKEN'     => '1',
            'VK_ENCODING'  => 'UTF-8',
            'VK_LANG'      => 'EST',
            'VK_MAC'       => 'RBkszGx+hP/B24Bziuq+vAJx0saRILcoc8BRQt8WYaq5mK6PdfOimZ3cTz9/t+4AQyZJfvA+Nv7NUxtieDKPorp4P1jzlbcR4K6lkit286H+TptIlWbPvcD2dj7Q7UapNtEB5FmMc62IMbbQCiTVyV5bs6f3DJYr3kOrOV/LHTY='
        );

        $response = $this->protocol->handleResponse($responseData);

        $this->assertInstanceOf('RKD\Banklink\Response\AuthResponse', $response);
        $this->assertEquals(AuthResponse::STATUS_ERROR, $response->getStatus());

        // This is not valid response
        $this->assertFalse($response->wasSuccessful());

        // We should have exactly same data
        $this->assertEquals($responseData, $response->getResponseData());
    }

    /**
     * @expectedException UnexpectedValueException
     */

    public function testHandleResponseUnsupportedService(){
        $responseData = array(
            'VK_SERVICE'  => '0000',
        );

        $response = $this->protocol->handleResponse($responseData);
    }

    /**
     * @expectedException UnexpectedValueException
     */

    public function testGetRequestFieldMissing(){
        $responseData = $this->protocol->getPaymentRequest($this->orderId, null, $this->message, 'UTF-8', $this->language, $this->currency, $this->timezone);
    }

    /**
     * Test can't generate request inputs
     *
     * @expectedException UnexpectedValueException
     */

    public function testNoRequestData(){
        $request = new PaymentRequest('http://google.com', array());

        $request->getRequestInputs();
    }

    /**
     * Test invalid public key
     * @expectedException UnexpectedValueException
     */

    public function testInvalidPublicKey(){
        $this->protocol = new iPizza(
            $this->sellerId,
            __DIR__.'/../keys/iPizza/private_key.pem',
            '',
            __DIR__.'/../keys/iPizza/no_key.pem',
            $this->requestUrl,
            $this->sellerName,
            $this->sellerAccount
        );

        $responseData = array(
            'VK_SERVICE'   => '3013',
            'VK_VERSION'   => '008',
            'VK_DATETIME'  => '2015-10-12T08:47:15+0300',
            'VK_SND_ID'    => 'uid100010',
            'VK_REC_ID'    => 'EYP',
            'VK_RID'       => 'random-rid',
            'VK_NONCE'     => 'random-nonce',
            'VK_USER_NAME' => 'Error here',
            'VK_USER_ID'   => '37602294565',
            'VK_COUNTRY'   => 'EE',
            'VK_OTHER'     => '',
            'VK_TOKEN'     => '1',
            'VK_ENCODING'  => 'UTF-8',
            'VK_LANG'      => 'EST',
            'VK_MAC'       => 'RBkszGx+hP/B24Bziuq+vAJx0saRILcoc8BRQt8WYaq5mK6PdfOimZ3cTz9/t+4AQyZJfvA+Nv7NUxtieDKPorp4P1jzlbcR4K6lkit286H+TptIlWbPvcD2dj7Q7UapNtEB5FmMc62IMbbQCiTVyV5bs6f3DJYr3kOrOV/LHTY='
        );

        $this->protocol->handleResponse($responseData);
    }

    /**
     * Test invalid private key
     * @expectedException UnexpectedValueException
     */

    public function testInvalidPrivateKey(){

        $this->protocol = new iPizza(
            $this->sellerId,
            __DIR__.'/../keys/iPizza/no_key.pem',
            '',
            __DIR__.'/../keys/iPizza/public_key.pem',
            $this->requestUrl,
            $this->sellerName,
            $this->sellerAccount
        );

        $this->protocol->getAuthRequest();
    }
}
