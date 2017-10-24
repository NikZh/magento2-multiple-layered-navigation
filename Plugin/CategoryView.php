<?php

namespace Niks\LayeredNavigation\Plugin;

class CategoryView
{
    public function afterExecute(\Magento\Catalog\Controller\Category\View $subject, \Magento\Framework\Controller\ResultInterface $result)
    {
        if ($subject->getRequest()->isXmlHttpRequest()) {
            $subject->getResponse()->setHeader('Content-Type', 'application/json', true);
            $navigationBlock = $result->getLayout()->getBlock('catalog.leftnav');
            $productsBlock = $result->getLayout()->getBlock('category.products');
            if ($navigationBlock) {
                return $subject->getResponse()->setBody(json_encode(['products' => $productsBlock->toHtml(), 'leftnav' => $navigationBlock->toHtml()]));
            }
        }
        return $result;
    }
}
