<?php
/**
 * RKD Banklink.
 *
 * @link https://github.com/renekorss/Banklink/
 *
 * @author Rene Korss <rene.korss@gmail.com>
 * @copyright 2016-2018 Rene Korss
 * @license MIT
 */
namespace RKD\Banklink;

use RKD\Banklink\Protocol\IPizza;

/**
 * Banklink settings for Coop Pank.
 *
 * For more information, please visit: UNKNOWN
 * @TODO: Link broken due to bank name change. New url unknown. E-mail sent.
 *
 * @author  Rene Korss <rene.korss@gmail.com>
 */
class CoopPank extends Banklink
{
    /**
     * Request url.
     *
     * @var string
     */
    protected $requestUrl = 'https://i-pank.krediidipank.ee/teller/maksa';

    /**
     * Test request url.
     *
     * @var string
     */
    protected $testRequestUrl = 'http://localhost:8080/banklink/krediidipank-common';

    /**
     * Response encoding.
     *
     * @var string
     */
    protected $responseEncoding = 'ISO-8859-13';

    /**
     * Force Krediidipank class to use IPizza protocol.
     *
     * @param RKD\Banklink\Protocol\IPizza $protocol   Protocol used
     */
    public function __construct(IPizza $protocol)
    {
        parent::__construct($protocol);
    }

    /**
     * Override encoding field.
     */
    protected function getEncodingField()
    {
        return 'VK_ENCODING';
    }

    /**
     * By default uses UTF-8.
     *
     * @return array Array of additional fields to send to bank
     */
    protected function getAdditionalFields()
    {
        return [
            'VK_ENCODING' => $this->requestEncoding,
        ];
    }
}
