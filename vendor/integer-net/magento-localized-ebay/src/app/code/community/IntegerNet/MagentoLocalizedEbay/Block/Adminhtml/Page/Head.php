<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_MagentoLocalizedEbay
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class IntegerNet_MagentoLocalizedEbay_Block_Adminhtml_Page_Head extends Mage_Adminhtml_Block_Page_Head
{
    public function getCssJsHtml()
    {
        $html = parent::getCssJsHtml();

        if ($this->getRequest()->getControllerName() == 'magentoLocalized' && $this->getRequest()->getActionName() == 'form') {
            $externalHtmlData = Mage::helper('magento_localized_ebay')->getHtmlData();

            if (isset($externalHtmlData['css'])) {
                $html .= '<link rel="stylesheet" type="text/css" href="' . $externalHtmlData['css'] . '" />' . "\n";
            }

            if (isset($externalHtmlData['js'])) {
                $html .= '<script type="text/javascript" src="' . $externalHtmlData['js'] . '"></script>' . "\n";
            }
        }

        return $html;
    }
}