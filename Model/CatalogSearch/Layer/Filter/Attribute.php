<?php
namespace Niks\LayeredNavigation\Model\CatalogSearch\Layer\Filter;
use Niks\LayeredNavigation\Model\Layer\Filter\Attribute as CatalogFilter;

use Magento\Search\Model\QueryFactory;

/**
 * Layer attribute filter
 */
class Attribute extends CatalogFilter
{
    /**
     * @var \Magento\Search\Model\QueryFactory
     */
    protected $queryFactory;

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
        array $data = [],
        QueryFactory $queryFactory
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $tagFilter,
            $data
        );
        $this->queryFactory = $queryFactory;
    }

    /**
     * Get data array for building attribute filter items
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getItemsData()
    {

        if (!$this->_getRequest()->getParam($this->_requestVar)) {
            return parent::_getItemsData();
        }

        /** @var \Niks\LayeredNavigation\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()
            ->getProductCollection();

        /** @var \Niks\LayeredNavigation\Model\ResourceModel\Fulltext\Collection $collection */
        $collection = $this->getLayer()->getCollectionProvider()->getCollection($this->getLayer()->getCurrentCategory());

        $query = $this->queryFactory->get();
        if (!$query->isQueryTextShort()) {
            $collection->addSearchFilter($query->getQueryText());
        }

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
        $usedOptions = $this->getValueAsArray();
        foreach ($options as $option) {
            if (empty($option['value']) || in_array($option['value'], $usedOptions)) {
                continue;
            }
            // Check filter type
            if (empty($optionsFacetedData[$option['value']]['count'])) {
                continue;
            }

            $count = isset($optionsFacetedData[$option['value']]['count'])
                ? (int)$optionsFacetedData[$option['value']]['count']
                : 0;

            $this->itemDataBuilder->addItemData(
                $this->tagFilter->filter($option['label']),
                $option['value'],
                isset($count) ? '+' . $count : 0
            );
        }

        return $this->itemDataBuilder->build();
    }
}
