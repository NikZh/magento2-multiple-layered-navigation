<?php
namespace Niks\LayeredNavigation\Model\Layer\Filter;
use Magento\CatalogSearch\Model\Layer\Filter\Attribute as CoreAttribute;

/**
 * Layer attribute filter
 */
class Attribute extends CoreAttribute
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\Filter\StripTags
     */
    private $tagFilter;

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
    }

    /**
     * @return \Magento\Framework\App\RequestInterface
     */
    protected function _getRequest()
    {
        return $this->_request;
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
        $this->_request = $request;
        if (empty($request->getParam($this->_requestVar))) {
            return $this;
        }

        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()
            ->getProductCollection();
        $this->applyToCollection($productCollection);

        $attributeValues = $this->getValueAsArray();
        foreach ($attributeValues as $value) {
            $label = $this->getOptionText($value);
            $this->getLayer()
                ->getState()
                ->addFilter($this->_createItem($label, $value));
        }
        return $this;
    }

    /**
     * Get filter values
     *
     * @return array
     */
    public function getValueAsArray()
    {
        $paramValue = $this->_getRequest()->getParam($this->_requestVar);
        if (!$paramValue) {
            return array();
        }
        $requestValue = $this->_getRequest()->getParam($this->_requestVar);
        return explode('_', $requestValue);
    }

    /**
     * Apply current filter to collection
     *
     * @return Attribute
     */
    public function applyToCollection($collection)
    {
        $attribute = $this->getAttributeModel();
        $attributeValue = $this->getValueAsArray();
        if (empty($attributeValue)) {
            return $this;
        }
        $collection->addFieldToFilter($attribute->getAttributeCode(), array('in' => $attributeValue));
    }

    /**
     * Get filter value for reset current filter state
     *
     * @param string $value
     * @return string
     */
    public function getResetOptionValue($value)
    {
        $attributeValues = $this->getValueAsArray();
        $key = array_search($value, $attributeValues);
        unset($attributeValues[$key]);
        return implode('_', $attributeValues);
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
        $usedOptions = $this->getValueAsArray();
        foreach ($options as $option) {
            if (empty($option['value']) || in_array($option['value'], $usedOptions)) {
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
