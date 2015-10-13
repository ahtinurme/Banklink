<?php
/**
 * RKD Banklink
 *
 * @package Banklink
 * @link https://github.com/renekorss/Banklink/
 * @author Rene Korss <rene.korss@gmail.com>
 * @copyright 2015 Rene Korss
 * @license MIT
 */

namespace RKD\Banklink;

// use RKD\Banklink\Protocol\Solo;

/**
 * Banklink settings for Nordea
 *
 * For more information, please visit: http://www.nordea.ee/sitemod/upload/root/content/nordea_ee_ee/eeee_corporate/eeee_co_igapaevapangandus_pr/epangandus/e-makse_teh_kirj.pdf
 *
 * @author  Rene Korss <rene.korss@gmail.com>
 */

class Nordea extends Banklink{

    /**
     * Request url
     * @var string
     */
    protected $requestUrl     = 'https://netbank.nordea.com/pnbepay/epayn.jsp';

    /**
     * Test request url
     * @var string
     */
    protected $testRequestUrl = 'http://localhost:8080/banklink/nordea';

    /**
     * Force Nordea class to use Solo protocol
     *
     * @param RKD\Banklink\Protocol\Solo $protocol Protocol used
     * @param boolean $debug Is in debug mode?
     * @param string $requestUrl Request URL
     */

    public function __construct(Solo $protocol, $debug = false, $requestUrl = null){
        // TODO
        // Must add support for SOLO protocol

        parent::__construct($protocol, $debug, $requestUrl);
    }
}
