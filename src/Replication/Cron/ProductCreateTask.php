<?php

namespace Ls\Replication\Cron;


use Ls\Replication\Model\ReplImageLink;
use Ls\Replication\Model\ReplImageLinkRepository;
use Magento\Eav\Model\Config;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
/*
 * TODO replace all Models with API interfaces.
 * for now, we dont have di.xml which can implement Interface with our model
 *
 */

use Ls\Replication\Model\ReplImageRepository;
use Ls\Replication\Model\ReplExtendedVariantValueRepository;
use Ls\Replication\Model\ReplItemVariantRegistrationRepository;
use Ls\Replication\Model\ReplItemRepository;
use Ls\Replication\Model\ReplHierarchyLeafRepository;
use Ls\Replication\Model\ReplBarcodeRepository;
use Ls\Replication\Model\ReplPriceRepository;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Api\Data\ImageContentInterface;

/*
 * For search filters
 */

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;

use Ls\Replication\Api\ReplImageLinkRepositoryInterface;
use Ls\Replication\Api\ReplAttributeValueRepositoryInterface;
use Ls\Omni\Helper\LoyaltyHelper;

use Psr\Log\LoggerInterface;
use Ls\Replication\Helper\ReplicationHelper;
use Ls\Core\Model\LSR;

/**
 * Class ProductCreateTask
 * @package Ls\Replication\Cron
 */
class ProductCreateTask
{
    /** @var Factory */
    protected $factory;

    /** @var Item */
    protected $item;

    /** @var Config */
    protected $eavConfig;

    /** @var Configurable */
    protected $configurable;

    /** @var Attribute */
    protected $attribute;

    /** @var ProductInterfaceFactory */
    protected $productFactory;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var CollectionFactory */
    protected $categoryCollectionFactory;

    /** @var CategoryLinkManagementInterface */
    protected $categoryLinkManagement;

    /** @var ReplItemRepository */
    protected $itemRepository;

    /** @var ReplExtendedVariantValueRepository */
    protected $extendedVariantValueRepository;

    /** @var ReplImageRepository */
    protected $imageRepository;

    /** @var ReplBarcodeRepository */
    protected $replBarcodeRepository;

    /** @var ReplImageLinkRepositoryInterface */
    protected $replImageLinkRepositoryInterface;

    /** @var ReplHierarchyLeafRepository */
    protected $replHierarchyLeafRepository;

    /** @var ReplPriceRepository */
    protected $replPriceRepository;

    /** @var ProductAttributeMediaGalleryEntryInterface */
    protected $attributeMediaGalleryEntry;

    /** @var ImageContentInterface */
    protected $imageContent;

    /** @var SearchCriteriaBuilder */
    protected $searchCriteriaBuilder;

    /** @var FilterBuilder */
    protected $filterBuilder;

    /** @var FilterGroupBuilder */
    protected $filterGroupBuilder;

    /** @var LoggerInterface */
    protected $logger;

    /* @var LoyaltyHelper */
    private $loyaltyHelper;

    /** @var ReplicationHelper */
    protected $replicationHelper;

    /** @var ReplAttributeValueRepositoryInterface */
    protected $replAttributeValueRepositoryInterface;

    /** @var LSR */
    protected $_lsr;

    /** @var Cron Checking */
    protected $cronStatus = false;

    /**
     * ProductCreateTask constructor.
     *
     * @param Factory $factory
     * @param Item $item
     * @param Config $eavConfig
     * @param Configurable $configurable
     * @param Attribute $attribute
     * @param ProductInterfaceFactory $productInterfaceFactory
     * @param ProductRepositoryInterface $productRepository
     * @param ProductAttributeMediaGalleryEntryInterface $attributeMediaGalleryEntry
     * @param ImageContentInterface $imageContent
     * @param CollectionFactory $categoryCollectionFactory
     * @param CategoryLinkManagementInterface $categoryLinkManagement
     * @param ReplItemRepository $itemRepository
     * @param ReplItemVariantRegistrationRepository $replItemVariantRegistrationRepository
     * @param ReplExtendedVariantValueRepository $extendedVariantValueRepository
     * @param ReplImageRepository $replImageRepository
     * @param ReplHierarchyLeafRepository $replHierarchyLeafRepository
     * @param ReplBarcodeRepository $replBarcodeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param ReplImageLinkRepositoryInterface $replImageLinkRepositoryInterface
     * @param LoyaltyHelper $loyaltyHelper
     * @param ReplicationHelper $replicationHelper
     * @param ReplAttributeValueRepositoryInterface $replAttributeValueRepositoryInterface
     * @param LoggerInterface $logger
     * @param LSR $LSR
     */
    public function __construct(
        Factory $factory,
        Item $item,
        Config $eavConfig,
        Configurable $configurable,
        Attribute $attribute,
        ProductInterfaceFactory $productInterfaceFactory,
        ProductRepositoryInterface $productRepository,
        ProductAttributeMediaGalleryEntryInterface $attributeMediaGalleryEntry,
        ImageContentInterface $imageContent,
        CollectionFactory $categoryCollectionFactory,
        CategoryLinkManagementInterface $categoryLinkManagement,
        ReplItemRepository $itemRepository,
        ReplItemVariantRegistrationRepository $replItemVariantRegistrationRepository,
        ReplExtendedVariantValueRepository $extendedVariantValueRepository,
        ReplImageRepository $replImageRepository,
        ReplHierarchyLeafRepository $replHierarchyLeafRepository,
        ReplBarcodeRepository $replBarcodeRepository,
        ReplPriceRepository $replPriceRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        ReplImageLinkRepositoryInterface $replImageLinkRepositoryInterface,
        LoyaltyHelper $loyaltyHelper,
        ReplicationHelper $replicationHelper,
        ReplAttributeValueRepositoryInterface $replAttributeValueRepositoryInterface,
        LoggerInterface $logger,
        LSR $LSR
    )
    {
        $this->factory = $factory;
        $this->item = $item;
        $this->eavConfig = $eavConfig;
        $this->configurable = $configurable;
        $this->attribute = $attribute;
        $this->productFactory = $productInterfaceFactory;
        $this->productRepository = $productRepository;
        $this->attributeMediaGalleryEntry = $attributeMediaGalleryEntry;
        $this->imageContent = $imageContent;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->itemRepository = $itemRepository;
        $this->replItemVariantRegistrationRepository = $replItemVariantRegistrationRepository;
        $this->extendedVariantValueRepository = $extendedVariantValueRepository;
        $this->imageRepository = $replImageRepository;
        $this->replHierarchyLeafRepository = $replHierarchyLeafRepository;
        $this->replBarcodeRepository = $replBarcodeRepository;
        $this->replPriceRepository = $replPriceRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->logger = $logger;
        $this->replImageLinkRepositoryInterface = $replImageLinkRepositoryInterface;
        $this->loyaltyHelper = $loyaltyHelper;
        $this->replicationHelper = $replicationHelper;
        $this->replAttributeValueRepositoryInterface = $replAttributeValueRepositoryInterface;
        $this->_lsr = $LSR;
    }

    /**
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function execute()
    {
        $fullReplicationAttributeStatus = $this->_lsr->getStoreConfig(ReplEcommAttributeTask::CONFIG_PATH_STATUS);
        $fullReplicationAttributeOptionValueStatus = $this->_lsr->getStoreConfig(ReplEcommAttributeValueTask::CONFIG_PATH_STATUS);

        $CronCategoryCheck = $this->_lsr->getStoreConfig(LSR::SC_SUCCESS_CRON_CATEGORY);
        $CronAttributeCheck = $this->_lsr->getStoreConfig(LSR::SC_SUCCESS_CRON_ATTRIBUTE);

        if ($CronCategoryCheck == 1 && $CronAttributeCheck == 1) {
            if ($fullReplicationAttributeStatus != 1 && $fullReplicationAttributeOptionValueStatus != 1)
                return;


            $this->logger->debug('Running ProductCreateTask');
            /** @var \Magento\Framework\Api\SearchCriteria $criteria */
            $criteria = $this->replicationHelper->buildCriteriaForNewItems('', '', '', 25);

            /** @var \Ls\Replication\Model\ReplItemSearchResults $items */
            $items = $this->itemRepository->getList($criteria);

            /** @var \Ls\Replication\Model\ReplItem $item */
            foreach ($items->getItems() as $item) {
                try {
                    $productData = $this->productRepository->get($item->getNavId());
                    if ($item->getIsUpdated() == 1) {
                        try {
                            $productData->setName($item->getDescription());
                            $productData->setMetaTitle($item->getDescription());
                            $productData->setDescription($item->getDetails());
                            $productData->setCustomAttribute("uom", $item->getBaseUnitOfMeasure());
                            $itemPrice = $this->getItemPrice($item->getNavId());
                            if (isset($itemPrice))
                                $productData->setPrice($itemPrice->getUnitPrice());
                            else
                                $productData->setPrice($item->getUnitPrice());
                            $productImages = $this->replicationHelper->getImageLinksByType($item->getNavId(), 'Item');
                            if ($productImages) {
                                $this->logger->debug('Found images for the item ' . $item->getNavId());
                                $productData->setMediaGalleryEntries($this->getMediaGalleryEntries($productImages));
                            }
                            $productData->save();
                            $item->setData('is_updated', '0');
                            $item->setData('processed', '1');
                            $item->save();
                            $this->cronStatus = true;

                        } catch (\Exception $e) {
                            $this->logger->debug($e->getMessage());
                        }
                    }else if($item->getIsUpdated() == 0 && $item->getProcessed() == 0){
                        try {
                            $item->setData('is_updated', '0');
                            $item->setData('processed', '1');
                            $item->save();
                            $this->cronStatus = true;
                        }catch (\Exception $e) {
                            $this->logger->debug($e->getMessage());
                        }
                    }
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
                    $product = $this->productFactory->create();
                    $product->setName($item->getDescription());
                    $product->setMetaTitle($item->getDescription());
                    $product->setSku($item->getNavId());
                    $product->setUrlKey($this->oSlug($item->getDescription() . "-" . $item->getNavId()));
                    $product->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
                    $product->setWeight(10);
                    $product->setDescription($item->getDetails());
                    $itemPrice = $this->getItemPrice($item->getNavId());
                    if (isset($itemPrice))
                        $product->setPrice($itemPrice->getUnitPrice());
                    else
                        $product->setPrice($item->getUnitPrice());
                    $product->setAttributeSetId(4);
                    $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                    $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
                    $product->setCustomAttribute("uom", $item->getBaseUnitOfMeasure());
                    /** @var ReplBarcodeRepository $itemBarcodes */
                    $itemBarcodes = $this->_getBarcode($item->getNavId());
                    if (isset($itemBarcodes[$item->getNavId()])) {
                        $product->setCustomAttribute("barcode", $itemBarcodes[$item->getNavId()]);
                    }
                    $product->setStockData(array(
                        'use_config_manage_stock' => 1,
                        'is_in_stock' => 1,
                        'qty' => 100
                    ));
                    $productImages = $this->replicationHelper->getImageLinksByType($item->getNavId(), 'Item');
                    if ($productImages) {
                        $this->logger->debug('Found images for the item ' . $item->getNavId());
                        $product->setMediaGalleryEntries($this->getMediaGalleryEntries($productImages));
                    }
                    $this->logger->debug('trying to save product ' . $item->getNavId());

                    /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productSaved */
                    $product = $this->getProductAttributes($product, $item);
                    $productSaved = $this->productRepository->save($product);
                    $variants = $this->getProductVarients($item->getNavId());
                    if (count($variants) > 0) {
                        $this->createConfigurableProducts($productSaved, $item, $itemBarcodes, $variants);
                    }
                    try {
                        $item->setProcessed(1)->save();
                        $this->cronStatus = true;
                    } catch (\Exception $e) {
                        $this->logger->debug($e->getMessage());
                    }
                }
            }
            if (count($items->getItems()) == 0)
                $this->assignProductToCategory();
            $fullReplicationVariantStatus = $this->_lsr->getStoreConfig(ReplEcommItemVariantRegistrationsTask::CONFIG_PATH_STATUS);
            if ($fullReplicationVariantStatus == 1)
                $this->updateVariantsOnly();
            $fullReplicationImageLinkStatus = $this->_lsr->getStoreConfig(ReplEcommImageLinksTask::CONFIG_PATH_STATUS);
            if ($fullReplicationImageLinkStatus == 1)
                $this->updateImagesOnly();
            $this->logger->debug('End ProductCreateTask');
            $this->cronStatus = true;
        } else {
            $this->logger->debug("Product Replication cron fails because category or attribute replication cron not executed successfully.");
        }
        $this->replicationHelper->updateCronStatus($this->cronStatus, LSR::SC_SUCCESS_CRON_PRODUCT);
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \Ls\Replication\Model\ReplItem $item
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    protected function getProductAttributes(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        \Ls\Replication\Model\ReplItem $item
    )
    {
        $criteria = $this->replicationHelper->buildCriteriaForProductAttributes($item->getNavId(), 100);
        /** @var \Ls\Replication\Model\ReplAttributeValueSearchResults $items */
        $items = $this->replAttributeValueRepositoryInterface->getList($criteria);
        /** @var \Ls\Replication\Model\ReplAttributeValue $item */
        foreach ($items->getItems() as $item) {
            $formattedCode = $this->replicationHelper->formatAttributeCode($item->getCode());
            $attribute = $this->eavConfig->getAttribute('catalog_product', $formattedCode);
            if ($attribute->getFrontendInput() == 'select') {
                $value = $this->_getOptionIDByCode($formattedCode, $item->getValue());
            } elseif ($attribute->getFrontendInput() == 'boolean') {
                if (strtolower($item->getValue()) == 'yes') {
                    $value = 1;
                } else {
                    $value = 0;
                }
            } else {
                $value = $item->getValue();
            }
            $product->setData($formattedCode, $value);
            try {
                $item->setProcessed(1)->save();
            } catch (\Exception $e) {
                $this->logger->debug($e->getMessage());
            }
        }
        return $product;
    }

    /**
     * @param $productImages
     * @return array
     * @throws \Exception
     */
    private function getMediaGalleryEntries($productImages)
    {
        $galleryArray = array();
        $i = 0;
        /** @var \Ls\Replication\Model\ReplImageLink $image */
        foreach ($productImages as $image) {
            $imageSize = array(
                'height' => $this->_lsr::DEFAULT_IMAGE_HEIGHT,
                'width' => $this->_lsr::DEFAULT_IMAGE_WIDTH
            );
            /** @var \Ls\Omni\Client\Ecommerce\Entity\ImageSize $imageSizeObject */
            $imageSizeObject = $this->loyaltyHelper->getImageSize($imageSize);
            $result = $this->loyaltyHelper->getImageById($image->getImageId(), $imageSizeObject);
            if ($result) {
                $i++;
                /** @var \Magento\Framework\Api\Data\ImageContentInterface $imageContent */
                $imageContent = $this->imageContent->setBase64EncodedData($result->getImage())
                    ->setName($image->getImageId() . ".jpg")
                    ->setType($this->getMimeType($result->getImage()));
                $this->attributeMediaGalleryEntry->setMediaType("image")
                    ->setLabel("Product Image")
                    ->setPosition($i)
                    ->setDisabled(false)
                    ->setTypes(
                        array(
                            "image",
                            "small_image",
                            "thumbnail"
                        )
                    )->setContent($imageContent);
                $galleryArray[] = $this->attributeMediaGalleryEntry;
                try {
                    $image->setProcessed(1)->save();
                } catch (\Exception $e) {
                    $this->logger->debug($e->getMessage());
                }
            }
        }
        return $galleryArray;

    }

    /**
     * @param $productGroupId
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function findCategoryIdFromFactory($productGroupId)
    {
        $categoryCollection = $this->categoryCollectionFactory->create()->addAttributeToFilter('nav_id',
            $productGroupId)->setPageSize(1);
        if ($categoryCollection->getSize()) {
            return array(
                $categoryCollection->getFirstItem()->getParentId(),
                $categoryCollection->getFirstItem()->getId()
            );
        }
    }

    /**
     * Assign products to category using HierarchyCode
     */
    private function assignProductToCategory()
    {
        $hierarchyCode = $this->_lsr->getStoreConfig(LSR::SC_REPLICATION_HIERARCHY_CODE);
        if (empty($hierarchyCode)) {
            $this->logger->debug("Hierarchy Code not defined in the configuration.");
            return;
        }
        $filters = array(
            array('field' => 'NodeId', 'value' => true, 'condition_type' => 'notnull'),
            array('field' => 'HierarchyCode', 'value' => $hierarchyCode, 'condition_type' => 'eq')
        );
        $criteria = $this->replicationHelper->buildCriteriaForArray($filters, 100);
        /** @var \Ls\Replication\Model\ReplHierarchyLeafSearchResults $replHierarchyLeafRepository */
        $replHierarchyLeafRepository = $this->replHierarchyLeafRepository->getList($criteria);
        foreach ($replHierarchyLeafRepository->getItems() as $hierarchyLeaf) {
            try {
                $categoryArray = $this->findCategoryIdFromFactory($hierarchyLeaf->getNodeId());
                if (!empty($categoryArray)) {
                    $this->categoryLinkManagement->assignProductToCategories($hierarchyLeaf->getNavId(), $categoryArray);
                    $hierarchyLeaf->setData('processed', '1');
                    $hierarchyLeaf->setData('is_updated', '0');
                    $hierarchyLeaf->save();
                }
            } catch (\Exception $e) {
                $this->logger->debug($e->getMessage());
            }
        }
    }

    /**
     * @param $image64
     * @return string
     */
    private function getMimeType($image64)
    {
        return finfo_buffer(finfo_open(), base64_decode($image64), FILEINFO_MIME_TYPE);
    }


    /**
     * @param $string
     * @return string
     */
    protected function oSlug($string)
    {
        return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-',
            html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i',
                '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
    }

    /**
     * Return all Variants
     * @param type $itemid
     * @return type
     */
    private function getProductVarients($itemid)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('ItemId', $itemid)->create();
        $variants = $this->replItemVariantRegistrationRepository->getList($searchCriteria)->getItems();
        return $variants;
    }

    /**
     * Return all updated variants only
     * @param type $filters
     * @return type
     */
    private function getUpdatedProductVariants($filters)
    {
        /** @var \Magento\Framework\Api\SearchCriteria $criteria */
        $criteria = $this->replicationHelper->buildCriteriaGetUpdatedOnly($filters);
        $variants = $this->replItemVariantRegistrationRepository->getList($criteria)->getItems();
        return $variants;
    }

    /**
     *
     * @param type $code
     * @param type $value
     * @return type
     */
    protected function _getOptionIDByCode($code, $value)
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', $code);
        $optionID = $attribute->getSource()->getOptionId($value);
        return $optionID;
    }

    /**
     *
     * @param type $itemId
     * @return type
     * @throws \Exception
     */
    protected function _getAttributesCodes($itemId)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('ItemId', $itemId)->create();
        $attributeCodes = $this->extendedVariantValueRepository->getList($searchCriteria)->getItems();
        /** @var \Ls\Replication\Model\ReplExtendedVariantValue $valueCode */
        foreach ($attributeCodes as $valueCode) {
            $formattedCode = $this->replicationHelper->formatAttributeCode($valueCode->getCode());
            $finalCodes[$valueCode->getDimensions()] = $formattedCode;
            try {
                $valueCode->setProcessed(1)->save();
            } catch (\Exception $e) {
                $this->logger->debug($e->getMessage());
            }
        }
        return $finalCodes;
    }


    /**
     * Return all the barcodes including the variant
     *
     * @param $itemId
     * @return array
     */
    protected function _getBarcode($itemId)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('ItemId', $itemId)->create();
        $allBarCodes = array();
        /** @var ReplBarcodeRepository $itemBarcodes */
        $itemBarcodes = $this->replBarcodeRepository->getList($searchCriteria)->getItems();
        foreach ($itemBarcodes as $itemBarcode) {
            $sku = $itemBarcode->getItemId() . (($itemBarcode->getVariantId()) ? '-' . $itemBarcode->getVariantId() : '');
            $allBarCodes[$sku] = $itemBarcode->getNavId();
        }
        return $allBarCodes;
    }

    /**
     * Return item
     *
     * @param $itemId
     * @return array
     */
    protected function _getItem($itemId)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('nav_id', $itemId)->create();
        $items = array();
        /** @var ReplItemRepository $items */
        $items = $this->itemRepository->getList($searchCriteria)->getItems();
        foreach ($items as $item) {
            return $item;
        }
    }

    /**
     * Item Price
     * @param $itemId
     * @return mixed
     */
    protected function getItemPrice($itemId, $variantId = NULL)
    {
        $storeId = $this->_lsr->getStoreConfig(LSR::SC_SERVICE_STORE);
        $filters = array(
            array('field' => 'ItemId', 'value' => $itemId, 'condition_type' => 'eq'),
            array('field' => 'StoreId', 'value' => $storeId, 'condition_type' => 'eq'),
            array('field' => 'QtyPerUnitOfMeasure', 'value' => 0, 'condition_type' => 'eq'),
            //array('field' => 'VariantId', 'value' => $variantId, 'condition_type' => 'eq')
        );
        $items = array();
        $searchCriteria = $this->replicationHelper->buildCriteriaForArray($filters, 1);
        /** @var ReplPriceRepository $items */
        $items = $this->replPriceRepository->getList($searchCriteria)->getItems();
        foreach ($items as $item) {
            return $item;
        }
    }

    /**
     * Update/Add the modified/added variants of the item
     */
    protected function updateVariantsOnly()
    {
        $filters = array(
            array('field' => 'ItemId', 'value' => true, 'condition_type' => 'notnull')
        );
        $variants = $this->getUpdatedProductVariants($filters);
        if (count($variants) > 0) {
            try {
                foreach ($variants as $variant) {
                    $items[] = $variant->getItemId();
                }
                array_unique($items);
                foreach ($items as $item) {
                    $productData = $this->productRepository->get($item);
                    /** @var ReplBarcodeRepository $itemBarcodes */
                    $itemBarcodes = $this->_getBarcode($item);
                    /** @var ReplItemRepository $itemData */
                    $itemData = $this->_getItem($item);
                    $this->createConfigurableProducts($productData, $itemData, $itemBarcodes, $variants);
                }
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return;
            }
        }
    }

    /**
     * Update/Add the modified/added images of the item
     */
    protected function updateImagesOnly()
    {
        $filters = array(
            array('field' => 'TableName', 'value' => 'Item%', 'condition_type' => 'like')
        );
        $criteria = $this->replicationHelper->buildCriteriaGetUpdatedOnly($filters);
        $images = $this->replImageLinkRepositoryInterface->getList($criteria)->getItems();
        if (count($images) > 0) {
            foreach ($images as $image) {
                try {
                    if ($image->getTableName() == "Item" || $image->getTableName() == "Item Variant") {
                        /** @var ReplImageLink $image */
                        $item = $image->getKeyValue();
                        $item = str_replace(',', '-', $item);
                        /** @var ProductRepositoryInterface $productData */
                        $productData = $this->productRepository->get($item);
                        $galleryImage = array($image);
                        $productData->setMediaGalleryEntries($this->getMediaGalleryEntries($galleryImage));
                        $this->productRepository->save($productData);
                        $image->setData('is_updated', '0');
                        $image->save();
                    }
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    continue;
                }
            }
        }
    }


    /** For product variants, get image from item_image_link with type item variant
     * @param $configProduct
     * @param $item
     * @param $itemBarcodes
     * @param $variants
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\StateException
     */
    protected function createConfigurableProducts($configProduct, $item, $itemBarcodes, $variants)
    {
        // get those attribute codes which are assigned to product.
        $attributesCode = $this->_getAttributesCodes($item->getNavId());
        $this->logger->debug('Attribute code array');
        $attributesIds = array();
        $associatedProductIds = array();
        $configurableProductsData = array();
        foreach ($attributesCode as $value) {
            /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute\Interceptor $attribute */
            $attribute = $this->eavConfig->getAttribute('catalog_product', $value);
            $this->logger->debug('Class for Attribute is ' . get_class($attribute));
            $attributeOptions[$attribute->getId()] = $attribute->getSource()->getAllOptions();
            $attributesIds[] = $attribute->getId();
        }

        /** @var \Ls\Replication\Model\ReplItemVariantRegistration $value */
        foreach ($variants as $value) {
            $sku = $value->getItemId() . '-' . $value->getVariantId();
            try {
                $productData = $this->productRepository->get($sku);
                if ($value->getIsUpdated() == 1) {
                    try {
                        $productData->setName($value->getDescription());
                        $productData->setMetaTitle($value->getDescription());
                        $productData->setDescription($value->getDetails());
                        $productData->setCustomAttribute("uom", $value->getBaseUnitOfMeasure());
                        $itemPrice = $this->getItemPrice($value->getItemId(), $value->getVariantId());
                        if (isset($itemPrice))
                            $productData->setPrice($itemPrice->getUnitPrice());
                        $productImages = $this->replicationHelper->getImageLinksByType($value->getItemId() . ',' . $value->getVariantId(), 'Item Variant');
                        if ($productImages) {
                            $productData->setMediaGalleryEntries($this->getMediaGalleryEntries($productImages));
                        }
                        $productData->save();
                        $value->setData('is_updated', '0');
                        $value->save();
                    } catch (\Exception $e) {
                        $this->logger->debug($e->getMessage());
                    }
                }
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                // check which attributes are available to be set as variant option.
                $d1 = (($value->getVariantDimension1()) ? $value->getVariantDimension1() : '');
                $d2 = (($value->getVariantDimension2()) ? $value->getVariantDimension2() : '');
                $d3 = (($value->getVariantDimension3()) ? $value->getVariantDimension3() : '');
                $d4 = (($value->getVariantDimension4()) ? $value->getVariantDimension4() : '');
                $d5 = (($value->getVariantDimension5()) ? $value->getVariantDimension5() : '');
                $d6 = (($value->getVariantDimension6()) ? $value->getVariantDimension6() : '');

                /** @var \Magento\Catalog\Api\Data\ProductInterface $productV */
                $productV = $this->productFactory->create();
                $productV->setName($item->getDescription() . (($d1) ? '-' . $d1 : '') . (($d2) ? '-' . $d2 : '') . (($d3) ? '-' . $d3 : ''));
                $productV->setSku($sku);
                $itemPrice = $this->getItemPrice($value->getItemId(), $value->getVariantId());
                if (isset($itemPrice))
                    $productV->setPrice($itemPrice->getUnitPrice());
                else
                    $productV->setPrice($item->getUnitPrice());
                $productV->setAttributeSetId(4);
                $productV->setWebsiteIds([1]);
                $productV->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE);
                $productV->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                $productV->setTypeId('simple');
                foreach ($attributesCode as $keyCode => $valueCode) {
                    if (isset($keyCode) && $keyCode != '') {
                        $optionId = $this->_getOptionIDByCode($valueCode, ${'d' . $keyCode});
                        if (isset($optionId)) {
                            $productV->setData($valueCode, $optionId);
                        }
                    }
                }
                $productImages = $this->replicationHelper->getImageLinksByType($value->getItemId() . ',' . $value->getVariantId(), 'Item Variant');
                if ($productImages) {
                    $this->logger->debug('Found images for the item ' . $item->getNavId());
                    $productV->setMediaGalleryEntries($this->getMediaGalleryEntries($productImages));
                }

                $productV->setCustomAttribute("uom", $item->getBaseUnitOfMeasure());
                if (isset($itemBarcodes[$sku])) {
                    $productV->setCustomAttribute("barcode", $itemBarcodes[$sku]);
                }
                $productV->setStockData(array(
                    'use_config_manage_stock' => 1,
                    'is_in_stock' => 1,
                    'is_qty_decimal' => 0,
                    'qty' => 100
                ));
                /** @var \Magento\Catalog\Api\Data\ProductInterface $productSaved */
                $productSaved = $this->productRepository->save($productV);
                $associatedProductIds[] = $productSaved->getId();
                $value->setData('is_updated', '0');
                $value->setProcessed(1)->save();
            }
        }
        $productId = $configProduct->getId();
        foreach ($attributesIds as $attributeKey => $attributeId) {
            $data = array(
                'attribute_id' => $attributeId,
                'product_id' => $productId,
                'position' => $attributeKey
            );
            try {
                $this->attribute->setData($data)->save();
            } catch (\Exception $e) {
                $this->logger->debug($e->getMessage());
            }
        }
        $configProduct->setTypeId("configurable"); // Setting Product Type As Configurable
        $configProduct->setAffectConfigurableProductAttributes(4);
        $this->configurable->setUsedProductAttributeIds($attributesIds, $configProduct);
        $configProduct->setNewVariationsAttributeSetId(4); // Setting Attribute Set Id
        $configProduct->setConfigurableProductsData($configurableProductsData);
        $configProduct->setCanSaveConfigurableAttributes(true);
        $configProduct->setAssociatedProductIds($associatedProductIds); // Setting Associated Products
        $configProduct->save();
    }
}
