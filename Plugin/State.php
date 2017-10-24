<?php
namespace Niks\LayeredNavigation\Plugin;


class State
{
    /**
     * @param \Magento\LayeredNavigation\Block\Navigation\State $subject
     * @param \Closure $proceed
     * @return mixed|string
     */
    public function aroundGetClearUrl(
        \Magento\LayeredNavigation\Block\Navigation\State $subject,
        \Closure $proceed
    ) {
        $filterState = [];
        foreach ($subject->getActiveFilters() as $item) {
            $filterState[$item->getFilter()->getRequestVar()] = $item->getFilter()->getCleanValue();
        }
        $params['_navigation_filters'] = '';
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_escape'] = true;
        $params['_query'] = $filterState;
        return $subject->getUrl('*/*/*', $params);
    }
}
