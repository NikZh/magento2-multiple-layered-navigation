<?php
namespace Niks\LayeredNavigation\Model\Layer;

/**
 * Category view layer model
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Search extends \Magento\Catalog\Model\Layer\Search
{
    /**
     * @return \Magento\Catalog\Model\Layer\ItemCollectionProviderInterface
     */
    public function getCollectionProvider()
    {
        return $this->collectionProvider;
    }
}
