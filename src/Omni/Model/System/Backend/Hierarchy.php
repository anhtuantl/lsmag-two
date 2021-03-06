<?php
namespace Ls\Omni\Model\System\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;

class Hierarchy extends Value
{
    /** @var LoggerInterface */
    protected $logger;


    /**
     * @param Context               $context
     * @param Registry              $registry
     * @param ScopeConfigInterface  $config
     * @param TypeListInterface     $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null       $resourceCollection
     * @param array                 $data
     */
    public function __construct ( Context $context,
                                  Registry $registry,
                                  ScopeConfigInterface $config,
                                  TypeListInterface $cacheTypeList,
                                  AbstractResource $resource = NULL,
                                  AbstractDb $resourceCollection = NULL,
                                  array $data = [ ]
    ) {
        $this->logger = $context->getLogger();
        parent::__construct( $context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data );
    }


    public function isValueChanged(){
        if($this->getValue() == ''){
            return false;

        }else{
            return parent::isValueChanged();
        }
    }



}
