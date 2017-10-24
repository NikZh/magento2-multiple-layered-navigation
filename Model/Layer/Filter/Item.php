<?php
namespace Niks\LayeredNavigation\Model\Layer\Filter;

class Item extends \Magento\Catalog\Model\Layer\Filter\Item
{
    /**
     * Get url for remove item from filter
     *
     * @return string
     */
    public function getRemoveUrl()
    {
        return $this->_url->getRemoveFilterUrl(
            $this->getFilter()->getRequestVar(),
            $this->getValue(),
            [$this->_htmlPagerBlock->getPageVarName() => null]
        );
    }

    /**
     * Get filter item url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->_url->getFilterUrl(
            $this->getFilter()->getRequestVar(),
            $this->getValue(),
            [$this->_htmlPagerBlock->getPageVarName() => null]
        );
    }
}
