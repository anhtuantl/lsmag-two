<?php


namespace Ls\Omni\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Psr\Log\LoggerInterface;


class Clickandcollect extends AbstractCarrier implements CarrierInterface
{

    /** @var string  */
    protected $_code = 'clickandcollect';

    /** @var bool  */
    protected $_isFixed = true;

    /** @var ResultFactory  */
    protected $_rateResultFactory;

    /** @var MethodFactory  */
    protected $_rateMethodFactory;

    /**
     * Clickandcollect constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->getCarrierCode() => __($this->getConfigData('name'))];
    }

    /**
     * @param RateRequest $request
     * @return bool|DataObject|\Magento\Shipping\Model\Rate\Result|null
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->isActive()) {
            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();

        $shippingPrice = $this->getConfigData('price');

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->_rateMethodFactory->create()
                            ->setCarrier($this->getCarrierCode())
                            ->setCarrierTitle($this->getConfigData('title'))
                            ->setMethod($this->getCarrierCode())
                            ->setMethodTitle($this->getConfigData('name'))
                            ->setPrice($shippingPrice)
                            ->setCost($shippingPrice);

        $result->append($method);

        return $result;
    }
}