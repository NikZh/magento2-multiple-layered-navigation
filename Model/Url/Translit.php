<?php
namespace Niks\LayeredNavigation\Model\Url;

class Translit extends \Magento\Framework\Filter\Translit
{
    /**
     * Filter value
     *
     * @param string $string
     * @return string
     */
    public function filter($string)
    {
        $string = preg_replace('#[^0-9a-z]+#i', '_', parent::filter($string));
        $string = strtolower($string);
        $string = trim($string, '_');

        return $string;
    }
}
