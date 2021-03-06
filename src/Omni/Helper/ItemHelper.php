<?php

namespace Ls\Omni\Helper;

use Ls\Replication\Model\ReplBarcodeRepository;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Helper\Context;
use Ls\Omni\Client\Ecommerce\Entity;
use Ls\Omni\Client\Ecommerce\Operation;
use Magento\Framework\Api\SearchCriteriaBuilder;

class ItemHelper extends \Magento\Framework\App\Helper\AbstractHelper
{

    /** @var SearchCriteriaBuilder */
    protected $searchCriteriaBuilder;

    /** @var ReplBarcodeRepository */
    protected $barcodeRepository;

    /** @var ProductRepository */
    protected $productRepository;

    /** @var array */
    private $hashCache = array();

    /**
     * ItemHelper constructor.
     * @param Context $context
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ReplBarcodeRepository $barcodeRepository
     * @param ProductRepository $productRepository
     */

    public function __construct(
        Context $context,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ReplBarcodeRepository $barcodeRepository,
        ProductRepository $productRepository
    )
    {
        parent::__construct($context);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->barcodeRepository = $barcodeRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * @param $id
     * @param bool $lite
     * @return bool|Entity\LoyItem
     */
    public function get($id, $lite = FALSE)
    {

        $result = FALSE;
        $entity = new Entity\ItemGetById();
        $entity->setItemId($id);
        $request = new Operation\ItemGetById();

        /** @var \Ls\Omni\Client\Ecommerce\Entity\ItemGetByIdResponse $response */
        $response = $request->execute($entity);

        if ($response && !is_null($response->getItemGetByIdResult())) {
            $item = $response->getItemGetByIdResult();
            $result = $item;
        }

        return $lite && $result
            ? $this->lite($result)
            : $result;
    }


    /**
     * @param Entity\LoyItem $item
     * @return $this
     */
    public function lite(Entity\LoyItem $item)
    {
        return (new Entity\LoyItem)
            ->setId($item->getId())
            ->setPrice($item->getPrice())
            ->setAllowedToSell($item->getAllowedToSell());
    }


    /**
     * @param Entity\LoyItem $item
     * @return Entity\UnitOfMeasure|Entity\UnitOfMeasure[]|null
     */
    public function uom(Entity\LoyItem $item)
    {

        $uom = new Entity\UnitOfMeasure();
        $salesUomId = $item->getSalesUomId();

        $uoms = $item->getUnitOfMeasures()->getUnitOfMeasure();

        if (is_array($uoms)) {
            /** @var Entity\UnitOfMeasure $row */
            foreach ($uoms as $row) {
                if ($row->getId() == $salesUomId) {
                    $uom = $row;
                    break;
                }
            }
        } else {
            $uom = $uoms;

        }
        /** @var Entity\UnitOfMeasure $response */
        $response = new Entity\UnitOfMeasure();
        $response->setId($uom->getId())
            ->setDecimals($uom->getDecimals())
            ->setDescription($uom->getDescription())
            ->setItemId($uom->getItemId())
            ->setPrice($uom->getPrice())
            ->setQtyPerUom($uom->getQtyPerUom())
            ->setShortDescription($uom->getShortDescription());

        return $response;
    }


    /**
     * @param Entity\LoyItem $item
     * @param null $variant_id
     * @return Entity\VariantRegistration|null
     */
    public function getItemVariant(Entity\LoyItem $item, $variant_id = NULL)
    {
        $variant = NULL;
        if (is_null($variant_id)) {
            return $variant;
        }
        /** @var \Ls\Omni\Client\Ecommerce\Entity\VariantRegistration $row */
        foreach ($item->getVariantsRegistration()->getVariantRegistration() as $row) {
            if ($row->getId() == $variant_id) {
                $variant = $row;
                break;
            }
        }

        /**  Omni is not accepting the return object so trying to work this out in different way */

        /** @var Entity\VariantRegistration $response */
        $response = new Entity\VariantRegistration();

        $response->setItemId($variant->getItemId())
            ->setId($variant->getId())
            ->setDimension1($variant->getDimension1())
            ->setDimension2($variant->getDimension2())
            ->setDimension3($variant->getDimension3())
            ->setDimension4($variant->getDimension4())
            ->setDimension5($variant->getDimension5())
            ->setDimension6($variant->getDimension6())
            ->setFrameworkCode($variant->getFrameworkCode())
            ->setImages($variant->getImages());

        return $response;

    }
}