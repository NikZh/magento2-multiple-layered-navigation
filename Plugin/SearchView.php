<?php

namespace Niks\LayeredNavigation\Plugin;

use Magento\Framework\App\Action\Context;

class SearchView
{
    protected $_view;

    public function __construct(
        Context $context
    ) {
        $this->_view = $context->getView();
    }

    public function afterExecute(\Magento\CatalogSearch\Controller\Result\Index $subject)
    {
        $layout = $this->_view->getLayout();
        if ($subject->getRequest()->isXmlHttpRequest()) {
            $subject->getResponse()->setHeader('Content-Type', 'application/json', true);
            $navigationBlock = $layout->getBlock('catalogsearch.leftnav');
            $productsBlock = $layout->getBlock('search_result_list');
            if ($navigationBlock) {
                return $subject->getResponse()->setBody(json_encode(['products' => $productsBlock->toHtml(), 'leftnav' => $navigationBlock->toHtml()]));
            }
        }
    }
}
