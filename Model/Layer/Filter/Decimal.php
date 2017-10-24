<?php
namespace Niks\LayeredNavigation\Model\Layer\Filter;
use Magento\CatalogSearch\Model\Layer\Filter\Decimal as CoreDecimal;

/**
 * Layer attribute filter
 */
class Decimal extends CoreDecimal
{
    use SliderTrait;

    /**
     * @var \\Niks\LayeredNavigation\Model\Url\Builder
     */
    protected $urlBuilder;

    /**
     * @var \Magento\CatalogSearch\Model\Layer\Category\ItemCollectionProvider
     */
    protected $collectionProvider;

    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\DecimalFactory $filterDecimalFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Niks\LayeredNavigation\Model\Url\Builder $urlBuilder,
        \Magento\CatalogSearch\Model\Layer\Category\ItemCollectionProvider $collectionProvider,
        array $data = [])
    {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $filterDecimalFactory,
            $priceCurrency,
            $data
        );
        $this->urlBuilder = $urlBuilder;
        $this->collectionProvider = $collectionProvider;
    }
    
    /**
     * Apply current filter to collection
     *
     * @return Decimal
     */
    public function applyToCollection($collection, $addFilter = false)
    {
        $values = $this->urlBuilder->getValuesFromUrl($this->_requestVar);
        $filter = false;
        if ($values) {
            $filter = $values[0];
        }
        if (!$filter || is_array($filter)) {
            return $this;
        }

        list($from, $to) = explode('-', $filter);

        $collection->addFieldToFilter(
            $this->getAttributeModel()->getAttributeCode(),
            ['from' => $from, 'to' => $to]
        );

        if ($addFilter) {
            $this->getLayer()->getState()->addFilter(
                $this->_createItem($this->renderRangeLabel(empty($from) ? 0 : $from, $to), $filter)
            );
        }
        return $this;
    }

    public function getCurrentValue()
    {
        $values = $this->urlBuilder->getValuesFromUrl($this->_requestVar);
        $filter = false;
        if ($values) {
            $filter = $values[0];
        }
        $filterParams = explode('-', $filter);
        return $filterParams;
    }

    public function getMin()
    {
        return $this->getCollectionWithoutFilter()->getMin($this->_requestVar);
    }

    public function getMax()
    {
        return $this->getCollectionWithoutFilter()->getMax($this->_requestVar);
    }
}
