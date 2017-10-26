<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Niks\LayeredNavigation\Model\Layer;

use Magento\Catalog\Model\Layer\ItemCollectionProviderInterface;
use Niks\LayeredNavigation\Model\ResourceModel\Fulltext\CollectionFactory;

class ItemCollectionProvider implements ItemCollectionProviderInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getCollection(\Magento\Catalog\Model\Category $category)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        if ($category->getParentId() == 1) {
            $collection = $this->collectionFactory->create(['searchRequestName' => 'quick_search_container']);
        } else {
            $collection = $this->collectionFactory->create();
            $collection->addCategoryFilter($category);
        }
        return $collection;
    }
}
