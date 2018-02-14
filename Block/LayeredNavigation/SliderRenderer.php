<?php

namespace Niks\LayeredNavigation\Block\LayeredNavigation;

use Magento\Framework\View\Element\Template;

/**
 * Class RenderLayered Render Swatches at Layered Navigation
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SliderRenderer extends Template
{
    /**
     * Path to template file.
     *
     * @var string
     */
    protected $_template = 'Niks_LayeredNavigation::slider.phtml';

    public function getFrom()
    {
        $currentValues = $this->getFilter()->getCurrentValue();
        if (isset($currentValues[0])) {
            return $currentValues[0];
        }
        return $this->getFilter()->getMin();
    }

    public function getTo()
    {
        $currentValues = $this->getFilter()->getCurrentValue();
        if (isset($currentValues[1])) {
            return $currentValues[1];
        }
        return $this->getFilter()->getMax();
    }

    public function getPriceRangeUrlTemplate()
    {
        return $this->_urlBuilder->getFilterUrl(
            $this->getFilter()->getRequestVar(),
            '{{from}}-{{to}}',
            [],
            true
        );
    }

    public function getCurrencySymbol()
    {
        if ($this->getFilter()->getAttributeModel()->getFrontendInput() == \Magento\Catalog\Model\Layer\FilterList::PRICE_FILTER) {
            return $this->_storeManager->getStore()->getCurrentCurrency()->getCurrencySymbol();
        }
    }
}
