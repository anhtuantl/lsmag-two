<?php

namespace Ls\Replication\Controller\Adminhtml\Deletion;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;

/**
 * Class CategoryDeletion
 */
class Category extends Action
{

    /** @var LoggerInterface */
    protected $logger;

    /** @var Registry $registry */
    private $registry;

    /** @var CategoryFactory $categoryFactory */
    protected $categoryFactory;

    /** @var array  */
    protected $_publicActions = ['category'];
    
    /**
     * Category Deletion constructor.
     * @param CategoryFactory $categoryFactory Category Factory
     * @param Registry $registry Magento Registry
     * @param LoggerInterface $logger
     */
    public function __construct(
        CategoryFactory $categoryFactory,
        Registry $registry,
        LoggerInterface $logger,
        Context $context
    )
    {
        $this->categoryFactory = $categoryFactory;
        $this->registry = $registry;
        $this->logger = $logger;
        parent::__construct($context);
    }


    /**
     * Remove categories tree
     *
     * @return void
     */
    public function execute()
    {
        $categories = $this->categoryFactory->create()->getCollection();
        $this->registry->register("isSecureArea", true);
        foreach ($categories as $category) {
            if ($category->getId() > 2) {
                try {
                    $category->delete();
                } catch (\Exception $e) {
                    $this->logger->debug($e->getMessage());
                }
            }
        }
        $this->messageManager->addSuccessMessage(__('Categories deleted successfully.'));
        $this->_redirect('admin/system_config/index');
    }

}