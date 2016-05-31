<?php
namespace Niks\LayeredNavigation\Model\Layer\Filter;
use Magento\CatalogSearch\Model\Layer\Filter\Category as CoreCategory;

/**
 * Layer attribute filter
 */
class Category extends CoreCategory
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var CategoryDataProvider
     */
    private $dataProvider;

    /**
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Framework\Escaper $escaper
     * @param CategoryManagerFactory $categoryManager
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Escaper $escaper,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory $categoryDataProviderFactory,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $escaper,
            $categoryDataProviderFactory,
            $data
        );
        $this->dataProvider = $categoryDataProviderFactory->create(['layer' => $this->getLayer()]);
    }

    /**
     * @return \Magento\Framework\App\RequestInterface
     */
    protected function _getRequest()
    {
        return $this->_request;
    }

    /**
     * Apply category filter to product collection
     *
     * @param   \Magento\Framework\App\RequestInterface $request
     * @return  $this
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $this->_request = $request;
        return parent::apply($request);
    }

    /**
     * Apply current filter to collection
     *
     * @return $this
     */
    public function applyToCollection($collection)
    {
        $request = $this->_getRequest();
        $categoryId = $request->getParam($this->_requestVar) ?: $request->getParam('id');
        if (empty($categoryId)) {
            return $this;
        }
        $this->dataProvider->setCategoryId($categoryId);
        $category = $this->dataProvider->getCategory();
        $collection->addCategoryFilter($category);
        return $this;
    }
}
