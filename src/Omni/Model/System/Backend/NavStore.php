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
use Ls\Replication\Cron\ReplEcommHierarchyTask;

class NavStore extends Value
{
    /** @var LoggerInterface */
    protected $logger;

    protected $_replicationHelper;


    /**
     * @var ReplEcommHierarchyTask
     */
    protected $_replHierarchyTask;

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
                                  ReplEcommHierarchyTask $replEcommHierarchyTask,
                                  \Ls\Replication\Helper\ReplicationHelper $replicationHelper,
                                  array $data = [ ]
    ) {
        $this->logger = $context->getLogger();
        $this->_replHierarchyTask   =   $replEcommHierarchyTask;
        $this->_replicationHelper   =   $replicationHelper;
        parent::__construct( $context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data );
    }

    public function beforeSave () {

        return parent::beforeSave();
    }

    public function afterSave () {


        return parent::afterSave();
    }

    public function afterDelete () {

        return parent::afterDelete();
    }

}
