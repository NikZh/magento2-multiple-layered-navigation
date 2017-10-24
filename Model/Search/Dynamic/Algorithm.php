<?php
namespace Niks\LayeredNavigation\Model\Search\Dynamic;

class Algorithm extends \Magento\Framework\Search\Dynamic\Algorithm
{
    /**
     * Flush _lastValueLimiter
     *
     * @param \Magento\Framework\Search\Dynamic\IntervalInterface $interval
     * @return array
     */
    public function calculateSeparators(\Magento\Framework\Search\Dynamic\IntervalInterface $interval)
    {
        $this->_lastValueLimiter = [null, 0];
        return parent::calculateSeparators($interval);
    }
}
