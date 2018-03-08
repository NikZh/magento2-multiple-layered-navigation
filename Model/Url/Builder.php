<?php
namespace Niks\LayeredNavigation\Model\Url;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class Builder
 * @package Niks_LayeredNavigation
 */
class Builder extends \Magento\Framework\Url
{

    const REWRITE_NAVIGATION_PATH_ALIAS = 'rewrite_navigation_path';

    /** @var Hydrator  */
    protected $urlHydrator;

    /**
     * Retrieve route path
     *
     * @param array $routeParams
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _getRoutePath($routeParams = [])
    {
        if (!$this->isSeoUrlsEnabled()) {
            return parent::_getRoutePath($routeParams);
        }
        if (!$this->hasData('route_path')) {
            $routePath = $this->_getRequest()->getAlias(self::REWRITE_NAVIGATION_PATH_ALIAS)  ?? $this->_getRequest()->getAlias(self::REWRITE_REQUEST_PATH_ALIAS);
            if (!empty($routeParams['_use_rewrite']) && $routePath !== null && isset($routeParams['_navigation_filters'])) {
                if ($routeParams['_navigation_filters']) {
                    $suffix = $this->getUrlHydrator()->getSuffix();
                    $routePath = preg_replace('/' . preg_quote($suffix, '/') . '$/', '', $routePath) . '/' . $routeParams['_navigation_filters'] . $suffix;
                }
                $this->setData('route_path', $routePath);
                return $routePath;
            }
        }
        return parent::_getRoutePath($routeParams);
    }

    /**
     * Get filter item url
     *
     * @param string $code
     * @param string $value
     * @param array $query
     * @return string
     */
    public function getFilterUrl($code, $value, $query = [], $singleValue = false)
    {
        $params = ['_current' => true, '_use_rewrite' => true, '_query' => $query];
        $values = [];
        if (!$singleValue) {
            $values = $this->getValuesFromUrl($code);
        }
        $values[] = $value;

        if ($this->isSeoUrlsEnabled()) {
            $allFilters = $this->_getRequest()->getParam('navigation_filters', []);
            $allFilters[$code] = $values;
            $filterUrlPart = $this->getUrlHydrator()->hydrate($allFilters);
            $params['_navigation_filters'] = $filterUrlPart;
            return $this->getUrl('*/*/*', $params);
        }

        $values = implode('_', $values);
        $params['_query'][$code] = $values;
        return $this->getUrl('*/*/*', $params);
    }

    /**
     * Get remove filter item url
     *
     * @param string $code
     * @param string $value
     * @param array $query
     * @return string
     */
    public function getRemoveFilterUrl($code, $value, $query = [])
    {
        $params = ['_current' => true, '_use_rewrite' => true, '_query' => $query, '_escape' => true];
        $values = $this->getValuesFromUrl($code);
        $key = array_search($value, $values);
        unset($values[$key]);

        if ($this->isSeoUrlsEnabled()) {
            $allFilters = $this->_getRequest()->getParam('navigation_filters', []);
            if (!$values && isset($allFilters[$code])) {
                unset($allFilters[$code]);
            } else {
                $allFilters[$code] = $values;
            }

            $filterUrlPart = $this->getUrlHydrator()->hydrate($allFilters);
            $params['_navigation_filters'] = $filterUrlPart;
            return $this->getUrl('*/*/*', $params);
        }

        $params['_query'][$code] = $values ? implode('_', $values) : null;
        return $this->getUrl('*/*/*', $params);
    }

    /**
     * Get array of filter values
     *
     * @param string $code
     * @return array
     */
    public function getValuesFromUrl($code)
    {
        $paramValue = [];
        if ($this->isSeoUrlsEnabled()) {
            $filters = $this->_getRequest()->getParam('navigation_filters');
            if (is_array($filters) && isset($filters[$code])) {
                $paramValue = $filters[$code];
            }
        } else {
            $paramValue = array_filter(explode('_', $this->_getRequest()->getParam($code)));
        }
        return $paramValue;
    }

    /**
     * Chek is seo URLs opyion enabled
     *
     * @return bool
     */
    public function isSeoUrlsEnabled()
    {
        if ($this->_getRequest()->getModuleName() != 'catalog') {
            return false;
        }
        $storeManager = ObjectManager::getInstance()
            ->get(StoreManagerInterface::class);
        return $this->_scopeConfig->getValue(
            'niks_layered_navigation/general/friendly_urls',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeManager->getStore()->getId()
        );
    }

    /**
     * Remove ajax option and build url
     *
     * @param null $routePath
     * @param null $routeParams
     * @return string
     */
    public function getUrl($routePath = null, $routeParams = null)
    {
        if (isset($routeParams['_query'])) {
            $routeParams['_query']['niksAjax'] = null;
        }
        return parent::getUrl($routePath, $routeParams);
    }

    protected function getUrlHydrator()
    {
        if (!$this->urlHydrator) {
            $this->urlHydrator = ObjectManager::getInstance()
                ->get(Hydrator::class);
        }
        return $this->urlHydrator;
    }
}
