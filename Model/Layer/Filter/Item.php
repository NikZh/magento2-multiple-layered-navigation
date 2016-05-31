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
        $query = [$this->getFilter()->getRequestVar() => $this->getFilter()->getResetOptionValue($this->getValue())];
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_query'] = $query;
        $params['_escape'] = true;
        return $this->_url->getUrl('*/*/*', $params);
    }

    /**
     * Get filter item url
     *
     * @return string
     */
    public function getUrl()
    {
        $value = $this->getFilter()->getValueAsArray();
        if (empty($value)) {
            return parent::getUrl();
        }
        $value[] = $this->getValue();
        $value = implode('_', $value);
        $query = [
            $this->getFilter()->getRequestVar() => $value,
            // exclude current page from urls
            $this->_htmlPagerBlock->getPageVarName() => null,
        ];

        return $this->_url->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
    }
}
