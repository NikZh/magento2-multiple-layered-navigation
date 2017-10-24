<?php
namespace Niks\LayeredNavigation\Block;

use Magento\Framework\View\Element\Template;

/**
 * Class AjaxScript
 * @package Niks_LayeredNavigation
 */
class AjaxScript extends Template
{

    /**
     * Get JSON config
     *
     * @return string
     */
    public function getAjaxConfig()
    {
        $config = [
            'disabled'          => !$this->getIsAjax(),
            'filtersContainer'  => '#layered-filter-block',
            'productsContainer' => '.' . \Niks\LayeredNavigation\Plugin\CategoryViewBlock::PRODUCT_LIST_WRAPPER
        ];
        return json_encode($config);
    }

    /**
     * Get ajax option
     *
     * @return string
     */
    protected function getIsAjax()
    {
        return $this->_scopeConfig->getValue(
            'niks_layered_navigation/general/ajax',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()->getId()
        );
    }
}
