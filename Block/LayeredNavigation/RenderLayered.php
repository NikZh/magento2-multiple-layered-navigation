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
        return $this->_urlBuilder->getFilterUrl(
            $this->filter->getRequestVar(),
            $optionId,
            []
        );
    }
}
