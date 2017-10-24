<?php
namespace Niks\LayeredNavigation\Model\ResourceModel\Fulltext;
use Magento\Framework\App\ObjectManager;

/**
 * Fulltext Collection
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection
{
    /**
     * @var array
     */
    protected $_addedFilters = [];

    /**
     * Apply attribute filter to facet collection
     *
     * @param string $field
     * @param null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if (is_string($field)) {
            $this->_addedFilters[$field] = $condition;
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Filter Product by Categories
     *
     * @param array $categoriesFilter
     * @return $this
     */
    public function addCategoriesFilter(array $categoriesFilter)
    {
        $this->addFieldToFilter('category_ids', $categoriesFilter);
        return $this;
    }

    /**
     * Get applied filters
     *
     * @return array
     */
    public function getAddedFilters()
    {
        return $this->_addedFilters;
    }

    public function updateSearchCriteriaBuilder()
    {
        $searchCriteriaBuilder = ObjectManager::getInstance()
            ->create(\Magento\Framework\Api\Search\SearchCriteriaBuilder::class);
        $this->setSearchCriteriaBuilder($searchCriteriaBuilder);
        return $this;

    }

    protected function _prepareStatisticsData()
    {
        $this->_renderFilters();
        return parent::_prepareStatisticsData();
    }


    public function getMax($code)
    {
        $data = $this->prepareDecimalData($code);
        return isset($data['max']) ? $data['max'] : false;
    }

    public function getMin($code)
    {
        $data = $this->prepareDecimalData($code);
        return isset($data['min']) ? $data['min'] : false;
    }

    protected $decimalData = [];

    protected function prepareDecimalData($code)
    {
        if (!isset($this->decimalData[$code])) {
            $this->joinAttribute($code, 'catalog_product/' . $code, 'entity_id');
            $fieldName = $this->_getAttributeFieldName($code);
            $sqlEndPart = ') * ' . $this->getCurrencyRate() . ', 2)';
            $select = clone $this->getSelect();
            $select->reset(\Magento\Framework\DB\Select::COLUMNS);
            $select->columns(
                [
                    'max' => 'ROUND(MAX(' . $fieldName . $sqlEndPart,
                    'min' => 'ROUND(MIN(' . $fieldName . $sqlEndPart,
                ]
            );
            $row = $this->getConnection()->fetchRow($select, $this->_bindParams, \Zend_Db::FETCH_NUM);
            $this->decimalData[$code] = [
                'min' => $row[1],
                'max' => $row[0]
            ];
        }
        return $this->decimalData[$code];
    }
}
