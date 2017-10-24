<?php

namespace Niks\LayeredNavigation\Model\Layer\Filter;

use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;

trait SliderTrait
{
    /** @var \Niks\LayeredNavigation\Model\ResourceModel\Fulltext\Collection|null  */
    protected $_skipFilterCollection;

    /**
     * Apply filter to product collection
     *
     * @param   \Magento\Framework\App\RequestInterface $request
     * @return  $this
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $this->applyToCollection($this->getLayer()->getProductCollection(), true);
        return $this;
    }

    /**
     * Get collection without current filter
     *
     * @return \Niks\LayeredNavigation\Model\ResourceModel\Fulltext\Collection
     */
    protected function getCollectionWithoutFilter()
    {
        if (!$this->_skipFilterCollection) {
            /** @var \Niks\LayeredNavigation\Model\ResourceModel\Fulltext\Collection $productCollection */
            $productCollection = $this->getLayer()
                ->getProductCollection();

            /** @var \Niks\LayeredNavigation\Model\ResourceModel\Fulltext\Collection $collection */
            $this->_skipFilterCollection = $this->collectionProvider->getCollection($this->getLayer()->getCurrentCategory());
            $this->_skipFilterCollection->updateSearchCriteriaBuilder();
            $this->getLayer()->prepareProductCollection($this->_skipFilterCollection);
            foreach ($productCollection->getAddedFilters() as $field => $condition) {
                if ($this->getAttributeModel()->getAttributeCode() == $field) {
                    continue;
                }
                $this->_skipFilterCollection->addFieldToFilter($field, $condition);
            }
        }
        return $this->_skipFilterCollection;
    }

    /**
     * Mock items for slider
     *
     * @return mixed
     */
    protected function _getItemsData()
    {
        if ($this->isSliderEnabled()) {
            $this->itemDataBuilder->addItemData(
                true,
                true,
                true
            );
            return $this->itemDataBuilder->build();
        }
        return parent::_getItemsData();
    }

    /**
     * Check is slider enabled
     *
     * @return bool
     */
    protected function isSliderEnabled()
    {
        $scope = ObjectManager::getInstance()->get(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $storeManager = ObjectManager::getInstance()->get(StoreManagerInterface::class);

        return $scope->getValue(
            'niks_layered_navigation/general/slider',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeManager->getStore()->getId()
        );
    }
}