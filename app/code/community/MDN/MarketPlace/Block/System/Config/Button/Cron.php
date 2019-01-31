<?php
/* 
 * Magento
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * @copyright  Copyright (c) 2013 Boost My Shop (http://www.boostmyshop.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDn_MarketPlace
 * @version 2.1
 */

class MDN_MarketPlace_Block_System_Config_Button_Cron extends Mage_Adminhtml_Block_System_Config_Form_Field {

    /**
     * Get element html
     * 
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string $html 
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getUrl('MarketPlace/Cron/Run');

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel($this->__('Run'))
                    ->setOnClick("setLocation('$url')")
                    ->toHtml();

        return $html;
    }

}
