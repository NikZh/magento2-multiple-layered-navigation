<?php
namespace Niks\LayeredNavigation\Plugin;

use Magento\Swatches\Model\Plugin\FilterRenderer as CoreRenderer;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;

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
    protected $block = \Niks\LayeredNavigation\Block\LayeredNavigation\RenderLayered::class;


    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param \Magento\LayeredNavigation\Block\Navigation\FilterRenderer $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Layer\Filter\FilterInterface $filter
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundRender(
        \Magento\LayeredNavigation\Block\Navigation\FilterRenderer $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Layer\Filter\FilterInterface $filter
    ) {
        if ($filter->hasAttributeModel()) {
            if ($this->swatchHelper->isSwatchAttribute($filter->getAttributeModel())) {
                return $this->layout
                    ->createBlock($this->block)
                    ->setSwatchFilter($filter)
                    ->toHtml();
            }
        }

        if ($this->isSliderEnabled()
            && $filter->hasAttributeModel()
            && $filter->getAttributeModel()->getBackendType() == 'decimal'
            && strpos('no-slider', $filter->getAttributeModel()->getFrontendClass()) === false
        ) {
            return $this->layout
                ->createBlock(\Niks\LayeredNavigation\Block\LayeredNavigation\SliderRenderer::class)
                ->setFilter($filter)
                ->toHtml();
        }
        return $proceed($filter);
    }

    protected function isSliderEnabled()
    {
        $scope = ObjectManager::getInstance()->get(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $storeManager  = ObjectManager::getInstance()->get(StoreManagerInterface::class);

        return $scope->getValue(
            'niks_layered_navigation/general/slider',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeManager->getStore()->getId()
        );
    }


}
