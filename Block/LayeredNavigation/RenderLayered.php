<?php
namespace Niks\LayeredNavigation\Block\LayeredNavigation;

use Magento\Swatches\Block\LayeredNavigation\RenderLayered as CoreRender;

/**
 * Class RenderLayered Render Swatches at Layered Navigation
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RenderLayered extends CoreRender
{
    /**
     * @param string $attributeCode
     * @param int $optionId
     * @return string
     */
    public function buildUrl($attributeCode, $optionId)
    {
        $value = $this->filter->getValueAsArray();
        $value[] = $optionId;
        $value = implode('_', $value);
        $query = [$attributeCode => $value];
        return $this->_urlBuilder->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
    }
}
