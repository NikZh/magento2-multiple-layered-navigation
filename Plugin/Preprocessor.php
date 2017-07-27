<?php
namespace Niks\LayeredNavigation\Plugin;

use Magento\Framework\Search\Request\FilterInterface;

class Preprocessor
{
    /**
     * @param \Magento\CatalogSearch\Model\Adapter\Mysql\Filter\Preprocessor $subject
     * @param \Closure $proceed
     * @param FilterInterface $filter
     * @param $isNegation
     * @param $query
     * @return mixed|string
     */
    public function aroundProcess(
        \Magento\CatalogSearch\Model\Adapter\Mysql\Filter\Preprocessor $subject,
        \Closure $proceed,
        FilterInterface $filter,
        $isNegation,
        $query
    ) {
        if ($filter->getField() === 'category_ids' && is_array($filter->getValue()) && isset($filter->getValue()['in'])) {
                return 'category_ids_index.category_id IN (' . implode(',', $filter->getValue()['in']) . ')';
        }
        return $proceed($filter, $isNegation, $query);
    }
}
