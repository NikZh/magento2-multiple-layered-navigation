<?php
namespace Niks\LayeredNavigation\Model\Layer\Filter;
use Magento\CatalogSearch\Model\Layer\Filter\Price as CorePrice;

/**
 * Layer attribute filter
 */
class Price extends CorePrice
{
    use SliderTrait;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\Price
     */
    private $dataProvider;

    /**
     * @var \\Niks\LayeredNavigation\Model\Url\Builder
     */
    protected $urlBuilder;

    /**
     * @var \Magento\CatalogSearch\Model\Layer\Category\ItemCollectionProvider
     */
    protected $collectionProvider;

    /**
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price $resource
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Search\Dynamic\Algorithm $priceAlgorithm
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory $algorithmFactory
     * @param \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory $dataProviderFactory
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price $resource,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Search\Dynamic\Algorithm $priceAlgorithm,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory $algorithmFactory,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory $dataProviderFactory,
        \Niks\LayeredNavigation\Model\Url\Builder $urlBuilder,
        \Magento\CatalogSearch\Model\Layer\Category\ItemCollectionProvider $collectionProvider,
        array $data = []
    ) {

        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $resource,
            $customerSession,
            $priceAlgorithm,
            $priceCurrency,
            $algorithmFactory,
            $dataProviderFactory,
            $data
        );
        $this->dataProvider = $dataProviderFactory->create(['layer' => $this->getLayer()]);
        $this->urlBuilder = $urlBuilder;
        $this->collectionProvider = $collectionProvider;
    }

    /**
     * Apply current filter to collection
     *
     * @return Attribute
     */
    public function applyToCollection($collection, $addFilter = false)
    {
        $values = $this->urlBuilder->getValuesFromUrl($this->_requestVar);
        $filter = false;
        if ($values) {
            $filter = $values[0];
        }

        $filterParams = explode(',', $filter);
        $filter = $this->getCurrentValue();
        if (!$filter) {
            return $this;
        }

        $this->dataProvider->setInterval($filter);
        $priorFilters = $this->dataProvider->getPriorFilters($filterParams);
        if ($priorFilters) {
            $this->dataProvider->setPriorIntervals($priorFilters);
        }

        list($from, $to) = $filter;

        $collection->addFieldToFilter(
            'price',
            ['from' => $from, 'to' =>  empty($to) || $from == $to ? $to : $to]
        );

        if ($addFilter) {
            $this->getLayer()->getState()->addFilter(
                $this->_createItem($this->_renderRangeLabel(empty($from) ? 0 : $from, $to), $filter)
            );
        }
        return $this;
    }

    /**
     * Get applied values
     *
     * @return array|bool
     */
    public function getCurrentValue()
    {
        $values = $this->urlBuilder->getValuesFromUrl($this->_requestVar);
        $filter = false;
        if ($values) {
            $filter = $values[0];
        }
        $filterParams = explode(',', $filter);
        return $this->dataProvider->validateFilter($filterParams[0]);
    }

    /**
     * Get max value
     *
     * @return float
     */
    public function getMax()
    {
        return $this->getCollectionWithoutFilter()->getMaxPrice();
    }

    /**
     * Get min value
     *
     * @return float
     */
    public function getMin()
    {
        return $this->getCollectionWithoutFilter()->getMinPrice();
    }
}
