<?php
namespace Niks\LayeredNavigation\Plugin;

use Magento\Swatches\Model\Plugin\FilterRenderer as CoreRenderer;

/**
 * Class FilterRenderer
 */
class FilterRenderer extends CoreRenderer
{
    /**
     * Path to RenderLayered Block
     *
     * @var string
     */
    protected $block = 'Niks\LayeredNavigation\Block\LayeredNavigation\RenderLayered';
}
