<?php

namespace Niks\LayeredNavigation\Model\Url;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use Magento\Catalog\Model\Layer\Category\FilterableAttributeList;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Hydrator
 * @package Niks_LayeredNavigation
 */
class Hydrator
{
    const SEO_FILTERS_DELIMITER = '__';

    const SEO_FILTER_CODE_DELIMITER = '-';

    const SEO_FILTER_VALUES_DELIMITER = '--';

    /** @var CollectionFactory  */
    protected $_attrOptionCollectionFactory;

    /** @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory  */
    protected $categoryCollectionFactory;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $scopeConfig;

    /** @var \Magento\Framework\Registry  */
    protected $registry;

    /** @var Translit  */
    protected $translitFilter;

    /** @var StoreManagerInterface  */
    protected $storeManager;

    /** @var  array */
    protected $_optionsByCode;

    /** @var FilterableAttributeList  */
    protected $attributeList;

    /** @var array  */
    protected $_attributes;

    public function __construct(
        CollectionFactory $attrOptionCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        FilterableAttributeList $attributeList,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        Translit $translitFilter
    )
    {
        $this->_attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->attributeList = $attributeList;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->translitFilter = $translitFilter;
    }

    /**
     * Extract filter params from URL part
     *
     * @param string $url
     * @return array
     */
    public function extract($url)
    {
        $byAttribute = explode(self::SEO_FILTERS_DELIMITER, $this->getFilterString($url));
        $data = [];
        foreach ($byAttribute as $attributeString) {
            preg_match('/[^-]*/', $attributeString, $match);
            if (empty($match)) {
                continue;
            }
            $attributeCode = $match[0];
            $attributeValues = explode(
                self::SEO_FILTER_VALUES_DELIMITER,
                preg_replace('/' . $attributeCode . self::SEO_FILTER_CODE_DELIMITER . '/', '', $attributeString, 1)
            );
            $attribute = $this->getAttribute($attributeCode);
            if (!$attribute && $attributeCode != 'cat') {
                continue;
            }
            $options = $this->getOptions($attributeCode);
            $data[$attributeCode] = [];
            foreach ($attributeValues as $value) {
                if ($attribute && $attribute->getBackendType() == 'decimal') {
                    $data[$attributeCode][] = str_replace('_', '-', $value);
                    continue;
                }
                $id = array_search($value, $options);
                if ($id !== false) {
                    $data[$attributeCode][] = $id;
                }
            }
        }
        return $data;
    }

    /**
     * Hydrate filter params to url string
     *
     * @param array $data
     * @return string
     */
    public function hydrate(array $data)
    {
        $stringParts = [];

        ksort($data);

        foreach ($data as $attributeCode => $values) {
            $attributeParts = [];
            $attribute = $this->getAttribute($attributeCode);
            $options = $this->getOptions($attributeCode);

            sort($values);

            foreach ($values as $value) {
                if ($attribute && $attribute->getBackendType() == 'decimal') {
                    $attributeParts[] = str_replace('-', '_', $value);
                    continue;
                }
                $attributeParts[] = $options[$value] ?? false;
            }
            $stringParts[] = $attributeCode . self::SEO_FILTER_CODE_DELIMITER . implode(self::SEO_FILTER_VALUES_DELIMITER, $attributeParts);
        }
        return implode(self::SEO_FILTERS_DELIMITER, $stringParts);
    }

    /**
     * Get filter url part
     *
     * @param string $url
     * @return string
     */
    public function getFilterString($url)
    {
        $suffix = preg_quote($this->getSuffix(), '/');
        preg_match('/[^\/]*' . $suffix . '$/', $url, $match);
        $string = '';
        if (count($match)) {
            $string = $match[0];
        }
        return preg_replace('/' . $suffix . '$/', '', $string);
    }

    /**
     * Get filterable attributes options
     *
     * @param null|string $attribute
     * @return array
     */
    protected function getOptions($attribute = null)
    {
        if (!$this->_optionsByCode) {
            $attributeIds = $this->attributeList->getList()->getAllIds();

            $optionsCollection = $this->_attrOptionCollectionFactory->create()
                ->addFieldToFilter('main_table.attribute_id', ['in' => $attributeIds])
                ->setStoreFilter($this->storeManager->getStore()->getId())
            ;
            $optionsCollection->getSelect()->joinLeft(
                ['attr_table' => $optionsCollection->getTable('eav_attribute')],
                'attr_table.attribute_id = main_table.attribute_id',
                ['attribute_code']
            );

            $this->_optionsByCode = [];
            foreach ($optionsCollection as $option) {
                if (!isset($this->_optionsByCode[$option->getAttributeCode()])) {
                    $this->_optionsByCode[$option->getAttributeCode()] = [];
                }
                $this->_optionsByCode[$option->getAttributeCode()][$option->getOptionId()] = $this->prepareOptionLabel($option->getValue());
            }

            $currentCategoryId = $this->registry->registry('current_category_id');
            if ($currentCategoryId) {
                $this->_optionsByCode['cat'] = $this->getCategoryOptions($currentCategoryId);
            }
        }

        if (!isset($this->_optionsByCode['cat'])) {
            $currentCategory = $this->registry->registry('current_category');
            $this->_optionsByCode['cat'] = $this->getCategoryOptions($currentCategory->getId());
        }
        return isset($this->_optionsByCode[$attribute]) ? $this->_optionsByCode[$attribute] : [];
    }

    /**
     * Get attribute model by code
     *
     * @param string $code
     * @return bool|\Magento\Eav\Model\Entity\Attribute
     */
    protected function getAttribute($code)
    {
        if (!$this->_attributes) {
            $this->_attributes = [];
            foreach ($this->attributeList->getList() as $attribute) {
                $this->_attributes[$attribute->getAttributeCode()] = $attribute;
            }
        }
        return isset($this->_attributes[$code]) ? $this->_attributes[$code] : false;
    }

    /**
     * Get category options
     *
     * @param int $parentId
     * @return array
     */
    protected function getCategoryOptions($parentId)
    {
        $options = [];
        $categories = $this->categoryCollectionFactory->create()->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'is_active'
        )->addAttributeToFilter('parent_id', $parentId)
            ->setStoreId(
                $this->storeManager->getStore()->getId()
            );
        foreach ($categories as $category) {
            $options[$category->getId()] = $this->prepareOptionLabel($category->getName());
        }
        return $options;
    }

    /**
     * Prepare option label for url
     *
     * @param string $label
     * @return string
     */
    protected function prepareOptionLabel($label)
    {
        return $this->translitFilter->filter($label);
    }

    /**
     * Get category url suffix
     *
     * @return string
     */
    public function getSuffix()
    {
        return $this->scopeConfig->getValue(
            \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }
}
