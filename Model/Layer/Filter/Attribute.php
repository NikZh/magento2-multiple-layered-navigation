<?php
namespace Niks\LayeredNavigation\Model\Layer\Filter;
use Magento\CatalogSearch\Model\Layer\Filter\Attribute as CoreAttribute;

/**
 * Layer attribute filter
 */
class Attribute extends CoreAttribute
{
    /**
     * @var \Magento\Framework\Filter\StripTags
     */
    private $tagFilter;

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
     * @param \Magento\Framework\Filter\StripTags $tagFilter
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Filter\StripTags $tagFilter,
        \Niks\LayeredNavigation\Model\Url\Builder $urlBuilder,
        \Magento\CatalogSearch\Model\Layer\Category\ItemCollectionProvider $collectionProvider,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $tagFilter,
            $data
        );
        $this->tagFilter = $tagFilter;
        $this->urlBuilder = $urlBuilder;
        $this->collectionProvider = $collectionProvider;
    }

    /**
     * Apply attribute option filter to product collection
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $values = $this->urlBuilder->getValuesFromUrl($this->_requestVar);
        if (!$values) {
            return $this;
        }

        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()
            ->getProductCollection();
        $this->applyToCollection($productCollection);

        foreach ($values as $value) {
            $label = $this->getOptionText($value);
            $this->getLayer()
                ->getState()
                ->addFilter($this->_createItem($label, $value));
        }
        return $this;
    }

    /**
     * Apply current filter to collection
     *
     * @return Attribute
     */
    public function applyToCollection($collection)
    {
        $attribute = $this->getAttributeModel();
        $attributeValue = $this->urlBuilder->getValuesFromUrl($this->_requestVar);
        if (empty($attributeValue)) {
            return $this;
        }
        $collection->addFieldToFilter($attribute->getAttributeCode(), array('in' => $attributeValue));
    }

    /**
     * Get data array for building attribute filter items
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getItemsData()
    {
        $values = $this->urlBuilder->getValuesFromUrl($this->_requestVar);
        if (!$values) {
            return parent::_getItemsData();
        }

        /** @var \Niks\LayeredNavigation\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()
            ->getProductCollection();

        /** @var \Niks\LayeredNavigation\Model\ResourceModel\Fulltext\Collection $collection */
        $collection = $this->collectionProvider->getCollection($this->getLayer()->getCurrentCategory());
        $collection->updateSearchCriteriaBuilder();
        $this->getLayer()->prepareProductCollection($collection);
        foreach ($productCollection->getAddedFilters() as $field => $condition) {
            if ($this->getAttributeModel()->getAttributeCode() == $field) {
                continue;
            }
            $collection->addFieldToFilter($field, $condition);
        }

        $attribute = $this->getAttributeModel();

        $optionsFacetedData = $collection->getFacetedData($attribute->getAttributeCode());

        if ($attribute->getFrontendInput() == 'multiselect') {
            $originalFacetedData = $productCollection->getFacetedData($attribute->getAttributeCode());
            foreach ($originalFacetedData as $key => $optionData) {
                $optionsFacetedData[$key]['count'] -= $optionData['count'];
                if ($optionsFacetedData[$key]['count'] <= 0) {
                    unset($optionsFacetedData[$key]['count']);
                }
            }
        }

        $options = $attribute->getFrontend()
            ->getSelectOptions();

        foreach ($options as $option) {
            if (empty($option['value']) || in_array($option['value'], $values)) {
                continue;
            }
            // Check filter type
            if (empty($optionsFacetedData[$option['value']]['count'])) {
                continue;
            }
            $this->itemDataBuilder->addItemData(
                $this->tagFilter->filter($option['label']),
                $option['value'],
                isset($optionsFacetedData[$option['value']]['count']) ? '+' . $optionsFacetedData[$option['value']]['count'] : 0
            );
        }

        return $this->itemDataBuilder->build();
    }
}
