<?php

namespace Ls\Omni\Controller\Stock;


class Store extends \Magento\Framework\App\Action\Action
{
    /**
     * @var $_request
     */
    protected $_request;
    /**
     * @var $_scopeConfig
     */
    protected $_scopeConfig;
    /**
     * @var $_session
     */
    protected $_session;
    /**
     * @var $_stockHelper
     */
    protected $_stockHelper;
    /**
     * @var $_resultJsonFactory
     */
    protected $_resultJsonFactory;
    /**
     * Store constructor
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $session
     * @param \Ls\Omni\Helper\StockHelper $stockHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $session,
        \Ls\Omni\Helper\StockHelper $stockHelper
    )
    {
        $this->_request = $request;
        $this->_scopeConfig = $scopeConfig;
        $this->_session = $session;
        $this->_stockHelper = $stockHelper;
        $this->_resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * execute
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
            $result = $this->_resultJsonFactory->create();
        if ($this->getRequest()->isAjax())
        {
            $selectedStore = $this->_request->getParam('storeid');
            $quote = $this->_session->getQuote();
            $totalItems = $quote->getAllItems();
            $stockCollection = [];
            $notAvailableNotice = __("Check other stores or remove not available at");
            foreach ($totalItems as $item) {
                $sku = $item->getSku();
                if ($item->getProductType() == "configurable") {
                    continue;
                }
                if (strpos($sku, '-') !== false) {
                    $sku = explode('-', $sku)[0];
                }
                $response = $this->_stockHelper->getItemStockInStore($selectedStore, $sku);
                if ($response->getInventoryResponse()->getQtyActualInventory()) {
                    $stockCollection[] = ["name" => $item->getName(), "status" => "1", "display" => __("This item is available")];
                } else {
                    $stockCollection[] = ["name" => $item->getName(), "status" => "0", "display" => __("This item is not available")];
                }
            }
            $result = $result->setData(["remarks" => $notAvailableNotice, "stocks" => $stockCollection]);
        }
        return $result;
    }



}
