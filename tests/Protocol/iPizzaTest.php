<?php
namespace RKD\Banklink\iPizza;

use RKD\Banklink\Protocol\iPizza;
use RKD\Banklink\Response\PaymentResponse;

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

    private $responseUrl;

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
        $this->datetime      = new \Datetime('now', new \DateTimeZone($this->timezone));

        $this->responseUrl   = 'http://example.com';

        $this->protocol = new iPizza(
            $this->sellerId,
            __DIR__.'/../keys/iPizza/private_key.pem',
            '',
            __DIR__.'/../keys/iPizza/public_key.pem',
            $this->responseUrl
        );
    }

    protected function renewDatetime(){
        $this->datetime = new \Datetime('now', new \DateTimeZone($this->timezone));
    }

    public function testGetPaymentRequestData(){

        // Test service 1012
        $expectedData = array(
            'VK_SERVICE'  => '1012',
            'VK_VERSION'  => '008',
            'VK_SND_ID'   => $this->sellerId,
            'VK_STAMP'    => $this->orderId,
            'VK_AMOUNT'   => $this->amount,
            'VK_CURR'     => $this->currency,
            'VK_REF'      => $this->orderId,
            'VK_MSG'      => $this->message,
            'VK_RETURN'   => $this->responseUrl,
            'VK_CANCEL'   => $this->responseUrl,
            'VK_LANG'     => $this->language,
            'VK_MAC'      => 'o4rju0oEwITuIheUdtDjp2njKhBzvQv8RjKg+rdCB+fwGiUS8zpXzr0I+wj0vl13h+ACGAR1LO9gR2+IG1yq+AJdQdVszJIbeA1jcg1GFtl1xyLN8LXYfubHHUB/7EWwiEGZKcHrNp3pAsADlLwySQLRWatheMLPqRRk2FX96Ko=',
            'VK_DATETIME' => $this->datetime->format('Y-m-d\TH:i:sO'),
        );

        $responseData = $this->protocol->getPaymentRequestData($this->orderId, $this->amount, $this->message, 'UTF-8', $this->language, $this->currency, $this->timezone);

        $this->assertEquals($expectedData, $responseData);

        // Test service 1011
        $this->protocol = new iPizza(
            $this->sellerId,
            __DIR__.'/../keys/iPizza/private_key.pem',
            '',
            __DIR__.'/../keys/iPizza/public_key.pem',
            $this->responseUrl,
            $this->sellerName,
            $this->sellerAccount
        );

        // New extected values
        $this->renewDatetime();

        $expectedData['VK_SERVICE']  = '1011';
        $expectedData['VK_ACC']      = $this->sellerAccount;
        $expectedData['VK_NAME']     = $this->sellerName;
        $expectedData['VK_MAC']      = 'TUw/f3+mQSt+vwfXq/qtf2NYuMTdkmQ1r9FcfPF6qpRRLYmstTTdryTh1lGgdVrBmfWX3s4Ju7y1VAyS4KyJC8C/U5WA9tp0QHVaIgMVClcoz4qMCf7r0apPF+NVwZlFWezx18prRHuKLhH7Tz/TigSR0/R9hidmGhB2MD85PIA=';
        $expectedData['VK_DATETIME'] = $this->datetime->format('Y-m-d\TH:i:sO');

        $responseData = $this->protocol->getPaymentRequestData($this->orderId, $this->amount, $this->message, 'UTF-8', $this->language, $this->currency, $this->timezone);

        $this->assertEquals($expectedData, $responseData);
    }

    public function testHandlePaymentResponseSuccess(){
        $this->renewDatetime();

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
            'VK_MAC'        => 'qtOjJvtRymP54/Xua+W75JADgq5Dc/lMpVnzA9nv9GP7n75VPKeHsKI07ok0XnY1fCeRHms2E+PKilgq8JzTUF80oTR1Jtt2OqW/IzGxoxMbmhmFGLR45W+3KmmcPOl6E95ZwjwF9cFe9NPsl/4RwvsKeOad5XeidaNsS43EHoY=',
            'VK_T_DATETIME' => '2015-09-29T15:00:00+0300',
        );

        $response = $this->protocol->handleResponse($responseData);

        $this->assertEquals(PaymentResponse::STATUS_SUCCESS, $response->getStatus());
    }

    public function testHandlePaymentResponseError(){
        $this->renewDatetime();

        $responseData = array(
            'VK_SERVICE'  => '1911',
            'VK_VERSION'  => '008',
            'VK_SND_ID'   => $this->senderName,
            'VK_REC_ID'   => $this->sellerId,
            'VK_STAMP'    => $this->orderId,
            'VK_REF'      => $this->orderId,
            'VK_MSG'      => $this->message,

            'VK_MAC'      => 'o4rju0oEwITuIheUdtDjp2njKhBzvQv8RjKg+rdCB+fwGiUS8zpXzr0I+wj0vl13h+ACGAR1LO9gR2+IG1yq+AJdQdVszJIbeA1jcg1GFtl1xyLN8LXYfubHHUB/7EWwiEGZKcHrNp3pAsADlLwySQLRWatheMLPqRRk2FX96Ko=',
            'VK_DATETIME' => $this->datetime->format('Y-m-d\TH:i:sO'),
        );

        $response = $this->protocol->handleResponse($responseData);

        $this->assertEquals(PaymentResponse::STATUS_ERROR, $response->getStatus());
    }

    /**
     * @expectedException UnexpectedValueException
     */

    public function testHandlePaymentResponseUnsupportedService(){
        $responseData = array(
            'VK_SERVICE'  => '0000',
        );

        $response = $this->protocol->handleResponse($responseData);
    }
}
