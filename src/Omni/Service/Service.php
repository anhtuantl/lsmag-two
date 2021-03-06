<?php

namespace Ls\Omni\Service;

use Zend\Uri\Uri;
use Zend\Uri\UriFactory;
use Ls\Core\Model\LSR;


class Service
{

    const DEFAULT_BASE_URL = NULL;

    /** @var  LSR $_lsr */
    protected $_lsr;

    /** @var null|string  */
    protected $_baseurl = NULL;


    static protected $endpoints = [
        ServiceType::ECOMMERCE => 'ecommerceservice.svc',
        ServiceType::LOYALTY => 'loyservice.svc',
        ServiceType::GENERAL => 'service.svc',
    ];


    /**
     * Service constructor.
     * @param LSR $Lsr
     */
    public function __construct()
    {
        $this->_baseurl = $this->getOmniBaseUrl();
    }

    /**
     * @param ServiceType $type
     * @param string $base_url
     * @param bool $wsdl
     *
     * @return Uri
     */
    public static function getUrl(ServiceType $type,
                                  $base_url = self::DEFAULT_BASE_URL,
                                  $wsdl = TRUE)
    {
        if (is_null($base_url)) {
            $base_url = (new self)->getOmniBaseUrl();
        }
        $url = join('/', [$base_url, static::$endpoints[$type->getValue()]]);
        if ($wsdl) $url .= '?singlewsdl';
        return UriFactory::factory($url);
    }

    /**
     * @return string
     * Use this in combination with \Ls\Core\Model\LSR::isLSR funciton
     */
    public function getOmniBaseUrl()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $lsr = $objectManager->create('Ls\Core\Model\LSR');
        return $lsr->getStoreConfig(LSR::SC_SERVICE_BASE_URL);
    }

}
