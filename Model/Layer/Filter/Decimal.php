<?php
namespace Niks\LayeredNavigation\Model\Layer\Filter;
use Magento\CatalogSearch\Model\Layer\Filter\Decimal as CoreDecimal;

/**
 * Layer attribute filter
 */
class Decimal extends CoreDecimal
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

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
     * @return Attribute
     */
    public function applyToCollection($collection)
    {
        $request = $this->_getRequest();
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter || is_array($filter)) {
            return $this;
        }

        list($from, $to) = explode('-', $filter);

        $collection->addFieldToFilter(
                $this->getAttributeModel()->getAttributeCode(),
                ['from' => $from, 'to' => $to]
            );
        
        return $this;
    }
}
